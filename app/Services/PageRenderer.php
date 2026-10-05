<?php

namespace App\Services;

use App\Enums\SectionType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\Media;
use App\Models\PageSection;
use App\Models\Post;
use App\Models\Recipe;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Models\TimelineEntry;
use App\Support\CacheGroups;
use Illuminate\Support\Collection;

/**
 * Resolves the data each page-builder section needs. A section whose data
 * source is empty (no products yet, no translation, no banners live) is
 * dropped instead of rendering placeholder content.
 */
class PageRenderer
{
    public function __construct(private CatalogService $catalog, private TasteFinderService $taste) {}

    /** @return Collection<int, array{section: PageSection, view: string, data: array}> */
    public function prepare(Collection $sections): Collection
    {
        return $sections->map(function (PageSection $section) {
            $data = $this->resolve($section);

            return $data === null ? null : [
                'section' => $section,
                'content' => $section->translation(),
                'view' => 'sections.'.str_replace('_', '-', $section->type->value),
                'data' => $data,
            ];
        })->filter()->values();
    }

    private function resolve(PageSection $s): ?array
    {
        $locale = app()->getLocale();
        $limit = (int) $s->setting('limit', 8) ?: 8;
        $content = $s->translation();

        $data = match ($s->type) {
            SectionType::Hero => $this->hero($s),
            SectionType::Story => $content && filled($content->body) ? [
                'media' => $this->media($s->setting('media_id')),
                'secondary' => $this->media($s->setting('secondary_media_id')),
                'keywords' => collect($content->items ?? [])->pluck('text')->filter()->values(),
            ] : null,
            SectionType::ProductUniverse, SectionType::CategoryShowcase => $this->nonEmpty(['categories' => $this->catalog->categories()->take($limit)], 'categories'),
            SectionType::ProductShowcase, SectionType::ProductSlider, SectionType::ProductShelf => $this->nonEmpty([
                'products' => $this->catalog->bySource((string) $s->setting('source', $s->type === SectionType::ProductShelf ? 'new' : 'featured'), (int) $s->setting('category_id') ?: null, $limit),
            ], 'products'),
            SectionType::InsideSuru => $this->nonEmpty(['products' => $this->catalog->insideProducts((int) $s->setting('limit', 3) ?: 3)], 'products'),
            SectionType::Journey, SectionType::Timeline => $this->nonEmpty(['entries' => CacheGroups::remember(CacheGroups::CONTENT, "timeline:{$s->setting('group', $s->type === SectionType::Journey ? 'journey' : 'history')}:{$locale}",
                fn () => TimelineEntry::where('group', $s->setting('group', $s->type === SectionType::Journey ? 'journey' : 'history'))->where('is_active', true)
                    ->translatedIn()->withTranslation()->with('media.translations')->orderBy('sort_order')->get())], 'entries'),
            SectionType::Banners => $this->nonEmpty(['banners' => Banner::live()->where('placement', $s->setting('placement', 'home'))
                ->with(['desktopMedia.translations', 'mobileMedia.translations'])->get()], 'banners'),
            SectionType::TasteFinder => $this->nonEmpty(['questions' => $this->taste->questions()], 'questions'),
            SectionType::RichText => $content && filled($content->body) ? [] : null,
            SectionType::ImageText => $content && (filled($content->body) || filled($content->title)) ? ['media' => $this->media($s->setting('media_id'))] : null,
            SectionType::Gallery => $this->nonEmpty(['media' => $this->mediaList($s->setting('media_ids', []))], 'media'),
            SectionType::Video => filled($s->setting('video_url')) ? ['poster' => $this->media($s->setting('poster_media_id'))] : null,
            SectionType::Faq => $this->nonEmpty(['faqs' => Faq::where('is_active', true)->where('group', $s->setting('group', 'general'))->translatedIn()->withTranslation()->orderBy('sort_order')->get()], 'faqs'),
            SectionType::Testimonials => $this->nonEmpty(['testimonials' => Testimonial::where('is_active', true)->translatedIn()->withTranslation()->with('avatar')->orderBy('sort_order')->limit($limit)->get()], 'testimonials'),
            SectionType::Recipes => $this->nonEmpty(['recipes' => Recipe::published()->translatedIn()->withTranslation()->with('cover.translations')->orderByDesc('is_featured')->latest('published_at')->limit($limit)->get()], 'recipes'),
            SectionType::Articles => $this->nonEmpty(['posts' => Post::published()->translatedIn()->withTranslation()->with(['cover.translations', 'category.translations'])->latest('published_at')->limit($limit)->get()], 'posts'),
            SectionType::Cta => $content && filled($content->title) ? ['media' => $this->mediaList($s->setting('media_ids', []))] : null,
            SectionType::Certificates => $this->nonEmpty(['certificates' => Certificate::where('is_active', true)->translatedIn()->withTranslation()->with('media.translations')->orderBy('sort_order')->get()], 'certificates'),
        };

        return $data;
    }

    private function hero(PageSection $s): ?array
    {
        $slides = Slider::where('placement', $s->setting('slider_placement', 'home_hero'))->where('is_active', true)
            ->with(['translations', 'media.translations', 'mobileMedia.translations', 'product' => fn ($q) => $q->forCard()])
            ->orderBy('sort_order')->get()
            ->filter(fn ($slide) => $slide->translation() && ($slide->media || $slide->product));

        if ($slides->isEmpty()) {
            return null;
        }

        return ['slides' => $slides->values(), 'layers' => $slides->first()->layerMedia()];
    }

    private function media(mixed $id): ?Media
    {
        return $id ? Media::with('translations')->find((int) $id) : null;
    }

    private function mediaList(mixed $ids): Collection
    {
        $ids = array_filter(array_map('intval', (array) $ids));
        if (! $ids) {
            return collect();
        }
        $media = Media::with('translations')->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(fn ($id) => $media->get($id))->filter()->values();
    }

    private function nonEmpty(array $data, string $key): ?array
    {
        return $data[$key]->isEmpty() ? null : $data;
    }
}
