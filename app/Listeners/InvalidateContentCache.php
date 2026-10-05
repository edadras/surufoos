<?php

namespace App\Listeners;

use App\Events\ContentChanged;
use App\Models;
use App\Support\CacheGroups;

/** Maps each changed model to the cache groups that may contain it. */
class InvalidateContentCache
{
    public function handle(ContentChanged $event): void
    {
        $groups = match (true) {
            $event->model instanceof Models\Setting => [CacheGroups::SETTINGS, CacheGroups::HOME],
            $event->model instanceof Models\Menu, $event->model instanceof Models\MenuItem, $event->model instanceof Models\MenuItemTranslation => [CacheGroups::MENUS],
            $event->model instanceof Models\Category, $event->model instanceof Models\CategoryTranslation => [CacheGroups::CATEGORIES, CacheGroups::MENUS, CacheGroups::HOME, CacheGroups::PRODUCTS],
            $event->model instanceof Models\Product, $event->model instanceof Models\ProductTranslation => [CacheGroups::PRODUCTS, CacheGroups::HOME, CacheGroups::MENUS],
            $event->model instanceof Models\Page, $event->model instanceof Models\PageSection, $event->model instanceof Models\PageSectionTranslation,
            $event->model instanceof Models\Slider, $event->model instanceof Models\Banner => [CacheGroups::HOME, CacheGroups::CONTENT],
            $event->model instanceof Models\Media, $event->model instanceof Models\MediaTranslation => [CacheGroups::HOME, CacheGroups::SETTINGS, CacheGroups::MENUS, CacheGroups::PRODUCTS],
            default => [CacheGroups::CONTENT, CacheGroups::HOME],
        };

        CacheGroups::flush(...$groups);
    }
}
