<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache groups. Flushing a group bumps its version so every key in
 * it is orphaned at once; works on any cache store (Redis, database, file).
 */
class CacheGroups
{
    public const SETTINGS = 'settings';

    public const MENUS = 'menus';

    public const HOME = 'home';

    public const CATEGORIES = 'categories';

    public const PRODUCTS = 'products';

    public const CONTENT = 'content';

    public const TRANSLATIONS = 'translations';

    public static function remember(string $group, string $key, Closure $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(self::key($group, $key), $ttl ?? config('suru.cache_ttl'), $callback);
    }

    public static function key(string $group, string $key): string
    {
        return "suru:{$group}:v".self::version($group).":{$key}";
    }

    public static function version(string $group): int
    {
        return (int) Cache::rememberForever("suru:{$group}:version", fn () => 1);
    }

    public static function flush(string ...$groups): void
    {
        foreach ($groups as $group) {
            Cache::forever("suru:{$group}:version", self::version($group) + 1);
        }
    }
}
