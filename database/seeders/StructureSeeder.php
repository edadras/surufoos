<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\SectionType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Menu;
use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class StructureSeeder extends Seeder
{
    /** Navigation labels are interface text (like button labels), provided in all three languages. */
    private const NAV = [
        'home' => ['خانه', 'Home', 'الرئيسية'],
        'products.index' => ['محصولات', 'Products', 'المنتجات'],
        'categories.index' => ['دسته‌بندی‌ها', 'Categories', 'الفئات'],
        'about' => ['درباره ما', 'About us', 'من نحن'],
        'magazine.index' => ['مجله', 'Magazine', 'المجلة'],
        'recipes.index' => ['دستورهای غذایی', 'Recipes', 'وصفات'],
        'contact' => ['تماس با ما', 'Contact', 'اتصل بنا'],
    ];

    public function run(): void
    {
        $this->menus();
        $this->pages();
        $this->quizTaxonomy();
        $this->defaults();
    }

    private function menus(): void
    {
        $main = Menu::firstOrCreate(['key' => 'main'], ['name' => 'منوی اصلی']);
        if (! $main->items()->exists()) {
            $i = 0;
            foreach (self::NAV as $route => $labels) {
                $item = $main->items()->create(['type' => 'route', 'route_name' => $route, 'is_mega' => $route === 'products.index', 'sort_order' => $i++]);
                $item->saveTranslations(['fa' => ['label' => $labels[0]], 'en' => ['label' => $labels[1]], 'ar' => ['label' => $labels[2]]], 'label');
            }
        }

        $footer = Menu::firstOrCreate(['key' => 'footer'], ['name' => 'منوی فوتر']);
        if (! $footer->items()->exists()) {
            $i = 0;
            foreach (['about', 'magazine.index', 'recipes.index', 'contact'] as $route) {
                $labels = self::NAV[$route];
                $item = $footer->items()->create(['type' => 'route', 'route_name' => $route, 'sort_order' => $i++]);
                $item->saveTranslations(['fa' => ['label' => $labels[0]], 'en' => ['label' => $labels[1]], 'ar' => ['label' => $labels[2]]], 'label');
            }
        }
    }

    /**
     * System pages and the homepage narrative order. Sections hold no copy:
     * each renders only once real data exists (slides, products, ingredients…)
     * or once an editor writes its text.
     */
    private function pages(): void
    {
        $home = Page::firstOrCreate(['key' => 'home'], ['template' => 'builder', 'status' => ContentStatus::Published]);
        if (! $home->sections()->exists()) {
            $layout = [
                [SectionType::Hero, ['slider_placement' => 'home_hero']],
                [SectionType::Story, []],
                [SectionType::ProductUniverse, ['limit' => 8]],
                [SectionType::ProductShowcase, ['source' => 'featured', 'limit' => 9]],
                [SectionType::InsideSuru, ['limit' => 3]],
                [SectionType::Journey, ['group' => 'journey']],
                [SectionType::Banners, ['placement' => 'home']],
                [SectionType::ProductShelf, ['source' => 'new', 'limit' => 10]],
                [SectionType::TasteFinder, []],
                [SectionType::Testimonials, ['limit' => 6]],
                [SectionType::Recipes, ['limit' => 3]],
                [SectionType::Articles, ['limit' => 4]],
            ];
            foreach ($layout as $i => [$type, $settings]) {
                $home->sections()->create(['type' => $type, 'settings' => $settings, 'sort_order' => $i, 'is_active' => true]);
            }
        }

        foreach (['about' => ['درباره سورو', 'About Suru', 'عن سورو'], 'contact' => ['تماس با ما', 'Contact us', 'اتصل بنا']] as $key => $titles) {
            $page = Page::firstOrCreate(['key' => $key], ['template' => 'builder', 'status' => ContentStatus::Published]);
            if (! $page->translations()->exists()) {
                $page->saveTranslations(['fa' => ['title' => $titles[0], 'slug' => $key], 'en' => ['title' => $titles[1], 'slug' => $key], 'ar' => ['title' => $titles[2], 'slug' => $key]]);
            }
            if ($key === 'about' && ! $page->sections()->exists()) {
                foreach ([[SectionType::Story, []], [SectionType::Timeline, ['group' => 'history']], [SectionType::Journey, ['group' => 'journey']], [SectionType::Certificates, []], [SectionType::Gallery, []]] as $i => [$type, $settings]) {
                    $page->sections()->create(['type' => $type, 'settings' => $settings, 'sort_order' => $i, 'is_active' => true]);
                }
            }
        }
    }

    /**
     * Taxonomy for "What do you feel like?". Values only appear on the site
     * once editors attach them to real products.
     */
    private function quizTaxonomy(): void
    {
        $sets = [
            'taste' => [['طعم', 'Taste', 'المذاق'], ['چه طعمی دوست داری؟', 'Which flavour do you like?', 'أي مذاق تحب؟'], [
                'sweet' => ['شیرین', 'Sweet', 'حلو', '#e8a33d'], 'fruity' => ['میوه‌ای', 'Fruity', 'فاكهي', '#d9573b'],
                'savory' => ['شور و خوش‌طعم', 'Savoury', 'مالح', '#8a6a3b'], 'light' => ['سبک', 'Light', 'خفيف', '#9cbf6a'],
            ]],
            'occasion' => [['زمان مصرف', 'Occasion', 'وقت الاستهلاك'], ['برای چه وقتی؟', 'For which moment?', 'لأي وقت؟'], [
                'breakfast' => ['صبحانه', 'Breakfast', 'الفطور', '#f0c46b'], 'snack' => ['میان‌وعده', 'Snack', 'وجبة خفيفة', '#c97b4a'],
                'cooking' => ['آشپزی', 'Cooking', 'الطبخ', '#6f8f4e'], 'kids' => ['برای کودکان', 'For kids', 'للأطفال', '#e58fa1'],
            ]],
        ];
        $order = 0;
        foreach ($sets as $code => [$name, $question, $values]) {
            $attribute = Attribute::firstOrCreate(['code' => $code], ['type' => 'multi', 'is_filterable' => true, 'is_quiz' => true, 'sort_order' => $order++]);
            if (! $attribute->translations()->exists()) {
                $attribute->saveTranslations([
                    'fa' => ['name' => $name[0], 'quiz_question' => $question[0]],
                    'en' => ['name' => $name[1], 'quiz_question' => $question[1]],
                    'ar' => ['name' => $name[2], 'quiz_question' => $question[2]],
                ], 'name');
            }
            $i = 0;
            foreach ($values as $valueCode => [$fa, $en, $ar, $color]) {
                $value = AttributeValue::firstOrCreate(['attribute_id' => $attribute->id, 'code' => $valueCode], ['color' => $color, 'sort_order' => $i++]);
                if (! $value->translations()->exists()) {
                    $value->saveTranslations(['fa' => ['value' => $fa], 'en' => ['value' => $en], 'ar' => ['value' => $ar]], 'value');
                }
            }
        }
    }

    private function defaults(): void
    {
        $settings = app(SettingsService::class);
        foreach (['commerce_enabled' => '0', 'show_prices' => '1', 'topbar_enabled' => '0', 'payment_methods' => json_encode(['cod'])] as $key => $value) {
            if ($settings->get($key) === null) {
                $settings->set($key, $value);
            }
        }
        foreach (['fa' => 'Suru Food', 'en' => 'Suru Food', 'ar' => 'Suru Food'] as $locale => $name) {
            if ($settings->get('site_name', null, $locale) === null) {
                $settings->set('site_name', $name, $locale);
            }
        }
        $settings->flush();
    }
}
