<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Ingredient;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Slider;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    public function test_root_redirects_to_a_locale(): void
    {
        $this->get('/', ['Accept-Language' => 'fa-IR,fa;q=0.9'])->assertRedirect('/fa');
        $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertRedirect('/en');
    }

    public function test_empty_site_renders_in_all_locales_with_correct_direction(): void
    {
        foreach (['fa' => 'rtl', 'en' => 'ltr', 'ar' => 'rtl'] as $locale => $dir) {
            $this->get("/{$locale}")->assertOk()->assertSee("dir=\"{$dir}\"", false)->assertSee("lang=\"{$locale}\"", false);
            foreach (['products', 'categories', 'about', 'contact', 'magazine', 'recipes', 'search?q=x', 'favorites'] as $path) {
                $this->get("/{$locale}/{$path}")->assertOk();
            }
        }
        $this->get('/xx/products')->assertNotFound();
    }

    public function test_home_renders_real_data_sections_and_hides_empty_ones(): void
    {
        $category = $this->category(['fa' => 'مربا', 'en' => 'Jams']);
        $product = $this->product(['fa' => 'مربای آلبالو', 'en' => 'Sour cherry jam'], ['is_featured' => true, 'is_new' => true, 'show_inside' => true], $category);
        $ingredient = Ingredient::create([]);
        $ingredient->saveTranslations(['fa' => ['name' => 'آلبالو']], 'name');
        $product->ingredients()->attach($ingredient, ['sort_order' => 0]);

        $slider = Slider::create(['placement' => 'home_hero', 'product_id' => $product->id, 'is_active' => true]);
        $slider->saveTranslations(['fa' => ['title' => 'عنوان هیرو']]);

        $html = $this->get('/fa')->assertOk()->getContent();
        $this->assertStringContainsString('عنوان هیرو', $html);
        $this->assertStringContainsString('data-hero', $html);
        $this->assertStringContainsString('مربای آلبالو', $html);
        $this->assertStringContainsString('data-universe', $html);
        $this->assertStringContainsString('data-inside', $html);
        $this->assertStringContainsString('data-shelf', $html);
        // No banners, journey entries or recipes exist → those sections must not render placeholders.
        $this->assertStringNotContainsString('data-journey', $html);
        $this->assertStringNotContainsString('banners section', $html);

        // English home shows the translated product; Arabic has no translation so it is hidden (strict mode).
        $this->assertStringContainsString('Sour cherry jam', $this->get('/en')->getContent());
        $this->assertStringNotContainsString('مربای آلبالو', $this->get('/ar')->getContent());
    }

    public function test_product_page_has_seo_schema_hreflang_and_counts_views(): void
    {
        $category = $this->category(['fa' => 'مربا', 'en' => 'Jams']);
        $product = $this->product(['fa' => 'مربای آلبالو', 'en' => 'Sour cherry jam'], ['price' => 120000, 'net_weight' => 300, 'weight_unit' => 'g'], $category);
        $url = $product->url('fa');

        $html = $this->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('"@type":"Product"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('rel="alternate" hreflang="en"', $html);
        $this->assertStringNotContainsString('rel="alternate" hreflang="ar"', $html);
        $this->assertStringContainsString('۳۰۰ گرم', $html);
        $this->assertStringContainsString('۱۲۰٬۰۰۰', $html);
        $this->assertSame(1, $product->fresh()->views_count);

        $this->get($product->url('en'))->assertOk()->assertSee('300 g');
        $this->get(route('products.quick-view', ['locale' => 'fa', 'product' => $product->id]))->assertOk()->assertSee('مربای آلبالو');
    }

    public function test_slugs_are_independent_per_locale_and_wrong_locale_slug_redirects(): void
    {
        $product = $this->product(['fa' => 'عسل طبیعی', 'en' => 'Natural honey']);
        $this->assertStringContainsString('/fa/products/عسل-طبیعی', rawurldecode($product->url('fa')));
        $this->assertStringContainsString('/en/products/natural-honey', $product->url('en'));
        $this->get('/fa/products/natural-honey')->assertRedirect($product->url('fa'));
    }

    public function test_catalog_filters_by_category_attribute_price_and_returns_json_for_ajax(): void
    {
        $jam = $this->category(['fa' => 'مربا']);
        $honey = $this->category(['fa' => 'عسل']);
        $sweet = Attribute::where('code', 'taste')->first()->values()->where('code', 'sweet')->first();
        $a = $this->product(['fa' => 'مربای هویج'], ['price' => 50000], $jam);
        $a->attributeValues()->attach($sweet);
        $this->product(['fa' => 'عسل کوهی'], ['price' => 300000], $honey);

        $slug = $jam->translation('fa')->slug;
        $this->get('/fa/products?category='.urlencode($slug))->assertSee('مربای هویج')->assertDontSee('عسل کوهی');
        $this->get('/fa/products?attr[taste][]=sweet')->assertSee('مربای هویج')->assertDontSee('عسل کوهی');
        $this->get('/fa/products?max=100000')->assertSee('مربای هویج')->assertDontSee('عسل کوهی');
        $this->getJson('/fa/products?sort=price_desc')->assertOk()->assertJsonStructure(['html', 'total', 'url'])->assertJsonPath('total', 2);
        $this->get('/fa/category/'.$slug)->assertOk()->assertSee('مربای هویج')->assertDontSee('عسل کوهی');
    }

    public function test_search_normalizes_arabic_and_persian_letters(): void
    {
        $this->product(['fa' => 'کیک یزدی']);
        // Typed with Arabic kaf/yeh.
        $this->getJson('/fa/search/live?q='.urlencode('كيك'))->assertOk()->assertJsonPath('total', 1);
        $this->get('/fa/search?q='.urlencode('یزدي'))->assertSee('کیک یزدی');
        $this->assertDatabaseHas('search_terms', ['term' => 'کیک', 'locale' => 'fa']);
    }

    public function test_taste_finder_ranks_products_by_matching_attributes(): void
    {
        $values = Attribute::with('values')->get()->flatMap->values->keyBy('code');
        $best = $this->product(['fa' => 'محصول صبحانه شیرین']);
        $best->attributeValues()->attach([$values['sweet']->id, $values['breakfast']->id]);
        $other = $this->product(['fa' => 'محصول شیرین']);
        $other->attributeValues()->attach([$values['sweet']->id]);

        $res = $this->postJson('/fa/taste-finder', ['values' => [$values['sweet']->id, $values['breakfast']->id]])->assertOk();
        $this->assertSame(2, $res->json('count'));
        $html = $res->json('html');
        $this->assertLessThan(strpos($html, 'محصول شیرین<'), strpos($html, 'محصول صبحانه شیرین'));
    }

    public function test_contact_form_stores_message_and_rejects_honeypot(): void
    {
        $this->post('/fa/contact', ['name' => 'سارا', 'mobile' => '۰۹۱۲۳۴۵۶۷۸۹', 'message' => 'سلام، سؤال دارم'])->assertRedirect();
        $this->assertDatabaseHas('contacts', ['name' => 'سارا', 'mobile' => '09123456789', 'locale' => 'fa']);

        $this->post('/fa/contact', ['name' => 'bot', 'email' => 'a@b.co', 'message' => 'spam spam', 'website' => 'x'])->assertSessionHasErrors('website');
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_favorites_toggle_for_guests(): void
    {
        $product = $this->product();
        $this->postJson("/fa/favorites/{$product->id}")->assertJson(['active' => true, 'count' => 1]);
        $this->get('/fa/favorites')->assertSee('محصول آزمایشی');
        $this->postJson("/fa/favorites/{$product->id}")->assertJson(['active' => false, 'count' => 0]);
    }

    public function test_cart_and_checkout_when_commerce_enabled(): void
    {
        $this->get('/fa/cart')->assertNotFound();
        app(SettingsService::class)->set('commerce_enabled', '1');
        app(SettingsService::class)->set('shipping_flat', '30000');
        app(SettingsService::class)->flush();

        $product = $this->product(['fa' => 'محصول فروشی'], ['price' => 100000, 'stock_qty' => 5]);
        $this->postJson('/fa/cart', ['product_id' => $product->id, 'quantity' => 2])->assertOk()->assertJson(['count' => 2]);
        $this->get('/fa/cart')->assertOk()->assertSee('محصول فروشی');

        $this->post('/fa/checkout', [
            'customer_name' => 'علی', 'customer_mobile' => '09120000000', 'province' => 'تهران', 'city' => 'تهران',
            'address' => 'خیابان نمونه', 'payment_method' => 'cod',
        ])->assertRedirect();
        $this->assertDatabaseHas('orders', ['customer_name' => 'علی', 'subtotal' => 200000, 'shipping_total' => 30000, 'grand_total' => 230000]);
        $this->assertSame(3, $product->fresh()->stock_qty);
    }

    public function test_sitemaps_list_translated_urls_with_alternates(): void
    {
        $product = $this->product(['fa' => 'زعفران', 'en' => 'Saffron']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('sitemaps/fa-products.xml', false);
        $xml = $this->get('/sitemaps/en-products.xml')->assertOk()->getContent();
        $this->assertStringContainsString($product->url('en'), $xml);
        $this->assertStringContainsString('hreflang="fa"', $xml);
        $this->assertStringNotContainsString('/ar/products', $this->get('/sitemaps/ar-products.xml')->getContent());
    }

    public function test_admin_redirects_apply_to_old_urls(): void
    {
        Redirect::create(['from_path' => '/product/old-jam', 'to_path' => '/fa/products', 'status_code' => 301, 'is_active' => true]);
        $this->get('/product/old-jam')->assertRedirect('/fa/products')->assertStatus(301);
        $this->assertSame(1, Redirect::first()->hits);
    }

    public function test_about_page_uses_seeded_system_page(): void
    {
        $page = Page::where('key', 'about')->first();
        $page->saveTranslations(['fa' => ['title' => 'درباره سورو', 'description' => '<p>متن معرفی</p><script>alert(1)</script>']]);
        $this->get('/fa/about')->assertOk()->assertSee('متن معرفی')->assertDontSee('<script>alert(1)</script>', false);
    }
}
