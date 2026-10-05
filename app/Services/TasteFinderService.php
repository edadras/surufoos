<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Product;
use App\Support\CacheGroups;
use Illuminate\Support\Collection;

/**
 * "What do you feel like?" — rule-based product matching on quiz attributes
 * (attributes flagged is_quiz, e.g. taste, occasion). Products are ranked by
 * how many of the chosen values they carry; no external AI is involved.
 */
class TasteFinderService
{
    public function questions(): Collection
    {
        return CacheGroups::remember(CacheGroups::PRODUCTS, 'quiz:'.app()->getLocale(), fn () => Attribute::where('is_quiz', true)
            ->withTranslation()
            ->with(['values' => fn ($q) => $q->withTranslation()->whereHas('products', fn ($p) => $p->published())])
            ->orderBy('sort_order')->get()
            ->filter(fn ($a) => $a->values->isNotEmpty())->values());
    }

    /** @param array<int, int> $valueIds */
    public function recommend(array $valueIds, int $limit = 6): Collection
    {
        $valueIds = array_values(array_unique(array_map('intval', $valueIds)));
        if (! $valueIds) {
            return collect();
        }

        return Product::published()->translatedIn()->forCard()
            ->whereHas('attributeValues', fn ($q) => $q->whereIn('attribute_values.id', $valueIds))
            ->withCount(['attributeValues as match_score' => fn ($q) => $q->whereIn('attribute_values.id', $valueIds)])
            ->orderByDesc('match_score')->orderByDesc('is_featured')->orderByDesc('views_count')
            ->limit($limit)->get();
    }
}
