<?php

namespace App\Support;

/**
 * Normalises Persian/Arabic text so that searches match regardless of which
 * keyboard produced the input (ي/ی, ك/ک, ة/ه, diacritics, ZWNJ, digits).
 */
class TextNormalizer
{
    private const MAP = [
        'ي' => 'ی', 'ى' => 'ی', 'ئ' => 'ی',
        'ك' => 'ک',
        'ة' => 'ه', 'ۀ' => 'ه',
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
        'ؤ' => 'و',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        "\u{200C}" => ' ', "\u{200D}" => '', "\u{00A0}" => ' ', 'ـ' => '',
    ];

    public static function normalize(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strtr($text, self::MAP);
        // Arabic harakat and tanwin.
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** Converts Latin digits to the digits used by the given locale for display. */
    public static function localizeDigits(string|int|float|null $value, ?string $locale = null): string
    {
        $value = (string) $value;
        $locale ??= app()->getLocale();

        return match ($locale) {
            'fa' => strtr($value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']),
            'ar' => strtr($value, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']),
            default => $value,
        };
    }

    /** URL-safe slug that keeps Persian/Arabic letters. */
    public static function slug(string $text): string
    {
        $text = strtr($text, self::MAP);
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);

        return trim(mb_substr($text, 0, 150), '-');
    }
}
