<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\SearchTerm;
use App\Support\TextNormalizer;
use Illuminate\Database\Eloquent\Builder;

/**
 * Site-wide search over the normalised `search_text` column of every
 * translatable entity, so ی/ي, ک/ك, diacritics and digit styles all match.
 */
class SearchService
{
    public function search(string $term, int $perGroup = 6): array
    {
        $normalized = TextNormalizer::normalize($term);
        $tokens = array_values(array_filter(explode(' ', $normalized), fn ($t) => mb_strlen($t) >= 1));
        if (! $tokens) {
            return ['products' => collect(), 'categories' => collect(), 'posts' => collect(), 'recipes' => collect(), 'total' => 0];
        }

        $match = function (Builder $q) use ($tokens) {
            $q->whereHas('translations', function ($t) use ($tokens) {
                $t->where('locale', app()->getLocale());
                foreach ($tokens as $token) {
                    $t->where('search_text', 'like', '%'.addcslashes($token, '%_\\').'%');
                }
            });
        };

        $products = Product::published()->forCard()->where($match)->orderByDesc('views_count')->limit($perGroup)->get();
        $categories = Category::published()->withTranslation()->with('image.translations')->where($match)->limit($perGroup)->get();
        $posts = Post::published()->withTranslation()->with('cover.translations')->where($match)->latest('published_at')->limit($perGroup)->get();
        $recipes = Recipe::published()->withTranslation()->with('cover.translations')->where($match)->latest('published_at')->limit($perGroup)->get();

        $total = $products->count() + $categories->count() + $posts->count() + $recipes->count();

        return compact('products', 'categories', 'posts', 'recipes', 'total');
    }

    public function record(string $term, int $results): void
    {
        $term = mb_substr(TextNormalizer::normalize($term), 0, 190);
        if (mb_strlen($term) < 2) {
            return;
        }
        $row = SearchTerm::firstOrCreate(['term' => $term, 'locale' => app()->getLocale()]);
        $row->increment('hits', 1, ['last_results_count' => $results]);
    }
}
