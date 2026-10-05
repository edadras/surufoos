<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Media;
use App\Support\CacheGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::with(['translations', 'image'])->withCount('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['status' => ContentStatus::Published, 'show_in_menu' => true]), 'parents' => $this->parents()]);
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->save(new Category, $request);

        return redirect()->route('admin.categories.edit', $category)->with('success', 'دسته‌بندی ذخیره شد.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', ['category' => $category->load('translations'), 'parents' => $this->parents($category->id)]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->save($category, $request);

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'دسته‌بندی حذف شد.');
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        DB::transaction(fn () => collect($ids)->each(fn ($id, $i) => Category::whereKey($id)->update(['sort_order' => $i])));
        CacheGroups::flush(CacheGroups::CATEGORIES, CacheGroups::MENUS, CacheGroups::HOME);

        return response()->json(['ok' => true]);
    }

    private function save(Category $category, CategoryRequest $request): Category
    {
        return DB::transaction(function () use ($category, $request) {
            $data = $request->safe()->except('translations');
            $category->fill($data);
            $category->sort_order = (int) ($data['sort_order'] ?? 0);
            if (! $category->theme_color && $category->image_media_id) {
                $category->theme_color = Media::find($category->image_media_id)?->dominant_color;
            }
            $category->save();
            $category->saveTranslations($request->translations());

            return $category;
        });
    }

    private function parents(?int $except = null)
    {
        return Category::with('translations')->whereNull('parent_id')->when($except, fn ($q) => $q->whereKeyNot($except))->orderBy('sort_order')->get();
    }
}
