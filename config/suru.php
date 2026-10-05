<?php

return [
    /*
    | Public site locales. Persian is the source language: imported content
    | lands in `fa` first and the other locales are filled through the admin
    | translation manager.
    */
    'locales' => [
        'fa' => ['name' => 'فارسی', 'native' => 'فارسی', 'dir' => 'rtl', 'hreflang' => 'fa', 'og' => 'fa_IR', 'short' => 'FA'],
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr', 'hreflang' => 'en', 'og' => 'en_US', 'short' => 'EN'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl', 'hreflang' => 'ar', 'og' => 'ar_AR', 'short' => 'AR'],
    ],

    'source_locale' => 'fa',

    /*
    | When a record has no translation in the requested locale, either hide it
    | (false) or show the Persian source text with noindex (true).
    */
    'content_fallback' => env('SURU_CONTENT_FALLBACK', false),

    'currency' => env('SURU_CURRENCY', 'IRT'),

    'images' => [
        'disk' => env('SURU_MEDIA_DISK', 'public'),
        'max_upload_kb' => 10240,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif', 'image/svg+xml'],
        'sizes' => [
            'large' => 1920,
            'medium' => 1024,
            'small' => 640,
            'thumbnail' => 320,
        ],
        'webp_quality' => 82,
        'avif_quality' => 60,
        'jpeg_quality' => 84,
    ],

    'import' => [
        'source' => env('SURU_IMPORT_SOURCE', 'https://surufood.ir'),
        'timeout' => 30,
        'user_agent' => 'SuruContentMigrator/1.0 (+https://surufood.ir)',
        'per_page' => 50,
    ],

    'cache_ttl' => 3600,
];
