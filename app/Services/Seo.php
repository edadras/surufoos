<?php

namespace App\Services;

use App\Models\Media;
use App\Models\SeoMeta;
use App\Support\Locales;
use Illuminate\Support\Str;

/**
 * Request-scoped page metadata: title, description, canonical, hreflang
 * alternates, OpenGraph/Twitter and JSON-LD. Controllers fill it, the layout renders it.
 */
class Seo
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $canonical = null;

    public ?Media $image = null;

    public string $type = 'website';

    public bool $noindex = false;

    /** @var array<string, string> locale => absolute URL */
    public array $alternates = [];

    /** @var array<int, array> */
    public array $schemas = [];

    /** @var array<int, array{0: string, 1: ?string}> */
    public array $breadcrumbs = [];

    public function __construct(private SettingsService $settings) {}

    public function set(?string $title = null, ?string $description = null, ?Media $image = null, string $type = 'website'): static
    {
        $this->title = $title;
        $this->description = $description ? Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($description))), 160) : null;
        $this->image = $image;
        $this->type = $type;

        return $this;
    }

    /** Applies an admin-edited SeoMeta row for a static route, if any. */
    public function forRoute(string $routeKey): static
    {
        $meta = SeoMeta::with('ogMedia')->where('route_key', $routeKey)->where('locale', app()->getLocale())->first();
        if ($meta) {
            $this->title = $meta->title ?: $this->title;
            $this->description = $meta->description ?: $this->description;
            $this->image = $meta->ogMedia ?: $this->image;
            $this->noindex = $meta->noindex || $this->noindex;
            $this->canonical = $meta->canonical ?: $this->canonical;
        }

        return $this;
    }

    /** Uses a translation row's seo_title / seo_description when present. */
    public function fromTranslation($translation, ?Media $image = null, string $type = 'website'): static
    {
        return $this->set(
            $translation?->seo_title ?: $translation?->title,
            $translation?->seo_description ?: ($translation?->short_description ?: $translation?->description),
            $image,
            $type
        );
    }

    /** @param callable(string $locale): ?string $resolver */
    public function alternatesFrom(callable $resolver): static
    {
        $this->alternates = [];
        foreach (Locales::codes() as $locale) {
            if ($url = $resolver($locale)) {
                $this->alternates[$locale] = $url;
            }
        }
        // A page whose current locale is only shown through fallback content must not be indexed.
        if (! isset($this->alternates[app()->getLocale()])) {
            $this->noindex = true;
        }

        return $this;
    }

    public function routeAlternates(string $route, array $params = []): static
    {
        return $this->alternatesFrom(fn ($locale) => route($route, ['locale' => $locale] + $params));
    }

    public function schema(array $schema): static
    {
        $this->schemas[] = ['@context' => 'https://schema.org'] + $schema;

        return $this;
    }

    public function breadcrumb(string $label, ?string $url = null): static
    {
        $this->breadcrumbs[] = [$label, $url];

        return $this;
    }

    public function fullTitle(): string
    {
        $site = $this->settings->siteName();
        $suffix = $this->settings->get('title_suffix', $site);

        if (! $this->title || $this->title === $site) {
            return $this->settings->get('tagline') ? $site.' | '.$this->settings->get('tagline') : $site;
        }

        return $this->title.' | '.$suffix;
    }

    public function metaDescription(): ?string
    {
        return $this->description ?: $this->settings->get('default_description');
    }

    public function canonicalUrl(): string
    {
        return $this->canonical ?: ($this->alternates[app()->getLocale()] ?? url()->current());
    }

    public function imageUrl(): ?string
    {
        $media = $this->image ?? $this->settings->media('og_media_id') ?? $this->settings->media('logo_media_id');

        return $media ? $media->variantUrl('large') : null;
    }

    /** All JSON-LD blocks including the global Organization and BreadcrumbList. */
    public function allSchemas(): array
    {
        $schemas = $this->schemas;

        if ($this->breadcrumbs) {
            $items = [];
            $position = 1;
            $items[] = ['@type' => 'ListItem', 'position' => $position++, 'name' => __('ui.nav.home'), 'item' => route('home', ['locale' => app()->getLocale()])];
            foreach ($this->breadcrumbs as [$label, $url]) {
                $items[] = array_filter(['@type' => 'ListItem', 'position' => $position++, 'name' => $label, 'item' => $url]);
            }
            $schemas[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }

        return $schemas;
    }

    public function organizationSchema(): array
    {
        $logo = $this->settings->media('logo_media_id');

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->settings->siteName(),
            'legalName' => $this->settings->get('legal_name'),
            'url' => route('home', ['locale' => app()->getLocale()]),
            'logo' => $logo?->url(),
            'email' => $this->settings->get('email'),
            'telephone' => $this->settings->phones()[0] ?? null,
            'foundingDate' => $this->settings->get('founded_year'),
            'address' => $this->settings->get('address') ? ['@type' => 'PostalAddress', 'streetAddress' => $this->settings->get('address')] : null,
            'sameAs' => array_values($this->settings->socials()) ?: null,
        ]);
    }
}
