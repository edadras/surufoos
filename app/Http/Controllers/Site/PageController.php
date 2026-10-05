<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\Page;
use App\Models\TimelineEntry;
use App\Services\PageRenderer;
use App\Services\Seo;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(private Seo $seo, private PageRenderer $renderer) {}

    public function about(Request $request)
    {
        $page = Page::published()->where('key', 'about')->with(['translations', 'cover.translations', 'sections' => fn ($q) => $q->where('is_active', true)->with('translations')])->first();
        $translation = $page?->translation();

        $this->seo->fromTranslation($translation, $page?->cover)->forRoute('about')->routeAlternates('about');
        $this->seo->set($this->seo->title ?: __('ui.nav.about'), $this->seo->description, $this->seo->image);
        $this->seo->breadcrumb(__('ui.nav.about'));
        $this->seo->schema(['@type' => 'AboutPage', 'name' => $this->seo->title, 'url' => lroute('about')]);

        return view('site.about', [
            'page' => $page,
            'translation' => $translation,
            'sections' => $page ? $this->renderer->prepare($page->sections) : collect(),
            'timeline' => TimelineEntry::where('group', 'history')->where('is_active', true)->translatedIn()->withTranslation()->with('media.translations')->orderBy('sort_order')->get(),
            'certificates' => Certificate::where('is_active', true)->translatedIn()->withTranslation()->with('media.translations')->orderBy('sort_order')->get(),
        ]);
    }

    public function faq()
    {
        $faqs = Faq::where('is_active', true)->translatedIn()->withTranslation()->orderBy('group')->orderBy('sort_order')->get();
        abort_if($faqs->isEmpty(), 404);

        $this->seo->set(__('ui.nav.faq'))->forRoute('faq')->routeAlternates('faq');
        $this->seo->breadcrumb(__('ui.nav.faq'));
        $this->seo->schema([
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($f) => [
                '@type' => 'Question', 'name' => $f->tr('question'),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $f->tr('answer'))],
            ])->values()->all(),
        ]);

        return view('site.faq', ['faqs' => $faqs->groupBy('group')]);
    }

    public function show(Request $request, string $slug)
    {
        $page = Page::published()->whereNull('key')->whereSlug($slug)
            ->with(['translations', 'cover.translations', 'sections' => fn ($q) => $q->where('is_active', true)->with('translations')])->first();
        abort_unless($page, 404);
        $request->attributes->set('viewable', $page);

        $this->seo->fromTranslation($page->translation(), $page->cover)->alternatesFrom(fn ($l) => $page->translation($l, false) ? $page->url($l) : null);
        $this->seo->breadcrumb((string) $page->tr('title'));

        return view('site.page', ['page' => $page, 'sections' => $this->renderer->prepare($page->sections)]);
    }
}
