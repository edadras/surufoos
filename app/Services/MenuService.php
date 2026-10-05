<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Product;
use App\Support\CacheGroups;
use Illuminate\Support\Collection;

class MenuService
{
    public function __construct(private CatalogService $catalog, private SettingsService $settings) {}

    /** Active root items with children for a menu key, cached per locale. */
    public function items(string $key): Collection
    {
        return CacheGroups::remember(CacheGroups::MENUS, "{$key}:".app()->getLocale(), function () use ($key) {
            $menu = Menu::where('key', $key)->first();
            if (! $menu) {
                return collect();
            }

            return $menu->rootItems()->where('is_active', true)
                ->with(['translations', 'linkable.translations', 'children' => fn ($q) => $q->where('is_active', true)->with(['translations', 'linkable.translations'])])
                ->get()
                ->filter(fn ($item) => $item->label() && $item->href());
        });
    }

    /** Mega menu data: categories, featured products and the promo story block. */
    public function mega(): array
    {
        return CacheGroups::remember(CacheGroups::MENUS, 'mega:'.app()->getLocale(), function () {
            $ids = array_filter(array_map('intval', explode(',', (string) $this->settings->get('mega_featured_ids'))));
            $featured = $ids
                ? Product::published()->translatedIn()->forCard()->whereIn('id', $ids)->get()->sortBy(fn ($p) => array_search($p->id, $ids))->values()
                : $this->catalog->featured(3);

            return [
                'categories' => $this->catalog->categories(),
                'featured' => $featured->take(3),
                'promo' => array_filter([
                    'title' => $this->settings->get('mega_promo_title'),
                    'text' => $this->settings->get('mega_promo_text'),
                    'url' => $this->settings->get('mega_promo_url'),
                    'media' => $this->settings->media('mega_promo_media_id'),
                ]),
            ];
        });
    }
}
