<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentHealthService;
use App\Support\Locales;

/** Read-only overview of the site languages and how complete each one is. */
class LanguageController extends Controller
{
    public function index()
    {
        $coverage = [];
        foreach (Locales::codes() as $locale) {
            $done = $total = 0;
            foreach (ContentHealthService::TRANSLATABLE as [$class]) {
                $count = $class::count();
                $total += $count;
                $done += $count ? $class::whereHas('translations', fn ($t) => $t->where('locale', $locale))->count() : 0;
            }
            $coverage[$locale] = $total ? round($done / $total * 100) : 0;
        }

        return view('admin.languages.index', ['locales' => Locales::all(), 'coverage' => $coverage, 'fallback' => config('suru.content_fallback')]);
    }
}
