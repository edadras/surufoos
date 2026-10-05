<?php

namespace App\Http\Controllers\Site;

use App\DTOs\ProductFilterData;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CatalogService;
use App\Services\FavoriteService;
use App\Services\Seo;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private CatalogService $catalog, private Seo $seo) {}

    public function index(Request $request)
    {
        $filters = ProductFilterData::fromRequest($request);
        $products = $this->catalog->filter($filters);

        $this->seo->set(__('ui.products.title'), __('ui.products.intro'))->forRoute('products.index');
        // Filtered views are shareable but only the clean listing (and simple category filters) are indexable.
        $this->seo->alternatesFrom(fn ($l) => route('products.index', ['locale' => $l] + array_filter(['category' => $filters->categorySlug && $l === app()->getLocale() ? $filters->categorySlug : null])));
        $this->seo->noindex = $filters->query || $filters->priceMin !== null || $filters->priceMax !== null || $filters->attributes || $filters->inStockOnly;
        $this->seo->breadcrumb(__('ui.products.title'));

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('site.products._results', compact('products', 'filters'))->render(),
                'total' => $products->total(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('site.products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $this->catalog->categories(),
            'facets' => $this->catalog->facets(),
            'priceRange' => $this->catalog->priceRange(),
            'category' => null,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $product = Product::published()->whereSlug($slug)->firstOr(function () use ($slug) {
            // Slug may exist in another locale (e.g. user switched language manually).
            return Product::published()->whereHas('translations', fn ($q) => $q->where('slug', $slug))->first();
        });
        abort_unless($product, 404);

        $translation = $product->translation();
        abort_unless($translation, 404);
        if ($translation->slug !== $slug && $translation->locale === app()->getLocale()) {
            return redirect($product->url(), 301);
        }

        $product->load([
            'translations', 'mainImage.translations', 'packshot.translations', 'images.media.translations',
            'categories.translations', 'primaryCategory.translations', 'attributeValues.translations', 'attributeValues.attribute.translations',
            'ingredients.translations', 'ingredients.media', 'nutritionFacts', 'tags.translations',
            'variants.attributeValues.translations',
            'recipes' => fn ($q) => $q->published()->withTranslation()->with('cover.translations')->limit(3),
        ]);

        $request->attributes->set('viewable', $product);
        $this->seoForProduct($product);

        return view('site.products.show', [
            'product' => $product,
            'related' => $this->catalog->related($product),
            'complementary' => $this->catalog->complementary($product),
            'isFavorite' => in_array($product->id, app(FavoriteService::class)->ids(), true),
        ]);
    }

    public function quickView(Product $product)
    {
        abort_unless($product->status->value === 'published' && $product->translation(), 404);
        $product->load([
            'translations', 'mainImage.translations', 'packshot.translations', 'images.media.translations',
            'primaryCategory.translations', 'attributeValues.translations', 'attributeValues.attribute.translations',
            'ingredients.translations', 'nutritionFacts',
        ]);

        return view('site.products._quick-view', [
            'product' => $product,
            'related' => $this->catalog->related($product, 4),
            'isFavorite' => in_array($product->id, app(FavoriteService::class)->ids(), true),
        ]);
    }

    private function seoForProduct(Product $product): void
    {
        $t = $product->translation();
        $image = $product->displayImage();
        $this->seo->fromTranslation($t, $image, 'product')->alternatesFrom(fn ($l) => $product->translation($l, false) ? $product->url($l) : null);

        $this->seo->breadcrumb(__('ui.products.title'), lroute('products.index'));
        if ($category = $product->primaryCategory ?? $product->categories->first()) {
            $this->seo->breadcrumb((string) $category->tr('title'), $category->url());
        }
        $this->seo->breadcrumb((string) $t->title);

        $images = collect([$image])->concat($product->images->pluck('media'))->filter()->map(fn ($m) => $m->variantUrl('large'))->unique()->values()->all();
        $offer = null;
        if ($product->effectivePrice() !== null && settings()->showPrices()) {
            $offer = [
                '@type' => 'Offer',
                'price' => $product->effectivePrice() * (config('suru.currency') === 'IRT' ? 10 : 1),
                'priceCurrency' => 'IRR',
                'availability' => 'https://schema.org/'.match ($product->stock_status->value) {
                    'in_stock' => 'InStock', 'preorder' => 'PreOrder', 'discontinued' => 'Discontinued', default => 'OutOfStock',
                },
                'url' => $product->url(),
            ];
        }
        $this->seo->schema(array_filter([
            '@type' => 'Product',
            'name' => $t->title,
            'description' => strip_tags((string) ($t->short_description ?: $t->description)),
            'image' => $images ?: null,
            'sku' => $product->sku,
            'brand' => ['@type' => 'Brand', 'name' => settings()->siteName()],
            'category' => $product->primaryCategory?->tr('title'),
            'weight' => $product->net_weight ? ['@type' => 'QuantitativeValue', 'value' => $product->net_weight, 'unitCode' => match ($product->weight_unit) {
                'kg' => 'KGM', 'ml' => 'MLT', 'l' => 'LTR', default => 'GRM',
            }] : null,
            'offers' => $offer,
        ]));
    }
}
