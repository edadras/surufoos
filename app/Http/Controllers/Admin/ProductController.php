<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveProduct;
use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Services\ContentHealthService;
use App\Support\TextNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request, ContentHealthService $health)
    {
        $query = $request->query('issue') === 'incomplete' ? $health->incompleteProductsQuery() : Product::query();

        $products = $query->with(['translations', 'mainImage', 'packshot', 'primaryCategory.translations'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = TextNormalizer::normalize($request->query('q'));
                $q->where(fn ($w) => $w->where('sku', 'like', "%{$request->query('q')}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('search_text', 'like', "%{$term}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('stock'), fn ($q) => $q->where('stock_status', $request->query('stock')))
            ->when($request->integer('category'), fn ($q, $id) => $q->whereHas('categories', fn ($c) => $c->whereKey($id)))
            ->when($request->filled('translation'), fn ($q) => $q->whereDoesntHave('translations', fn ($t) => $t->where('locale', $request->query('translation'))))
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', $this->formData(new Product(['status' => ContentStatus::Draft, 'stock_status' => 'in_stock'])));
    }

    public function store(ProductRequest $request, SaveProduct $save)
    {
        $product = $save->execute(new Product, $request->validated());

        return redirect()->route('admin.products.edit', $product)->with('success', 'محصول ذخیره شد.');
    }

    public function edit(Product $product)
    {
        $product->load(['translations', 'images', 'categories', 'attributeValues', 'ingredients', 'nutritionFacts', 'variants.attributeValues', 'tags.translations']);

        return view('admin.products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product, SaveProduct $save)
    {
        $save->execute($product, $request->validated());

        return back()->with('success', 'تغییرات محصول ذخیره شد.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'محصول حذف شد.');
    }

    public function duplicate(Product $product)
    {
        $copy = DB::transaction(function () use ($product) {
            $copy = $product->replicate(['sku', 'views_count']);
            $copy->status = ContentStatus::Draft;
            $copy->published_at = null;
            $copy->save();
            foreach ($product->translations as $t) {
                $data = $t->only(['title', 'short_description', 'description', 'story', 'features', 'how_to_use', 'storage', 'serving_suggestion', 'badge_label', 'seo_title', 'seo_description']);
                $data['title'] .= ' (کپی)';
                $copy->saveTranslations([$t->locale => $data]);
            }
            $copy->categories()->sync($product->categories->pluck('id'));
            $copy->attributeValues()->sync($product->attributeValues->pluck('id'));
            foreach ($product->images as $img) {
                $copy->images()->create($img->only(['media_id', 'type', 'sort_order']));
            }

            return $copy;
        });

        return redirect()->route('admin.products.edit', $copy)->with('success', 'نسخه پیش‌نویس از محصول ساخته شد.');
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
            'attributes' => Attribute::with(['translations', 'values.translations'])->orderBy('sort_order')->get(),
            'ingredients' => Ingredient::with('translations')->get()->sortBy(fn ($i) => $i->tr('name', 'fa', true)),
        ];
    }
}
