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

        $separator = in_array($locale, ['fa', 'ar'], true) ? '٬' : ',';

        return digits(number_format($amount, 0, '.', $separator), $locale).' '.__('ui.currency', [], $locale);
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

if (! function_exists('clean_html')) {
    /**
     * Sanitises CMS/imported rich text: keeps editorial markup (headings, lists,
     * links, images, tables) and strips scripts, event handlers and styles.
     */
    function clean_html(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }
        static $sanitizer = null;
        $sanitizer ??= new Symfony\Component\HtmlSanitizer\HtmlSanitizer(
            (new Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowElement('iframe', ['src', 'width', 'height', 'allowfullscreen', 'title'])
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowMediaSchemes(['https', 'http'])
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->forceAttribute('a', 'rel', 'noopener')
                ->withMaxInputLength(500000)
        );

        return $sanitizer->sanitize($html);
    }
}

if (! function_exists('rich_text')) {
    /** Rich HTML is sanitised; plain text gets paragraphs and line breaks. */
    function rich_text(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }
        if ($text !== strip_tags($text)) {
            return clean_html($text);
        }

        return collect(preg_split("/\R{2,}/u", trim($text)))
            ->map(fn ($p) => '<p>'.nl2br(e(trim($p)), false).'</p>')->implode('');
    }
}
