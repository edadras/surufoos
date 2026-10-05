<?php

namespace Tests\Feature;

use App\Models\ContentImportLog;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Redirect;
use App\Services\Import\SuruFoodImporter;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The importer is exercised against faked HTTP responses shaped like the
 * WordPress REST and WooCommerce Store APIs. These fixtures only test the
 * mapping logic; production content comes from the live source site.
 */
class ImporterTest extends TestCase
{
    use RefreshDatabase;

    private function png(int $seed = 0): string
    {
        $img = imagecreatetruecolor(40, 40);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        imagefilledrectangle($img, 10, 5, 30, 35, imagecolorallocate($img, 200, 40 + $seed % 200, 50));
        ob_start();
        imagepng($img);

        return ob_get_clean();
    }

    private function fakeSource(): void
    {
        $base = 'https://source.test';
        Http::fake([
            "{$base}/wp-json/" => Http::response(['namespaces' => ['wp/v2', 'wc/store/v1']]),
            "{$base}/wp-json/wc/store/v1/products/categories*" => Http::response([
                ['id' => 10, 'name' => 'مربا', 'slug' => '%d9%85%d8%b1%d8%a8%d8%a7', 'parent' => 0, 'count' => 1, 'description' => 'مرباهای خانگی', 'image' => ['src' => "{$base}/img/cat.png", 'alt' => 'مربا'], 'permalink' => "{$base}/product-category/jam/"],
            ]),
            "{$base}/wp-json/wc/store/v1/products*" => Http::response([[
                'id' => 501, 'name' => 'مربای آلبالو ۳۰۰ گرمی', 'slug' => 'sour-cherry', 'sku' => 'SC-300', 'permalink' => "{$base}/product/sour-cherry/",
                'short_description' => '<p>کوتاه</p>', 'description' => '<p>بلند <script>x()</script></p>',
                'prices' => ['price' => '1200000', 'regular_price' => '1200000', 'sale_price' => '1200000', 'currency_code' => 'IRR', 'currency_minor_unit' => 0],
                'images' => [['src' => "{$base}/img/pack-300x300.png", 'alt' => 'بسته']],
                'categories' => [['id' => 10, 'slug' => 'jam']],
                'attributes' => [['name' => 'طعم', 'taxonomy' => 'pa_taste', 'terms' => [['name' => 'ترش', 'slug' => 'sour']]]],
                'is_in_stock' => true,
            ]], 200, ['X-WP-TotalPages' => 1]),
            "{$base}/wp-json/wp/v2/pages*" => Http::response([[
                'id' => 7, 'slug' => 'about-us', 'link' => "{$base}/about-us/", 'title' => ['rendered' => 'درباره ما'],
                'content' => ['rendered' => '<p>داستان ما</p><img src="'.$base.'/img/factory.png" alt="کارخانه">'], 'excerpt' => ['rendered' => ''], 'date_gmt' => '2024-01-01T00:00:00',
            ]], 200, ['X-WP-TotalPages' => 1]),
            "{$base}/wp-json/wp/v2/categories*" => Http::response([['id' => 3, 'name' => 'تغذیه', 'slug' => 'nutrition', 'count' => 1, 'link' => "{$base}/category/nutrition/"]], 200, ['X-WP-TotalPages' => 1]),
            "{$base}/wp-json/wp/v2/posts*" => Http::response([[
                'id' => 90, 'slug' => 'post-one', 'link' => "{$base}/post-one/", 'status' => 'publish', 'title' => ['rendered' => 'مقاله اول'],
                'content' => ['rendered' => '<p>متن</p>'], 'excerpt' => ['rendered' => '<p>خلاصه</p>'], 'date_gmt' => '2024-02-01T10:00:00', 'categories' => [3],
            ]], 200, ['X-WP-TotalPages' => 1]),
            "{$base}/img/*" => fn ($request) => Http::response($this->png(crc32($request->url())), 200, ['Content-Type' => 'image/png']),
            "{$base}" => Http::response('<html><head><meta property="og:site_name" content="سورو"></head><body><header><img class="custom-logo" src="/img/logo.png" alt="سورو"></header>'
                .'<a href="tel:+982112345678">۰۲۱-۱۲۳۴۵۶۷۸</a><a href="mailto:info@source.test">ایمیل</a><a href="https://instagram.com/suru">ig</a></body></html>'),
            "{$base}/" => Http::response('<html><head><meta property="og:site_name" content="سورو"></head><body><header><img class="custom-logo" src="/img/logo.png" alt="سورو"></header>'
                .'<a href="tel:+982112345678">۰۲۱-۱۲۳۴۵۶۷۸</a><a href="mailto:info@source.test">ایمیل</a><a href="https://instagram.com/suru">ig</a></body></html>'),
        ]);
    }

