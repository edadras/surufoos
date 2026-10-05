<?php

namespace App\Support;

class Locales
{
    public static function all(): array
    {
        return config('suru.locales');
    }

    public static function codes(): array
    {
        return array_keys(config('suru.locales'));
    }

    public static function source(): string
    {
        return config('suru.source_locale', 'fa');
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, config('suru.locales'));
    }

    public static function dir(?string $locale = null): string
    {
        return config('suru.locales.'.($locale ?? app()->getLocale()).'.dir', 'rtl');
    }

    public static function isRtl(?string $locale = null): bool
    {
        return self::dir($locale) === 'rtl';
    }

    public static function routePattern(): string
    {
        return implode('|', self::codes());
    }
}
