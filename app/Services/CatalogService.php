<?php

namespace App\Services;

use App\DTOs\ProductFilterData;
use App\Enums\StockStatus;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Support\CacheGroups;
use App\Support\TextNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogService
{
    public function baseQuery(): Builder
    {
        return Product::query()->published()->translatedIn();
    }

    public function filter(ProductFilterData $filters): LengthAwarePaginator
    {
        $query = $this->baseQuery()->forCard();

        if ($filters->categoryId) {
            $ids = $this->categoryWithDescendants($filters->categoryId);
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
        } elseif ($filters->categorySlug) {
            $category = Category::published()->whereSlug($filters->categorySlug)->first();
            $ids = $category ? $this->categoryWithDescendants($category->id) : [0];
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
        }

        if ($filters->query) {
            $this->applySearch($query, $filters->query);
        }
        if ($filters->priceMin !== null) {
            $query->where('price', '>=', $filters->priceMin);
        }
        if ($filters->priceMax !== null) {
            $query->where('price', '<=', $filters->priceMax);
        }
        if ($filters->inStockOnly) {
            $query->whereIn('stock_status', [StockStatus::InStock->value, StockStatus::Preorder->value]);
        }

        foreach ($filters->attributes as $code => $values) {
            // OR within one attribute, AND across attributes.
            $query->whereHas('attributeValues', fn ($q) => $q->whereIn('attribute_values.code', $values)
                ->whereHas('attribute', fn ($a) => $a->where('code', $code)));
        }

        match ($filters->sort) {
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            'popular' => $query->orderByDesc('views_count'),
            'price_asc' => $query->orderByRaw('price IS NULL')->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'title' => $query->orderBy(
                ProductTranslation::select('title')->whereColumn('product_id', 'products.id')->where('locale', app()->getLocale())->limit(1)
            ),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id'),
        };

        return $query->paginate($filters->perPage)->withQueryString();
    }

    public function applySearch(Builder $query, string $term): void
    {
        $tokens = array_filter(explode(' ', TextNormalizer::normalize($term)));
        $locale = app()->getLocale();
        $query->where(function ($outer) use ($tokens, $locale) {
            $outer->whereHas('translations', function ($q) use ($tokens, $locale) {
                $q->where('locale', $locale);
                foreach ($tokens as $token) {
                    $q->where('search_text', 'like', '%'.addcslashes($token, '%_\\').'%');
                }
            })->orWhere('sku', $tokens ? implode(' ', $tokens) : '');
        });
    }

    /** @return array<int, int> */
    public function categoryWithDescendants(int $id): array
    {
        $all = CacheGroups::remember(CacheGroups::CATEGORIES, 'parent-map', fn () => Category::pluck('parent_id', 'id')->all());
        $ids = [$id];
        $frontier = [$id];
        while ($frontier) {
            $next = array_keys(array_filter($all, fn ($parent) => in_array($parent, $frontier, true)));
            $next = array_diff($next, $ids);
            $ids = array_merge($ids, $next);
            $frontier = $next;
        }

        return $ids;
    }

    /** Top-level categories with translations and images, cached per locale. */
    public function categories(): Collection
    {
        return CacheGroups::remember(CacheGroups::CATEGORIES, 'tree:'.app()->getLocale(), fn () => Category::published()->translatedIn()->withTranslation()
            ->with(['image.translations', 'cover.translations', 'icon.translations', 'children' => fn ($q) => $q->published()->translatedIn()->withTranslation()])
            ->whereNull('parent_id')->ordered()->get());
    }

    /** Filterable attributes with the values actually used by published products. */
    public function facets(): Collection
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, 'facets:'.app()->getLocale(), fn () => Attribute::where('is_filterable', true)
            ->withTranslation()
            ->with(['values' => fn ($q) => $q->withTranslation()->whereHas('products', fn ($p) => $p->published())])
            ->orderBy('sort_order')->get()
            ->filter(fn ($a) => $a->values->isNotEmpty())->values());
    }

    public function priceRange(): array
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, 'price-range', function () {
            $row = Product::published()->whereNotNull('price')->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

            return ['min' => (int) $row?->min_price, 'max' => (int) $row?->max_price];
        });
    }

    public function featured(int $limit = 8): Collection
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, "featured:{$limit}:".app()->getLocale(), fn () => $this->baseQuery()->forCard()
            ->with('attributeValues.translations')
            ->where('is_featured', true)->orderBy('sort_order')->orderByDesc('id')->limit($limit)->get());
    }

    public function newest(int $limit = 10): Collection
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, "newest:{$limit}:".app()->getLocale(), function () use ($limit) {
            $flagged = $this->baseQuery()->forCard()->where('is_new', true)->orderByDesc('published_at')->limit($limit)->get();

            return $flagged->isNotEmpty() ? $flagged : $this->baseQuery()->forCard()->orderByDesc('published_at')->orderByDesc('id')->limit($limit)->get();
        });
    }

    public function popular(int $limit = 8): Collection
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, "popular:{$limit}:".app()->getLocale(), fn () => $this->baseQuery()->forCard()
            ->orderByDesc('views_count')->limit($limit)->get());
    }

    public function bySource(string $source, ?int $categoryId, int $limit): Collection
    {
        return match ($source) {
            'new' => $this->newest($limit),
            'popular' => $this->popular($limit),
            'category' => $categoryId ? CacheGroups::remember(CacheGroups::PRODUCTS, "cat:{$categoryId}:{$limit}:".app()->getLocale(), fn () => $this->baseQuery()->forCard()
                ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $this->categoryWithDescendants($categoryId)))
                ->orderBy('sort_order')->limit($limit)->get()) : collect(),
            default => $this->featured($limit),
        };
    }

    /** Products with ingredient compositions for the "Inside Suru" section. */
    public function insideProducts(int $limit = 3): Collection
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, "inside:{$limit}:".app()->getLocale(), fn () => $this->baseQuery()->forCard()
            ->where('show_inside', true)->whereHas('ingredients')
            ->with(['ingredients.translations', 'ingredients.media'])
            ->orderBy('sort_order')->limit($limit)->get());
    }

    public function related(Product $product, int $limit = 8): Collection
    {
        $categoryIds = $product->categories->pluck('id');
        $tagIds = $product->tags->pluck('id');

        return $this->baseQuery()->forCard()->whereKeyNot($product->id)
            ->where(fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $categoryIds))
                ->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds)))
            ->inRandomOrder()->limit($limit)->get();
    }

    /** Complementary products: those that appear in the same recipes, then other categories. */
    public function complementary(Product $product, int $limit = 6): Collection
    {
        $recipeIds = $product->recipes()->pluck('recipes.id');
        $fromRecipes = $recipeIds->isEmpty() ? collect() : $this->baseQuery()->forCard()->whereKeyNot($product->id)
            ->whereHas('recipes', fn ($q) => $q->whereIn('recipes.id', $recipeIds))->limit($limit)->get();

        if ($fromRecipes->count() >= $limit) {
            return $fromRecipes;
        }

        $others = $this->baseQuery()->forCard()->whereKeyNot($product->id)->whereNotIn('id', $fromRecipes->pluck('id'))
            ->whereDoesntHave('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->orderByDesc('is_featured')->orderByDesc('views_count')->limit($limit - $fromRecipes->count())->get();

        return $fromRecipes->concat($others);
    }
}
