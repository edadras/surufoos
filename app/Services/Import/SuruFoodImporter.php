<?php

namespace App\Services\Import;

use App\Enums\ContentStatus;
use App\Enums\StockStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\Redirect;
use App\Services\MediaService;
use App\Services\SettingsService;
use App\Support\CacheGroups;
use App\Support\TextNormalizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * Migrates the real Suru Food content into the new schema.
 *
 * Strategy, in order of fidelity:
 *  1. WordPress REST API (pages, posts, categories) and WooCommerce Store API
 *     (products, product categories, attributes, prices, stock, images).
 *  2. Sitemap crawl with JSON-LD / OpenGraph / microdata extraction for any
 *     site that does not expose those APIs.
 * Brand settings (logo, phones, email, address, socials) are read from the
 * homepage. Nothing is invented: fields the source does not provide stay empty.
 * Imported content lands in Persian (the source language); EN/AR are filled
 * later in the translation manager.
 */
class SuruFoodImporter
{
    public const SECTIONS = ['settings', 'categories', 'products', 'pages', 'posts', 'redirects'];

    private SourceClient $client;

    private ImportLogger $log;

    private ContentImages $images;

    private string $host;

    private bool $hasWooStore = false;

    private bool $hasWp = false;

    /** @var array<int, int> remote category id => local id */
    private array $categoryMap = [];

    /** @var array<string, string> old path => new path */
    private array $redirects = [];

    public function __construct(private MediaService $media, private SettingsService $settings) {}

    /**
     * @param  array<int, string>  $only  sections to run (empty = all)
     * @return array<string, int> counts per status
     */
    public function run(string $source, array $only = [], ?callable $output = null): array
    {
        $this->client = new SourceClient($source);
        $this->log = new ImportLogger($output);
        $this->images = new ContentImages($this->media, $this->log);
        $this->host = parse_url($this->client->base(), PHP_URL_HOST) ?? '';
        $sections = $only ?: self::SECTIONS;

        if (! $this->client->isReachable()) {
            $this->log->failed('site', $source, $source, 'Source site is not reachable from this server.');

            return $this->log->counts;
        }

        $this->detect();
        $this->log->say(sprintf('Source: %s — WordPress API: %s, WooCommerce Store API: %s', $source, $this->hasWp ? 'yes' : 'no', $this->hasWooStore ? 'yes' : 'no'));

        foreach (['settings', 'categories', 'products', 'pages', 'posts'] as $section) {
            if (! in_array($section, $sections, true)) {
                continue;
            }
            $this->log->say("== {$section}");
            try {
                $this->{'import'.ucfirst($section)}();
            } catch (Throwable $e) {
                $this->log->failed($section, 'section', $source, $e);
            }
        }

        if (in_array('redirects', $sections, true)) {
            $this->saveRedirects();
        }

        CacheGroups::flush(...array_values((new \ReflectionClass(CacheGroups::class))->getConstants()));

        return $this->log->counts;
    }

    private function detect(): void
    {
        $index = $this->client->json('wp-json/');
        $namespaces = $index['namespaces'] ?? [];
        $this->hasWp = in_array('wp/v2', $namespaces, true);
        $this->hasWooStore = in_array('wc/store/v1', $namespaces, true) || in_array('wc/store', $namespaces, true);
    }

    // ---------------------------------------------------------------- settings

