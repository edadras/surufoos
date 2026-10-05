<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\Seo;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function __construct(private Seo $seo) {}

    public function index(Request $request)
    {
        $recipes = Recipe::published()->translatedIn()->withTranslation()->with(['cover.translations', 'products' => fn ($q) => $q->published()->forCard()])
            ->when(in_array($request->query('difficulty'), ['easy', 'medium', 'hard'], true), fn ($q) => $q->where('difficulty', $request->query('difficulty')))
            ->when($request->integer('product'), fn ($q, $id) => $q->whereHas('products', fn ($p) => $p->whereKey($id)))
            ->orderByDesc('is_featured')->orderByDesc('published_at')->paginate(12)->withQueryString();

        $this->seo->set(__('ui.recipes.title'), __('ui.recipes.intro'))->forRoute('recipes.index')->routeAlternates('recipes.index');
        $this->seo->noindex = $request->hasAny(['difficulty', 'product']);
        $this->seo->breadcrumb(__('ui.recipes.title'));

        return view('site.recipes.index', compact('recipes'));
    }

    public function show(Request $request, string $slug)
    {
        $recipe = Recipe::published()->whereSlug($slug)->with([
            'translations', 'cover.translations', 'ingredientLines.product', 'steps.media.translations', 'gallery.media.translations',
            'products' => fn ($q) => $q->published()->forCard(), 'tags.translations',
        ])->first();
        abort_unless($recipe && $recipe->translation(), 404);
        $request->attributes->set('viewable', $recipe);

        $t = $recipe->translation();
        $this->seo->fromTranslation($t, $recipe->cover, 'article')->alternatesFrom(fn ($l) => $recipe->translation($l, false) ? $recipe->url($l) : null);
        $this->seo->breadcrumb(__('ui.recipes.title'), lroute('recipes.index'))->breadcrumb((string) $t->title);

        $iso = fn (?int $m) => $m ? 'PT'.$m.'M' : null;
        $this->seo->schema(array_filter([
            '@type' => 'Recipe',
            'name' => $t->title,
            'description' => strip_tags((string) $t->short_description),
            'image' => $recipe->cover?->variantUrl('large'),
            'author' => ['@type' => 'Organization', 'name' => settings()->siteName()],
            'datePublished' => $recipe->published_at?->toDateString(),
            'prepTime' => $iso($recipe->prep_minutes),
            'cookTime' => $iso($recipe->cook_minutes),
            'totalTime' => $iso($recipe->totalMinutes()),
            'recipeYield' => $recipe->servings ? (string) $recipe->servings : null,
            'recipeIngredient' => $recipe->ingredientsFor()->map(fn ($i) => trim($i->quantity.' '.$i->name))->values()->all() ?: null,
            'recipeInstructions' => $recipe->stepsFor()->map(fn ($s) => ['@type' => 'HowToStep', 'text' => strip_tags($s->body)])->values()->all() ?: null,
            'nutrition' => ! empty($recipe->nutrition['calories']) ? ['@type' => 'NutritionInformation', 'calories' => $recipe->nutrition['calories'].' kcal'] : null,
            'video' => $recipe->video_url ? ['@type' => 'VideoObject', 'name' => $t->title, 'contentUrl' => $recipe->video_url, 'thumbnailUrl' => $recipe->cover?->variantUrl('medium'), 'uploadDate' => $recipe->published_at?->toAtomString()] : null,
        ]));

        $related = Recipe::published()->translatedIn()->withTranslation()->with('cover.translations')->whereKeyNot($recipe->id)
            ->whereHas('products', fn ($q) => $q->whereIn('products.id', $recipe->products->pluck('id')))->limit(3)->get();

        return view('site.recipes.show', compact('recipe', 'related'));
    }
}
