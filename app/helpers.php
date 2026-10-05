<?php

use App\Services\SettingsService;
use App\Support\TextNormalizer;

if (! function_exists('settings')) {
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(SettingsService::class);

        return $key === null ? $service : $service->get($key, $default);
    }
}

if (! function_exists('digits')) {
    /** Localised digits for display (۱۲۳ in Persian, ١٢٣ in Arabic). */
    function digits(string|int|float|null $value, ?string $locale = null): string
    {
        return TextNormalizer::localizeDigits($value, $locale);
    }
}

if (! function_exists('money')) {
    function money(?int $amount, ?string $locale = null): ?string
    {
        if ($amount === null) {
            return null;
        }
        $locale ??= app()->getLocale();

        return digits(number_format($amount), $locale).' '.__('ui.currency', [], $locale);
    }
}

if (! function_exists('jdate')) {
    /** Locale-aware date: Jalali for Persian, Hijri-free Gregorian otherwise. */
    function jdate(?\DateTimeInterface $date, string $pattern = 'd MMMM y', ?string $locale = null): ?string
    {
        if (! $date) {
            return null;
        }
        $locale ??= app()->getLocale();
        $icuLocale = match ($locale) {
            'fa' => 'fa_IR@calendar=persian',
            'ar' => 'ar',
            default => 'en_US',
        };
        $formatter = new IntlDateFormatter($icuLocale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, config('app.timezone'),
            $locale === 'fa' ? IntlDateFormatter::TRADITIONAL : IntlDateFormatter::GREGORIAN, $pattern);

        return $formatter->format($date) ?: null;
    }
}

if (! function_exists('lroute')) {
    /** Route URL in the current (or given) locale. */
    function lroute(string $name, array $params = [], ?string $locale = null): string
    {
        return route($name, ['locale' => $locale ?? app()->getLocale()] + $params);
    }
}
