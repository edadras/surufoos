<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    public function index()
    {
        return view('admin.post-categories.index', ['categories' => PostCategory::with('translations')->withCount('posts')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.post-categories.form', ['category' => new PostCategory]);
    }

    public function store(Request $request)
    {
        $category = PostCategory::create(['sort_order' => (int) $request->input('sort_order')]);
        $category->saveTranslations($this->validated($request));

        return redirect()->route('admin.post-categories.index')->with('success', 'دسته مجله ذخیره شد.');
    }

    public function edit(PostCategory $postCategory)
    {
        return view('admin.post-categories.form', ['category' => $postCategory->load('translations')]);
    }

    public function update(Request $request, PostCategory $postCategory)
    {
        $postCategory->update(['sort_order' => (int) $request->input('sort_order')]);
        $postCategory->saveTranslations($this->validated($request));

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(PostCategory $postCategory)
    {
        $postCategory->delete();

        return redirect()->route('admin.post-categories.index')->with('success', 'حذف شد.');
    }

    private function validated(Request $request): array
    {
        $rules = (new class extends \App\Http\Requests\Admin\TranslatableRequest {
            protected function translationRules(): array
            {
                return ['title' => ['string', 'max:191'], 'slug' => [], 'short_description' => ['string', 'max:1000'], 'seo_title' => ['string', 'max:191'], 'seo_description' => ['string', 'max:500']];
            }

            public function rules(): array
            {
                return $this->translationValidation();
            }
        })->rules();

        return $request->validate($rules + ['sort_order' => ['nullable', 'integer']])['translations'];
    }
}
