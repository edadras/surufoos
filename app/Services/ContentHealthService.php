<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Enums\StockStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Contact;
use App\Models\ContentImportLog;
use App\Models\Faq;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageView;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\SearchTerm;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\TimelineEntry;
use App\Support\Locales;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Everything the dashboard needs to answer "what needs my attention?":
 * incomplete products, missing translations, images without alt text,
 * unread messages, stock, drafts and import errors.
 */
class ContentHealthService
{
    /** Models shown in the translation manager, keyed by URL type. */
    public const TRANSLATABLE = [
        'products' => [Product::class, 'محصولات', 'title'],
        'categories' => [Category::class, 'دسته‌بندی‌ها', 'title'],
        'posts' => [Post::class, 'مقالات', 'title'],
        'recipes' => [Recipe::class, 'دستورهای غذایی', 'title'],
        'pages' => [Page::class, 'صفحات', 'title'],
        'post-categories' => [PostCategory::class, 'دسته‌های مجله', 'title'],
        'attributes' => [Attribute::class, 'ویژگی‌ها', 'name'],
        'attribute-values' => [AttributeValue::class, 'مقادیر ویژگی', 'value'],
        'ingredients' => [Ingredient::class, 'مواد اولیه', 'name'],
        'menu-items' => [MenuItem::class, 'آیتم‌های منو', 'label'],
        'sliders' => [Slider::class, 'اسلایدها', 'title'],
        'sections' => [PageSection::class, 'سکشن‌های صفحه‌ساز', 'title'],
        'faqs' => [Faq::class, 'پرسش‌های متداول', 'question'],
        'testimonials' => [Testimonial::class, 'نظرات مشتریان', 'author_name'],
        'timeline' => [TimelineEntry::class, 'تایم‌لاین', 'title'],
        'certificates' => [Certificate::class, 'گواهی‌ها', 'title'],
        'media' => [Media::class, 'متن جایگزین تصاویر', 'alt'],
        'tags' => [Tag::class, 'برچسب‌ها', 'name'],
    ];

    public function stats(): array
    {
        return [
            'products' => Product::count(),
            'products_published' => Product::where('status', ContentStatus::Published->value)->count(),
            'categories' => Category::count(),
            'posts' => Post::count(),
            'recipes' => Recipe::count(),
            'messages_unread' => Contact::unread()->count(),
            'orders_pending' => Order::where('status', 'pending')->count(),
            'views_today' => (int) PageView::whereDate('date', today())->sum('views'),
            'views_30' => (int) PageView::where('date', '>=', today()->subDays(29))->sum('views'),
        ];
    }

    /** @return array<int, array{label: string, count: int, url: ?string, level: string}> */
    public function issues(): array
    {
        $issues = [];

        $incomplete = $this->incompleteProductsQuery()->count();
        $issues[] = ['label' => 'محصولات ناقص (بدون تصویر، توضیح یا دسته)', 'count' => $incomplete, 'url' => route('admin.products.index', ['issue' => 'incomplete']), 'level' => 'warn'];

        foreach (['products', 'categories', 'posts', 'recipes', 'pages'] as $type) {
            [$class, $label] = self::TRANSLATABLE[$type];
            foreach (array_diff(Locales::codes(), [Locales::source()]) as $locale) {
                $missing = $class::whereDoesntHave('translations', fn ($q) => $q->where('locale', $locale))->count();
                if ($missing) {
                    $issues[] = ['label' => "ترجمه {$this->localeName($locale)} ناقص: {$label}", 'count' => $missing, 'url' => route('admin.translations.index', ['type' => $type, 'locale' => $locale]), 'level' => 'info'];
                }
            }
        }

        $issues[] = ['label' => 'تصاویر بدون متن جایگزین (ALT)', 'count' => Media::missingAlt()->count(), 'url' => route('admin.media.index', ['missing_alt' => 1]), 'level' => 'warn'];
        $issues[] = ['label' => 'پیام‌های خوانده‌نشده', 'count' => Contact::unread()->count(), 'url' => route('admin.contacts.index', ['unread' => 1]), 'level' => 'alert'];
        $issues[] = ['label' => 'محصولات ناموجود', 'count' => Product::where('status', ContentStatus::Published->value)->where('stock_status', StockStatus::OutOfStock->value)->count(), 'url' => route('admin.products.index', ['stock' => 'out_of_stock']), 'level' => 'warn'];
        $drafts = Product::where('status', 'draft')->count() + Post::where('status', 'draft')->count() + Recipe::where('status', 'draft')->count() + Page::where('status', 'draft')->count();
        $issues[] = ['label' => 'محتوای پیش‌نویس (محصول، مقاله، دستور، صفحه)', 'count' => $drafts, 'url' => route('admin.products.index', ['status' => 'draft']), 'level' => 'info'];
        $issues[] = ['label' => 'خطاهای درون‌ریزی', 'count' => ContentImportLog::where('status', 'failed')->count(), 'url' => route('admin.import.index', ['status' => 'failed']), 'level' => 'alert'];
        $issues[] = ['label' => 'تصاویر با خطای پردازش', 'count' => Media::where('conversion_status', 'failed')->count(), 'url' => route('admin.media.index', ['failed' => 1]), 'level' => 'alert'];

        return array_values(array_filter($issues, fn ($i) => $i['count'] > 0));
    }

    public function incompleteProductsQuery()
    {
        return Product::query()->where(fn ($q) => $q->whereNull('main_media_id')->whereNull('packshot_media_id')
            ->orWhereDoesntHave('categories')
            ->orWhereDoesntHave('translations', fn ($t) => $t->where('locale', Locales::source())->whereNotNull('short_description')->where('short_description', '!=', '')));
    }

    public function popularProducts(int $limit = 8): Collection
    {
        return Product::with('translations')->orderByDesc('views_count')->where('views_count', '>', 0)->limit($limit)->get();
    }

    public function topSearchTerms(int $limit = 10): Collection
    {
        return SearchTerm::orderByDesc('hits')->limit($limit)->get();
    }

    /** @return array<string, int> date => views */
    public function dailyViews(int $days): array
    {
        $rows = PageView::where('date', '>=', today()->subDays($days - 1))
            ->groupBy('date')->select('date', DB::raw('SUM(views) as total'))->pluck('total', 'date')
            ->mapWithKeys(fn ($v, $k) => [substr((string) $k, 0, 10) => (int) $v]);
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = today()->subDays($i)->toDateString();
            $out[$d] = $rows[$d] ?? 0;
        }

        return $out;
    }

    private function localeName(string $locale): string
    {
        return ['en' => 'انگلیسی', 'ar' => 'عربی', 'fa' => 'فارسی'][$locale] ?? $locale;
    }
}
