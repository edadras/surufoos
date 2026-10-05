<?php

namespace Tests\Feature;

use App\Models\AttributeValue;
use App\Models\Contact;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    public function test_guests_and_customers_cannot_enter_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_login_is_persian_and_throttled(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('ورود به پنل مدیریت')->assertSee('dir="rtl"', false);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'x@y.z', 'password' => 'wrong']);
        }
        $this->post('/admin/login', ['email' => 'x@y.z', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_every_admin_screen_renders_for_super_admin(): void
    {
        $admin = $this->admin();
        $product = $this->product([], [], $this->category());
        $home = Page::where('key', 'home')->first();
        $routes = [
            '/admin', '/admin/profile', '/admin/products', '/admin/products/create', "/admin/products/{$product->id}/edit",
            '/admin/categories', '/admin/categories/create', '/admin/attributes', '/admin/attributes/create', '/admin/ingredients', '/admin/ingredients/create',
            '/admin/posts', '/admin/posts/create', '/admin/post-categories', '/admin/post-categories/create', '/admin/recipes', '/admin/recipes/create',
            '/admin/pages', '/admin/pages/create', "/admin/pages/{$home->id}/edit", "/admin/pages/{$home->id}/sections/{$home->sections->first()->id}/edit",
            '/admin/sliders', '/admin/sliders/create', '/admin/banners', '/admin/banners/create', '/admin/menus', '/admin/menus/'.Menu::first()->id.'/edit',
            '/admin/blocks/faqs', '/admin/blocks/faqs/create', '/admin/blocks/testimonials/create', '/admin/blocks/timeline/create', '/admin/blocks/certificates/create',
            '/admin/comments', '/admin/media', '/admin/media/picker', '/admin/translations', '/admin/translations?type=products&locale=en', "/admin/translations/products/{$product->id}",
            '/admin/contacts', '/admin/newsletter', '/admin/orders', '/admin/seo', '/admin/redirects', '/admin/redirects/create',
            '/admin/users', '/admin/users/create', '/admin/roles', '/admin/roles/create', '/admin/settings', '/admin/settings/theme', '/admin/settings/commerce',
            '/admin/languages', '/admin/reports', '/admin/activity', '/admin/import',
        ];
        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        foreach ($home->sections as $section) {
            $this->actingAs($admin)->get("/admin/pages/{$home->id}/sections/{$section->id}/edit")->assertOk();
        }
    }

    public function test_dashboard_lists_real_issues(): void
    {
        $admin = $this->admin();
        $this->product(['fa' => 'بدون تصویر']);
        Contact::create(['name' => 'x', 'message' => 'hello']);
        $this->actingAs($admin)->get('/admin')->assertSee('محصولات ناقص')->assertSee('پیام‌های خوانده‌نشده')->assertSee('ترجمه انگلیسی ناقص');
    }

    public function test_product_can_be_created_with_relations_and_translations(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $ingredient = Ingredient::create([]);
        $ingredient->saveTranslations(['fa' => ['name' => 'شکر']], 'name');
        $value = AttributeValue::where('code', 'sweet')->first();

        $this->actingAs($admin)->post('/admin/products', [
            'translations' => [
                'fa' => ['title' => 'مربای انجیر', 'short_description' => 'کوتاه'],
                'en' => ['title' => 'Fig jam'],
                'ar' => ['title' => ''],
            ],
            'status' => 'published', 'stock_status' => 'in_stock', 'price' => '۸۵٬۰۰۰', 'net_weight' => '300', 'weight_unit' => 'g',
            'categories' => [$category->id], 'attribute_values' => [$value->id], 'tags' => 'صبحانه، شیرین',
            'ingredients' => [['ingredient_id' => $ingredient->id, 'amount' => '40%']],
            'nutrition' => [['nutrient' => 'energy', 'amount' => '250', 'unit' => 'kcal', 'per' => '100g']],
            'variants' => [['sku' => 'FIG-300', 'price' => '85000', 'stock_status' => 'in_stock']],
        ])->assertRedirect();

        $product = Product::latest('id')->first();
        $this->assertSame(85000, $product->price);
        $this->assertSame($category->id, $product->primary_category_id);
        $this->assertSame(['fa' => true, 'en' => true, 'ar' => false], $product->translationStatus());
        $this->assertSame('fig-jam', $product->translation('en')->slug);
        $this->assertCount(1, $product->ingredients);
        $this->assertCount(1, $product->nutritionFacts);
        $this->assertCount(1, $product->variants);
        $this->assertCount(2, $product->tags);
        $this->assertNotNull($product->published_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'created', 'subject_type' => 'product', 'subject_id' => $product->id]);
    }

    public function test_persian_title_is_required(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', ['translations' => ['en' => ['title' => 'x']], 'status' => 'draft', 'stock_status' => 'in_stock'])
            ->assertSessionHasErrors('translations.fa.title');
    }

    public function test_media_upload_validates_and_generates_variants(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/admin/media', ['files' => [UploadedFile::fake()->image('pack.png', 1200, 900)]])->assertOk()->assertJsonPath('items.0.id', fn ($id) => $id > 0);
        $media = Media::latest('id')->first();
        $this->assertSame('done', $media->conversion_status); // sync queue in tests
        $this->assertArrayHasKey('large.webp', $media->variants);
        $this->assertArrayHasKey('thumbnail', $media->variants);
        Storage::disk('public')->assertExists($media->path()); // original kept

        $fake = UploadedFile::fake()->createWithContent('evil.png', '<?php echo 1;');
        $this->actingAs($admin)->postJson('/admin/media', ['files' => [$fake]])->assertStatus(422);

        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>');
        $this->actingAs($admin)->postJson('/admin/media', ['files' => [$svg]])->assertStatus(422);
    }

    public function test_translator_role_is_limited_to_translations(): void
    {
        $translator = $this->admin('translator');
        $product = $this->product();
        $this->actingAs($translator)->get('/admin/products')->assertForbidden();
        $this->actingAs($translator)->get('/admin/settings')->assertForbidden();
        $this->actingAs($translator)->get('/admin/translations')->assertOk();

        $this->actingAs($translator)->put("/admin/translations/products/{$product->id}", [
            'translations' => ['fa' => ['title' => ''], 'ar' => ['title' => 'منتج تجريبي']],
        ])->assertRedirect();
        $product->refresh();
        $this->assertSame('منتج تجريبي', $product->tr('title', 'ar', false));
        $this->assertSame('محصول آزمایشی', $product->tr('title', 'fa', false), 'Persian source must never be wiped by a translator');
    }

    public function test_page_builder_add_reorder_toggle_and_edit_section(): void
    {
        $admin = $this->admin();
        $page = Page::where('key', 'home')->first();
        $this->actingAs($admin)->post("/admin/pages/{$page->id}/sections", ['type' => 'rich_text'])->assertRedirect();
        $section = PageSection::latest('id')->first();

        $this->actingAs($admin)->put("/admin/pages/{$page->id}/sections/{$section->id}", [
            'is_active' => 1,
            'translations' => ['fa' => ['title' => 'عنوان متن', 'body' => '<p>بدنه</p>'], 'en' => ['title' => 'Text title']],
        ])->assertRedirect();
        $this->assertSame('عنوان متن', $section->fresh()->tr('title', 'fa'));
        $this->get('/fa')->assertSee('عنوان متن')->assertSee('بدنه');

        $ids = $page->sections()->pluck('id')->reverse()->values()->all();
        $this->actingAs($admin)->postJson("/admin/pages/{$page->id}/sections/reorder", ['ids' => $ids])->assertOk();
        $this->assertSame($section->id, $page->sections()->orderBy('sort_order')->first()->id);

        $this->actingAs($admin)->patch("/admin/pages/{$page->id}/sections/{$section->id}/toggle")->assertRedirect();
        $this->assertFalse($section->fresh()->is_active);
        $this->get('/fa')->assertDontSee('عنوان متن');
    }

    public function test_settings_save_translatable_values_and_theme_tokens(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put('/admin/settings/theme', ['color_primary' => '#123456'])->assertRedirect();
        $this->actingAs($admin)->put('/admin/settings/header', ['topbar_enabled' => 1, 'topbar_text' => ['fa' => 'ارسال به سراسر ایران', 'en' => 'Nationwide delivery']])->assertRedirect();
        $this->get('/fa')->assertSee('--c-primary:#123456', false)->assertSee('ارسال به سراسر ایران');
        $this->get('/en')->assertSee('Nationwide delivery');
    }

    public function test_two_factor_flow(): void
    {
        $admin = $this->admin();
        $tf = app(TwoFactorService::class);
        $this->actingAs($admin)->post('/admin/profile/two-factor')->assertRedirect();
        $secret = session('2fa_pending_secret');
        $this->assertNotNull($secret);
        $code = $tf->codeAt($secret, (int) floor(time() / 30));
        $this->actingAs($admin)->post('/admin/profile/two-factor/confirm', ['code' => $code])->assertRedirect();
        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());

        // A new session must pass the challenge.
        $this->flushSession();
        $this->actingAs($admin->fresh())->get('/admin')->assertRedirect('/admin/two-factor');
        $this->post('/admin/two-factor', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/admin/two-factor', ['code' => $tf->codeAt($secret, (int) floor(time() / 30))])->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
    }

    public function test_redirect_admin_normalizes_paths(): void
    {
        $this->actingAs($this->admin())->post('/admin/redirects', ['from_path' => 'https://surufood.ir/shop/jam/', 'to_path' => '/fa/products', 'status_code' => 301, 'is_active' => 1])->assertRedirect();
        $this->assertDatabaseHas('redirects', ['from_path' => '/shop/jam']);
    }
}