    public function test_full_import_maps_content_logs_sources_and_creates_redirects(): void
    {
        Storage::fake('public');
        $this->seedStructure();
        $this->fakeSource();

        $counts = app(SuruFoodImporter::class)->run('https://source.test');
        $this->assertSame(0, $counts['failed'], json_encode(ContentImportLog::where('status', 'failed')->pluck('message')));

        $product = Product::with('translations')->first();
        $this->assertSame('مربای آلبالو ۳۰۰ گرمی', $product->tr('title', 'fa'));
        $this->assertSame(120000, $product->price, 'IRR is converted to toman');
        $this->assertSame(300.0, $product->net_weight);
        $this->assertSame('g', $product->weight_unit);
        $this->assertSame('SC-300', $product->sku);
        $this->assertNotNull($product->main_media_id);
        $this->assertSame($product->main_media_id, $product->packshot_media_id, 'transparent PNG is detected as packshot');
        $this->assertNotNull($product->theme_color);
        $this->assertStringNotContainsString('<script', $product->tr('description', 'fa'));
        $this->assertCount(1, $product->categories);
        $this->assertSame('ترش', $product->attributeValues->first()->label('fa'));
        $this->assertFalse($product->hasTranslation('en'), 'only the source language is imported');
        $this->assertSame('https://source.test/img/pack.png', $product->mainImage->source_url, 'full-size original preferred over WP thumbnail');

        $about = Page::where('key', 'about')->first();
        $this->assertStringContainsString('داستان ما', $about->tr('description', 'fa'));
        $this->assertStringNotContainsString('source.test/img', $about->tr('description', 'fa'), 'content images are localized, never hotlinked');

        $post = Post::first();
        $this->assertSame('مقاله اول', $post->tr('title', 'fa'));
        $this->assertNotNull($post->post_category_id);

        $settings = app(SettingsService::class);
        $settings->flush();
        $this->assertSame('سورو', $settings->get('site_name', null, 'fa'));
        $this->assertSame('info@source.test', $settings->get('email'));
        $this->assertSame('https://instagram.com/suru', $settings->get('instagram'));
        $this->assertNotNull($settings->get('logo_media_id'));
        $this->assertSame('021-12345678', $settings->get('phones'));

        $this->assertTrue(Redirect::where('from_path', '/product/sour-cherry')->exists());
        $this->assertDatabaseHas('content_import_logs', ['source_type' => 'product', 'source_url' => 'https://source.test/product/sour-cherry/', 'status' => 'imported', 'destination_type' => 'product']);

        // Second run: unchanged items are skipped, nothing is duplicated.
        $again = app(SuruFoodImporter::class)->run('https://source.test');
        $this->assertSame(1, Product::count());
        $this->assertGreaterThan(0, $again['skipped']);
        $this->get($product->url('fa'))->assertOk();
    }

    public function test_unreachable_source_is_logged_as_failure(): void
    {
        Http::fake(['*' => Http::response('', 503)]);
        $counts = app(SuruFoodImporter::class)->run('https://down.test');
        $this->assertSame(1, $counts['failed']);
        $this->assertDatabaseHas('content_import_logs', ['source_type' => 'site', 'status' => 'failed']);
    }
}
