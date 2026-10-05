<?php

namespace App\Http\Controllers\Site;

use App\DTOs\ProductFilterData;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CatalogService;
use App\Services\Seo;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CatalogService $catalog, private Seo $seo) {}

    public function index()
    {
        $this->seo->set(__('ui.categories.title'), __('ui.categories.intro'))->forRoute('categories.index')->routeAlternates('categories.index');
        $this->seo->breadcrumb(__('ui.categories.title'));

        return view('site.categories.index', ['categories' => $this->catalog->categories()]);
    }

    public function show(Request $request, string $slug)
    {
        $category = Category::published()->whereSlug($slug)->with(['translations', 'cover.translations', 'image.translations', 'parent.translations',
            'children' => fn ($q) => $q->published()->translatedIn()->withTranslation()->with('image.translations')])->first();
        abort_unless($category, 404);
        $request->attributes->set('viewable', $category);

        $filters = ProductFilterData::fromRequest($request, $category->id);
        $products = $this->catalog->filter($filters);

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('site.products._results', compact('products', 'filters'))->render(),
                'total' => $products->total(),
                'url' => $request->fullUrl(),
            ]);
        }

        $this->seo->fromTranslation($category->translation(), $category->cover ?? $category->image)
            ->alternatesFrom(fn ($l) => $category->url($l));
        $this->seo->noindex = $this->seo->noindex || $filters->isFiltered();
        $this->seo->breadcrumb(__('ui.products.title'), lroute('products.index'));
        if ($category->parent) {
            $this->seo->breadcrumb((string) $category->parent->tr('title'), $category->parent->url());
        }
        $this->seo->breadcrumb((string) $category->tr('title'));
        $this->seo->schema(['@type' => 'CollectionPage', 'name' => $category->tr('title'), 'url' => $category->url()]);

        return view('site.products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $this->catalog->categories(),
            'facets' => $this->catalog->facets(),
            'priceRange' => $this->catalog->priceRange(),
            'category' => $category,
        ]);
    }
}
