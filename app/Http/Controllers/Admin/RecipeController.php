<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecipeRequest;
use App\Models\Product;
use App\Models\Recipe;
use App\Support\Locales;
use App\Support\TextNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    use Concerns;

    public function index(Request $request)
    {
        $recipes = Recipe::with(['translations', 'cover'])->withCount('products')
            ->when($request->filled('q'), fn ($q) => $q->whereHas('translations', fn ($t) => $t->where('search_text', 'like', '%'.TextNormalizer::normalize($request->query('q')).'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->latest('id')->paginate(25)->withQueryString();

        return view('admin.recipes.index', compact('recipes'));
    }

    public function create()
    {
        return view('admin.recipes.form', ['recipe' => new Recipe(['status' => ContentStatus::Draft]), 'products' => $this->products()]);
    }

    public function store(RecipeRequest $request)
    {
        $recipe = $this->save(new Recipe, $request);

        return redirect()->route('admin.recipes.edit', $recipe)->with('success', 'دستور غذایی ذخیره شد.');
    }

    public function edit(Recipe $recipe)
    {
        return view('admin.recipes.form', ['recipe' => $recipe->load(['translations', 'ingredientLines', 'steps', 'products', 'gallery']), 'products' => $this->products()]);
    }

    public function update(RecipeRequest $request, Recipe $recipe)
    {
        $this->save($recipe, $request);

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'حذف شد.');
    }

    private function save(Recipe $recipe, RecipeRequest $request): Recipe
    {
        return DB::transaction(function () use ($recipe, $request) {
            $data = $request->validated();
            $recipe->fill(collect($data)->only(['cover_media_id', 'video_url', 'prep_minutes', 'cook_minutes', 'servings', 'difficulty', 'status', 'is_featured', 'published_at'])->all());
            $recipe->nutrition = array_filter($data['nutrition'] ?? [], 'filled') ?: null;
            $this->publishDate($recipe);
            $recipe->save();
            $recipe->saveTranslations($request->translations());

            $recipe->ingredientLines()->delete();
            $recipe->steps()->delete();
            foreach (Locales::codes() as $locale) {
                foreach (array_values($data['lines'][$locale] ?? []) as $i => $line) {
                    $recipe->ingredientLines()->create($line + ['locale' => $locale, 'sort_order' => $i]);
                }
                foreach (array_values($data['steps'][$locale] ?? []) as $i => $step) {
                    $recipe->steps()->create($step + ['locale' => $locale, 'sort_order' => $i]);
                }
            }
            // Products used in the recipe: explicit selection plus any product linked from an ingredient line.
            $linked = collect($data['lines'] ?? [])->flatten(1)->pluck('product_id')->filter();
            $recipe->products()->sync(collect($data['products'] ?? [])->merge($linked)->unique()->values());
            $recipe->gallery()->delete();
            foreach (array_values($data['gallery'] ?? []) as $i => $mid) {
                $recipe->gallery()->create(['media_id' => $mid, 'sort_order' => $i]);
            }

            return $recipe;
        });
    }

    private function products()
    {
        return Product::with('translations')->orderByDesc('id')->get();
    }
}
