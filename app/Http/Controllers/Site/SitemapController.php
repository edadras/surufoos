<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Recipe;
use App\Support\CacheGroups;
use App\Support\Locales;
use Illuminate\Support\Facades\URL;

/**
 * Sitemap index + one sitemap per locale and content type. Each URL lists its
 * hreflang alternates so search engines connect the three language versions.
 */
class SitemapController extends Controller
{
    private const TYPES = ['pages', 'products', 'categories', 'posts', 'recipes'];

    public function robots()
    {
        $lines = ['User-agent: *', 'Disallow: /admin', 'Disallow: /*/search', 'Disallow: /*/cart', 'Disallow: /*/checkout', 'Disallow: /*/account', 'Disallow: /*/favorites'];
        if ($extra = settings('robots_extra')) {
            $lines[] = trim($extra);
        }
        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap.index');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function index()
    {
        $xml = CacheGroups::remember(CacheGroups::CONTENT, 'sitemap-index', function () {
            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach (Locales::codes() as $locale) {
                foreach (self::TYPES as $type) {
                    $out .= '<sitemap><loc>'.e(route('sitemap.show', ['locale' => $locale, 'type' => $type])).'</loc></sitemap>';
                }
            }

            return $out.'</sitemapindex>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function show(string $locale, string $type)
    {
        $xml = CacheGroups::remember(CacheGroups::CONTENT, "sitemap:{$locale}:{$type}", function () use ($locale, $type) {
            app()->setLocale($locale);
            URL::defaults(['locale' => $locale]);
            $entries = $this->entries($type, $locale);

            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
            foreach ($entries as $entry) {
                $out .= '<url><loc>'.e($entry['loc']).'</loc>';
                if (! empty($entry['lastmod'])) {
                    $out .= '<lastmod>'.$entry['lastmod'].'</lastmod>';
                }
                foreach ($entry['alternates'] as $l => $href) {
                    $out .= '<xhtml:link rel="alternate" hreflang="'.config("suru.locales.{$l}.hreflang").'" href="'.e($href).'"/>';
                }
                if (! empty($entry['image'])) {
                    $out .= '<image:image><image:loc>'.e($entry['image']).'</image:loc></image:image>';
                }
                $out .= '</url>';
            }

            return $out.'</urlset>';
        }, 6 * 3600);

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function entries(string $type, string $locale): array
    {
        $static = fn (string $route) => [
            'loc' => route($route, ['locale' => $locale]),
            'alternates' => collect(Locales::codes())->mapWithKeys(fn ($l) => [$l => route($route, ['locale' => $l])])->all(),
        ];
        $translated = function ($models, callable $url) use ($locale) {
            return $models->filter(fn ($m) => $m->translation($locale, false))->map(fn ($m) => [
                'loc' => $url($m, $locale),
                'lastmod' => $m->updated_at?->toAtomString(),
                'alternates' => collect(Locales::codes())->filter(fn ($l) => $m->translation($l, false))->mapWithKeys(fn ($l) => [$l => $url($m, $l)])->all(),
                'image' => method_exists($m, 'displayImage') ? $m->displayImage()?->variantUrl('large') : ($m->cover ?? null)?->variantUrl('large'),
            ])->values()->all();
        };

        return match ($type) {
            'pages' => array_merge(
                array_map($static, ['home', 'products.index', 'categories.index', 'about', 'contact', 'magazine.index', 'recipes.index']),
                $translated(Page::published()->whereNull('key')->with('translations')->get(), fn ($m, $l) => $m->url($l)),
            ),
            'products' => $translated(Product::published()->with(['translations', 'mainImage', 'packshot'])->get(), fn ($m, $l) => $m->url($l)),
            'categories' => $translated(Category::published()->with('translations')->get(), fn ($m, $l) => $m->url($l)),
            'posts' => $translated(Post::published()->with(['translations', 'cover'])->get(), fn ($m, $l) => $m->url($l)),
            'recipes' => $translated(Recipe::published()->with(['translations', 'cover'])->get(), fn ($m, $l) => $m->url($l)),
        };
    }
}