    private function importSettings(): void
    {
        $html = $this->client->html('/');
        $crawler = new Crawler($html, $this->client->base());
        $found = [];

        $title = trim($crawler->filter('meta[property="og:site_name"]')->count() ? $crawler->filter('meta[property="og:site_name"]')->attr('content') : '');
        if ($title) {
            $found['site_name'] = ['fa' => $title];
        }
        if ($crawler->filter('meta[name="description"]')->count()) {
            $found['default_description'] = ['fa' => trim($crawler->filter('meta[name="description"]')->attr('content'))];
        }

        // Logo: custom-logo (WordPress), or the first image in the header whose class/src/alt mentions "logo".
        $logo = $crawler->filter('.custom-logo, header img[class*="logo"], header img[src*="logo"], .logo img, #logo img')->first();
        if ($logo->count()) {
            $src = $logo->attr('data-src') ?: $logo->attr('src');
            if ($media = $this->images->fetch($this->absolute($src), $title ?: $logo->attr('alt'))) {
                $found['logo_media_id'] = $media->id;
            }
        }
        $icon = $crawler->filter('link[rel="icon"], link[rel="apple-touch-icon"]')->last();
        if ($icon->count() && ($media = $this->images->fetch($this->absolute($icon->attr('href'))))) {
            $found['favicon_media_id'] = $media->id;
        }

        $phones = [];
        $crawler->filter('a[href^="tel:"]')->each(function (Crawler $a) use (&$phones) {
            $number = trim(TextNormalizer::normalize(str_replace('tel:', '', urldecode($a->attr('href')))));
            $number = preg_replace('/[^\d+]/', '', $number);
            if (strlen($number) >= 7) {
                $phones[$number] = trim($a->text('')) ?: $number;
            }
        });
        if ($phones) {
            // Keep the number as the brand displays it (dashes, spacing), with Latin digits so tel: links work.
            $found['phones'] = implode("\n", array_unique(array_values(array_map(fn ($t) => trim(TextNormalizer::latinDigits($t)), $phones))));
        }

        $crawler->filter('a[href^="mailto:"]')->each(function (Crawler $a) use (&$found) {
            $found['email'] ??= strtolower(trim(str_replace('mailto:', '', explode('?', $a->attr('href'))[0])));
        });

        $socialHosts = ['instagram.com' => 'instagram', 't.me' => 'telegram', 'telegram.me' => 'telegram', 'wa.me' => 'whatsapp', 'whatsapp.com' => 'whatsapp',
            'linkedin.com' => 'linkedin', 'aparat.com' => 'aparat', 'youtube.com' => 'youtube', 'twitter.com' => 'x', 'x.com' => 'x', 'eitaa.com' => 'eitaa', 'ble.ir' => 'bale'];
        $crawler->filter('a[href]')->each(function (Crawler $a) use (&$found, $socialHosts) {
            $host = preg_replace('/^www\./', '', (string) parse_url($a->attr('href'), PHP_URL_HOST));
            if (isset($socialHosts[$host])) {
                $found[$socialHosts[$host]] ??= $a->attr('href');
            }
        });

        // Organization / LocalBusiness JSON-LD: address, legal name, logo.
        foreach ($this->jsonLd($crawler) as $node) {
            $type = (array) ($node['@type'] ?? []);
            if (array_intersect($type, ['Organization', 'LocalBusiness', 'Store', 'Corporation', 'FoodEstablishment'])) {
                if (! empty($node['address'])) {
                    $addr = is_array($node['address']) ? implode('، ', array_filter(Arr::only($node['address'], ['addressRegion', 'addressLocality', 'streetAddress']))) : $node['address'];
                    $found['address'] = ['fa' => $addr];
                }
                if (! empty($node['legalName'])) {
                    $found['legal_name'] = ['fa' => $node['legalName']];
                }
                if (! empty($node['telephone']) && empty($found['phones'])) {
                    $found['phones'] = $node['telephone'];
                }
            }
        }

        $hash = $this->log->hash($found);
        foreach ($found as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $locale => $v) {
                    $this->settings->set($key, $v, $locale);
                }
            } else {
                $this->settings->set($key, $value);
            }
        }
        $this->settings->flush();
        $this->log->record('settings', 'home', $this->client->base(), null, $hash, $found ? 'updated' : 'skipped',
            $found ? 'Fields: '.implode(', ', array_keys($found)) : 'No brand data found on the homepage.');
    }

    // -------------------------------------------------------------- categories

    private function importCategories(): void
    {
        if ($this->hasWooStore) {
            $rows = $this->client->json('wp-json/wc/store/v1/products/categories') ?? [];
            // Parents first so parent_id can be mapped.
            usort($rows, fn ($a, $b) => ($a['parent'] ?? 0) <=> ($b['parent'] ?? 0));
            foreach ($rows as $row) {
                if (($row['slug'] ?? '') === 'uncategorized' || ($row['count'] ?? 1) === 0 && empty($row['description'])) {
                    continue;
                }
                $this->saveCategory((int) $row['id'], [
                    'title' => $this->text($row['name'] ?? ''),
                    'slug' => rawurldecode($row['slug'] ?? ''),
                    'description' => $row['description'] ?? null,
                    'image' => $row['image']['src'] ?? null,
                    'image_alt' => $row['image']['alt'] ?? null,
                    'parent' => (int) ($row['parent'] ?? 0),
                    'url' => $row['permalink'] ?? $this->client->url('product-category/'.$row['slug']),
                ]);
            }

            return;
        }

        if ($this->hasWp) {
            foreach ($this->client->paginate('wp-json/wp/v2/product_cat') as $row) {
                $this->saveCategory((int) $row['id'], [
                    'title' => $this->text($row['name'] ?? ''), 'slug' => rawurldecode($row['slug'] ?? ''),
                    'description' => $row['description'] ?? null, 'image' => null, 'image_alt' => null,
                    'parent' => (int) ($row['parent'] ?? 0), 'url' => $row['link'] ?? '',
                ]);
            }
        }
    }

    private function saveCategory(int $remoteId, array $row): void
    {
        $hash = $this->log->hash($row);
        if ($this->log->unchanged('category', $remoteId, $hash)) {
            $this->categoryMap[$remoteId] = $this->log->destination('category', $remoteId)->id;
            $this->log->record('category', $remoteId, $row['url'], $this->log->destination('category', $remoteId), $hash, 'skipped');

            return;
        }

        try {
            $category = DB::transaction(function () use ($remoteId, $row) {
                $category = $this->log->destination('category', $remoteId) ?? new Category(['status' => ContentStatus::Published]);
                $category->parent_id = $row['parent'] ? ($this->categoryMap[$row['parent']] ?? null) : null;
                if ($row['image'] && ($media = $this->images->fetch($row['image'], $row['image_alt'] ?: $row['title']))) {
                    $category->image_media_id = $media->id;
                    $category->theme_color ??= $media->dominant_color;
                }
                $category->sort_order ??= Category::max('sort_order') + 1;
                $category->save();
                $category->saveTranslations(['fa' => [
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'short_description' => Str::limit($this->text($row['description']), 300, '') ?: null,
                    'description' => $this->html($row['description']),
                ]]);

                return $category;
            });
            $this->categoryMap[$remoteId] = $category->id;
            $this->remember($row['url'], fn () => $category->url('fa'));
            $this->log->record('category', $remoteId, $row['url'], $category, $this->log->hash($row), $category->wasRecentlyCreated ? 'imported' : 'updated');
        } catch (Throwable $e) {
            $this->log->failed('category', $remoteId, $row['url'], $e);
        }
    }

    // ---------------------------------------------------------------- products

    private function importProducts(): void
    {
        if ($this->hasWooStore) {
            foreach ($this->client->paginate('wp-json/wc/store/v1/products') as $row) {
                $this->saveWooProduct($row);
            }

            return;
        }

        // Fallback: crawl product URLs from the sitemap and read JSON-LD Product data.
        foreach ($this->sitemapUrls() as $url) {
            if (preg_match('~/(product|products|محصول)/~u', rawurldecode($url))) {
                $this->saveCrawledProduct($url);
            }
        }
    }

    private function saveWooProduct(array $row): void
    {
        $remoteId = (int) $row['id'];
        $url = $row['permalink'] ?? $this->client->url('?p='.$remoteId);
        $hash = $this->log->hash($row);
        if ($this->log->unchanged('product', $remoteId, $hash)) {
            $this->log->record('product', $remoteId, $url, $this->log->destination('product', $remoteId), $hash, 'skipped');

            return;
        }

        try {
            $product = DB::transaction(function () use ($row, $remoteId) {
                $product = $this->log->destination('product', $remoteId) ?? new Product;
                $title = $this->text($row['name'] ?? '');

                $prices = $row['prices'] ?? [];
                $minor = (int) ($prices['currency_minor_unit'] ?? 0);
                $toToman = fn ($v) => $v === null || $v === '' ? null
                    : (int) round(((int) $v / (10 ** $minor)) / (strtoupper($prices['currency_code'] ?? 'IRT') === 'IRR' ? 10 : 1));
                $regular = $toToman($prices['regular_price'] ?? null) ?: null;
                $sale = $toToman($prices['sale_price'] ?? null) ?: null;

                $product->fill([
                    'sku' => ($row['sku'] ?? '') !== '' && ! Product::where('sku', $row['sku'])->whereKeyNot($product->id)->exists() ? $row['sku'] : $product->sku,
                    'status' => ContentStatus::Published,
                    'price' => $regular,
                    'sale_price' => $sale && $regular && $sale < $regular ? $sale : null,
                    'stock_status' => ($row['is_in_stock'] ?? true) ? StockStatus::InStock : (($row['is_on_backorder'] ?? false) ? StockStatus::Preorder : StockStatus::OutOfStock),
                    'is_featured' => (bool) ($row['featured'] ?? $product->is_featured),
                ]);
                $product->published_at ??= now();

                [$weight, $unit] = $this->weightFrom($row['attributes'] ?? [], $title);
                if ($weight) {
                    $product->net_weight = $weight;
                    $product->weight_unit = $unit;
                }

                $images = collect($row['images'] ?? [])->map(fn ($img) => $this->images->fetch($img['src'] ?? null, $img['alt'] ?: $title))->filter()->values();
                if ($images->isNotEmpty()) {
                    $product->main_media_id = $images[0]->id;
                    $cutout = $images->first(fn ($m) => $m->has_transparency);
                    $product->packshot_media_id = $cutout?->id;
                    $product->theme_color ??= ($cutout ?? $images[0])->dominant_color;
                }

                $categoryIds = collect($row['categories'] ?? [])->map(fn ($c) => $this->categoryMap[$c['id']] ?? $this->localCategoryBySlug(rawurldecode($c['slug'] ?? '')))->filter()->unique()->values();
                $product->primary_category_id = $categoryIds->first();
                $product->save();

                $product->saveTranslations(['fa' => [
                    'title' => $title,
                    'slug' => rawurldecode($row['slug'] ?? ''),
                    'short_description' => $this->html($row['short_description'] ?? '') ?: null,
                    'description' => $this->html($row['description'] ?? '') ?: null,
                ]]);
                $product->categories()->sync($categoryIds);

                $product->images()->delete();
                foreach ($images->slice(1)->values() as $i => $media) {
                    $product->images()->create(['media_id' => $media->id, 'type' => 'gallery', 'sort_order' => $i]);
                }

                $this->syncAttributes($product, $row['attributes'] ?? []);

                return $product;
            });

            $this->remember($row['permalink'] ?? null, fn () => $product->url('fa'));
            $this->log->record('product', $remoteId, $url, $product, $hash, $product->wasRecentlyCreated ? 'imported' : 'updated');
        } catch (Throwable $e) {
            $this->log->failed('product', $remoteId, $url, $e);
        }
    }

    private function saveCrawledProduct(string $url): void
    {
        try {
            $html = $this->client->html($url);
            $crawler = new Crawler($html, $url);
            $node = collect($this->jsonLd($crawler))->first(fn ($n) => in_array('Product', (array) ($n['@type'] ?? []), true));
            $title = $node['name'] ?? $this->meta($crawler, 'og:title');
            if (! $title) {
                $this->log->failed('product', $url, $url, 'No product data (JSON-LD/OpenGraph) on page.');

                return;
            }
            $payload = ['node' => $node, 'title' => $title, 'og' => $this->meta($crawler, 'og:image')];
            $hash = $this->log->hash($payload);
            if ($this->log->unchanged('product', $url, $hash)) {
                $this->log->record('product', $url, $url, $this->log->destination('product', $url), $hash, 'skipped');

                return;
            }

            $product = DB::transaction(function () use ($node, $title, $crawler, $url) {
                $product = $this->log->destination('product', $url) ?? new Product;
                $images = collect((array) ($node['image'] ?? $this->meta($crawler, 'og:image')))->map(fn ($i) => is_array($i) ? ($i['url'] ?? null) : $i)
                    ->filter()->map(fn ($src) => $this->images->fetch($this->absolute($src), $title))->filter()->values();
                $offer = $node['offers'] ?? [];
                $offer = isset($offer[0]) ? $offer[0] : $offer;
                $price = isset($offer['price']) ? (int) round((float) $offer['price'] / (strtoupper($offer['priceCurrency'] ?? 'IRR') === 'IRR' ? 10 : 1)) : null;

                $product->fill([
                    'sku' => $node['sku'] ?? $product->sku,
                    'status' => ContentStatus::Published,
                    'price' => $price ?: null,
                    'stock_status' => str_contains((string) ($offer['availability'] ?? 'InStock'), 'OutOfStock') ? StockStatus::OutOfStock : StockStatus::InStock,
                    'main_media_id' => $images->first()?->id,
                    'packshot_media_id' => $images->first(fn ($m) => $m->has_transparency)?->id,
                ]);
                $product->theme_color ??= $images->first()?->dominant_color;
                [$weight, $unit] = $this->weightFrom([], $title);
                $product->net_weight = $weight ?? $product->net_weight;
                $product->weight_unit = $unit ?? $product->weight_unit;
                $product->published_at ??= now();
                $product->save();

                $slug = rawurldecode(basename(parse_url($url, PHP_URL_PATH) ?: ''));
                $product->saveTranslations(['fa' => [
                    'title' => $this->text($title),
                    'slug' => $slug,
                    'short_description' => $this->text($node['description'] ?? $this->meta($crawler, 'og:description')) ?: null,
                    'description' => $this->html($this->mainContent($crawler)) ?: null,
                ]]);
                $product->images()->delete();
                foreach ($images->slice(1)->values() as $i => $media) {
                    $product->images()->create(['media_id' => $media->id, 'type' => 'gallery', 'sort_order' => $i]);
                }

                return $product;
            });
            $this->remember($url, fn () => $product->url('fa'));
            $this->log->record('product', $url, $url, $product, $hash, $product->wasRecentlyCreated ? 'imported' : 'updated');
        } catch (Throwable $e) {
            $this->log->failed('product', $url, $url, $e);
        }
    }

    private function syncAttributes(Product $product, array $attributes): void
    {
        $valueIds = [];
        foreach ($attributes as $attr) {
            $name = $this->text($attr['name'] ?? '');
            if (! $name || empty($attr['terms'])) {
                continue;
            }
            $code = $this->code($attr['taxonomy'] ?? $name);
            $attribute = Attribute::firstOrCreate(['code' => $code], ['type' => 'multi', 'is_filterable' => true]);
            if (! $attribute->hasTranslation('fa')) {
                $attribute->saveTranslations(['fa' => ['name' => $name]], 'name');
            }
            foreach ($attr['terms'] as $term) {
                $label = $this->text($term['name'] ?? '');
                if (! $label) {
                    continue;
                }
                $value = AttributeValue::firstOrCreate(['attribute_id' => $attribute->id, 'code' => $this->code($term['slug'] ?? $label)]);
                if (! $value->hasTranslation('fa')) {
                    $value->saveTranslations(['fa' => ['value' => $label]], 'value');
                }
                $valueIds[] = $value->id;
            }
        }
        $product->attributeValues()->sync($valueIds);
    }

    /** Net weight from a weight attribute or the product name (e.g. «۲۵۰ گرم», "1 kg"). */
    private function weightFrom(array $attributes, string $title): array
    {
        $candidates = [];
        foreach ($attributes as $attr) {
            if (preg_match('/وزن|weight|حجم|volume/iu', (string) ($attr['name'] ?? ''))) {
                foreach ($attr['terms'] ?? [] as $t) {
                    $candidates[] = $t['name'] ?? '';
                }
            }
        }
        $candidates[] = $title;
        $units = ['کیلوگرم' => 'kg', 'کیلو' => 'kg', 'kg' => 'kg', 'گرم' => 'g', 'گرمی' => 'g', 'g' => 'g', 'gr' => 'g',
            'میلی لیتر' => 'ml', 'میلی‌لیتر' => 'ml', 'ml' => 'ml', 'سی سی' => 'ml', 'لیتر' => 'l', 'l' => 'l'];
        foreach ($candidates as $text) {
            $text = TextNormalizer::normalize($text);
            if (preg_match('/(\d+(?:[.\/]\d+)?)\s*(کیلوگرم|کیلو|میلی لیتر|سی سی|لیتر|گرمی|گرم|kg|ml|gr|g|l)\b/u', $text, $m)) {
                return [(float) str_replace('/', '.', $m[1]), $units[$m[2]] ?? 'g'];
            }
        }

        return [null, null];
    }

    // ------------------------------------------------------------------- pages

    private function importPages(): void
    {
        if (! $this->hasWp) {
            return;
        }
        foreach ($this->client->paginate('wp-json/wp/v2/pages', ['_embed' => 1]) as $row) {
            $remoteId = (int) $row['id'];
            $slug = rawurldecode($row['slug'] ?? '');
            $title = $this->text($row['title']['rendered'] ?? '');
            $content = $row['content']['rendered'] ?? '';
            if (in_array($slug, ['cart', 'checkout', 'my-account', 'shop', 'wishlist', 'compare', 'sample-page'], true) || ! $title) {
                continue;
            }
            $hash = $this->log->hash([$title, $content, $row['modified'] ?? null]);
            if ($this->log->unchanged('page', $remoteId, $hash)) {
                $this->log->record('page', $remoteId, $row['link'] ?? '', $this->log->destination('page', $remoteId), $hash, 'skipped');

                continue;
            }

            try {
                $key = match (true) {
                    (bool) preg_match('/about|درباره/u', $slug.' '.$title) => 'about',
                    (bool) preg_match('/contact|تماس/u', $slug.' '.$title) => 'contact',
                    default => null,
                };
                $page = $this->log->destination('page', $remoteId)
                    ?? ($key ? Page::firstOrNew(['key' => $key]) : new Page);
                $page->status = ContentStatus::Published;
                $page->published_at ??= Carbon::parse($row['date_gmt'] ?? now());
                if ($cover = $this->featuredImage($row, $title)) {
                    $page->cover_media_id = $cover->id;
                }
                $page->save();
                $page->saveTranslations(['fa' => [
                    'title' => $title,
                    'slug' => $slug,
                    'short_description' => $this->text($row['excerpt']['rendered'] ?? '') ?: null,
                    'description' => $this->images->localizeHtml($this->html($content), $this->host),
                    'seo_description' => $row['yoast_head_json']['description'] ?? null,
                ]]);
                $this->remember($row['link'] ?? null, fn () => $page->url('fa'));
                $this->log->record('page', $remoteId, $row['link'] ?? '', $page, $hash, $page->wasRecentlyCreated ? 'imported' : 'updated', $key ? "Mapped to system page: {$key}" : null);
            } catch (Throwable $e) {
                $this->log->failed('page', $remoteId, $row['link'] ?? '', $e);
            }
        }
    }

    // ------------------------------------------------------------------- posts

    private function importPosts(): void
    {
        if (! $this->hasWp) {
            return;
        }
        $categoryMap = [];
        foreach ($this->client->paginate('wp-json/wp/v2/categories') as $row) {
            if (($row['count'] ?? 0) === 0 || ($row['slug'] ?? '') === 'uncategorized') {
                continue;
            }
            $existing = $this->log->destination('post_category', (int) $row['id']);
            $category = $existing ?? PostCategory::create(['sort_order' => (int) PostCategory::max('sort_order') + 1]);
            $category->saveTranslations(['fa' => [
                'title' => $this->text($row['name']), 'slug' => rawurldecode($row['slug']), 'short_description' => $this->text($row['description'] ?? '') ?: null,
            ]]);
            $categoryMap[$row['id']] = $category->id;
            $this->remember($row['link'] ?? null, fn () => $category->url('fa'));
            $this->log->record('post_category', (int) $row['id'], $row['link'] ?? '', $category, $this->log->hash($row), $existing ? 'updated' : 'imported');
        }

        foreach ($this->client->paginate('wp-json/wp/v2/posts', ['_embed' => 1]) as $row) {
            $remoteId = (int) $row['id'];
            $hash = $this->log->hash([$row['title'] ?? null, $row['content'] ?? null, $row['modified'] ?? null]);
            if ($this->log->unchanged('post', $remoteId, $hash)) {
                $this->log->record('post', $remoteId, $row['link'] ?? '', $this->log->destination('post', $remoteId), $hash, 'skipped');

                continue;
            }
            try {
                $title = $this->text($row['title']['rendered'] ?? '');
                $post = $this->log->destination('post', $remoteId) ?? new Post;
                $post->fill([
                    'status' => ($row['status'] ?? 'publish') === 'publish' ? ContentStatus::Published : ContentStatus::Draft,
                    'published_at' => Carbon::parse($row['date_gmt'] ?? $row['date'] ?? now(), 'UTC'),
                    'post_category_id' => $categoryMap[$row['categories'][0] ?? 0] ?? null,
                    'author_name' => $row['_embedded']['author'][0]['name'] ?? null,
                    'cover_media_id' => $this->featuredImage($row, $title)?->id,
                ]);
                $post->save();
                $post->saveTranslations(['fa' => [
                    'title' => $title,
                    'slug' => rawurldecode($row['slug'] ?? ''),
                    'short_description' => $this->text($row['excerpt']['rendered'] ?? '') ?: null,
                    'description' => $this->images->localizeHtml($this->html($row['content']['rendered'] ?? ''), $this->host),
                    'seo_description' => $row['yoast_head_json']['description'] ?? null,
                ]]);
                $this->remember($row['link'] ?? null, fn () => $post->url('fa'));
                $this->log->record('post', $remoteId, $row['link'] ?? '', $post, $hash, $post->wasRecentlyCreated ? 'imported' : 'updated');
            } catch (Throwable $e) {
                $this->log->failed('post', $remoteId, $row['link'] ?? '', $e);
            }
        }
    }

    // --------------------------------------------------------------- redirects

    /** Old surufood.ir URLs → new URLs, so links and search rankings carry over. */
    private function saveRedirects(): void
    {
        foreach ($this->redirects as $from => $to) {
            if (! $from || ! $to || $from === $to || $from === '/') {
                continue;
            }
            Redirect::updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301, 'is_active' => true]);
        }
        $this->log->say('Redirects saved: '.count($this->redirects));
    }

    private function remember(?string $oldUrl, callable $newUrl): void
    {
        if (! $oldUrl) {
            return;
        }
        $from = Redirect::normalizePath($oldUrl);
        $to = $newUrl();
        if ($to) {
            $this->redirects[$from] = parse_url($to, PHP_URL_PATH);
        }
    }

    // ----------------------------------------------------------------- helpers

    private function featuredImage(array $row, string $alt): ?\App\Models\Media
    {
        $media = $row['_embedded']['wp:featuredmedia'][0] ?? null;

        return $media ? $this->images->fetch($media['source_url'] ?? null, $media['alt_text'] ?: $alt) : null;
    }

    private function localCategoryBySlug(string $slug): ?int
    {
        return $slug ? Category::whereSlug($slug, 'fa')->value('id') : null;
    }

    /** @return array<int, string> */
    private function sitemapUrls(): array
    {
        $urls = [];
        $queue = ['sitemap.xml', 'sitemap_index.xml', 'wp-sitemap.xml'];
        $seen = [];
        while ($queue && count($seen) < 60) {
            $path = array_shift($queue);
            if (isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $res = $this->client->get($path);
            if (! $res->successful() || ! str_contains($res->body(), '<loc>')) {
                continue;
            }
            preg_match_all('~<loc>\s*(.*?)\s*</loc>~s', $res->body(), $m);
            foreach ($m[1] as $loc) {
                $loc = html_entity_decode($loc);
                str_ends_with(parse_url($loc, PHP_URL_PATH) ?? '', '.xml') ? $queue[] = $loc : $urls[] = $loc;
            }
        }

        return array_values(array_unique($urls));
    }

    private function jsonLd(Crawler $crawler): array
    {
        $nodes = [];
        $crawler->filter('script[type="application/ld+json"]')->each(function (Crawler $s) use (&$nodes) {
            $data = json_decode($s->text(''), true);
            if (! is_array($data)) {
                return;
            }
            foreach ($data['@graph'] ?? (isset($data[0]) ? $data : [$data]) as $node) {
                if (is_array($node)) {
                    $nodes[] = $node;
                }
            }
        });

        return $nodes;
    }

    private function meta(Crawler $crawler, string $property): ?string
    {
        $node = $crawler->filter("meta[property=\"{$property}\"], meta[name=\"{$property}\"]");

        return $node->count() ? trim($node->attr('content')) : null;
    }

    private function mainContent(Crawler $crawler): string
    {
        foreach (['.woocommerce-Tabs-panel--description', '.entry-content', 'article .content', 'main article', 'main'] as $selector) {
            $node = $crawler->filter($selector);
            if ($node->count()) {
                return $node->first()->html();
            }
        }

        return '';
    }

    private function absolute(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return str_starts_with($url, '//') ? 'https:'.$url : (str_starts_with($url, 'http') ? $url : $this->client->url($url));
    }

    private function text(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /** Cleans WordPress markup (shortcodes, builder wrappers) while keeping editorial HTML. */
    private function html(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('~\[/?[a-z_\-]+[^\]]*\]~i', '', $html);
        $html = preg_replace('~\s(class|style|id|data-[\w-]+)="[^"]*"~i', '', $html);
        $html = preg_replace('~<(div|span|section)>\s*</\1>~i', '', $html);

        return trim(clean_html($html));
    }

    private function code(string $text): string
    {
        $text = preg_replace('/^pa_/', '', rawurldecode($text));
        $ascii = Str::slug($text);

        return $ascii !== '' ? Str::limit($ascii, 60, '') : 'v'.substr(md5($text), 0, 10);
    }
}
