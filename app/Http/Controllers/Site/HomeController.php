<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageRenderer;
use App\Services\Seo;
use App\Support\CacheGroups;

class HomeController extends Controller
{
    public function __invoke(Seo $seo, PageRenderer $renderer)
    {
        $page = CacheGroups::remember(CacheGroups::HOME, 'page', fn () => Page::where('key', 'home')
            ->with(['translations', 'sections' => fn ($q) => $q->where('is_active', true)->with('translations')])->first());

        $translation = $page?->translation();
        $seo->fromTranslation($translation)->forRoute('home')->routeAlternates('home');
        $seo->schema(['@type' => 'WebSite', 'name' => settings()->siteName(), 'url' => lroute('home'),
            'potentialAction' => ['@type' => 'SearchAction', 'target' => lroute('search').'?q={q}', 'query-input' => 'required name=q']]);

        return view('site.home', [
            'sections' => $page ? $renderer->prepare($page->sections) : collect(),
        ]);
    }
}
