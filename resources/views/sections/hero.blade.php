{{-- Interactive product stage: real Suru packshot at the centre, ingredient layers at different depths. --}}
@php
    $slide = $slides->first();
    $st = $slide->translation();
    $hero = $slide->product?->displayImage() ?? $slide->media;
    $bg = $slide->product ? $slide->media : null;
    $tint = $slide->background_color ?: ($slide->product?->themeColor() ?? $hero?->dominant_color ?? 'var(--c-surface-warm)');
@endphp
@push('preload')
    @if ($hero && $hero->variants)
        <link rel="preload" as="image" href="{{ $hero->variantUrl('medium', 'webp') }}" imagesrcset="{{ $hero->srcset('webp') }}" imagesizes="(max-width: 768px) 80vw, 40vw" fetchpriority="high">
    @endif
@endpush
<section class="hero" style="--tint: {{ $tint }}" data-hero>
    @if ($bg)
        <div class="hero__bg" data-depth="0.05" aria-hidden="true"><x-picture :media="$bg" size="large" sizes="100vw" alt=""/></div>
    @endif
    <div class="hero__wash" aria-hidden="true"></div>

    <div class="container hero__grid">
        <div class="hero__copy" data-hero-copy>
            @if ($st->eyebrow)<p class="eyebrow eyebrow--line">{{ $st->eyebrow }}</p>@endif
            <h1 class="display-1 hero__title" data-split="lines">{{ $st->title }}</h1>
            @if ($st->subtitle)<p class="lead hero__lead">{{ $st->subtitle }}</p>@endif
            <div class="hero__ctas">
                <a href="{{ $st->cta_url ?: lroute('products.index') }}" class="btn" data-magnetic>{{ $st->cta_label ?: __('ui.hero.cta_products') }} <x-icon name="arrow" size="18" class="flip-rtl"/></a>
                <a href="{{ $st->cta2_url ?: lroute('about') }}" class="btn btn--ghost">{{ $st->cta2_label ?: __('ui.hero.cta_story') }}</a>
            </div>
        </div>

        <div class="hero__stage" data-hero-stage>
            <span class="hero__halo" aria-hidden="true"></span>
            @foreach ($layers->where('depth', '<', 0.5) as $layer)
                <span class="hero__layer" data-depth="{{ $layer['depth'] ?? 0.2 }}"
                      style="--x: {{ $layer['x'] ?? 50 }}%; --y: {{ $layer['y'] ?? 50 }}%; --s: {{ $layer['scale'] ?? 1 }}; --r: {{ $layer['rotate'] ?? 0 }}deg" aria-hidden="true">
                    <x-picture :media="$layer['media']" size="small" sizes="200px" alt=""/>
                </span>
            @endforeach
            @if ($hero)
                <div class="hero__product" data-depth="0.6" data-hero-product>
                    <x-picture :media="$hero" size="medium" sizes="(max-width: 768px) 80vw, 40vw" :eager="true"/>
                </div>
                <span class="hero__shadow" data-hero-shadow aria-hidden="true"></span>
            @endif
            @foreach ($layers->where('depth', '>=', 0.5) as $layer)
                <span class="hero__layer hero__layer--front" data-depth="{{ $layer['depth'] }}"
                      style="--x: {{ $layer['x'] ?? 50 }}%; --y: {{ $layer['y'] ?? 50 }}%; --s: {{ $layer['scale'] ?? 1 }}; --r: {{ $layer['rotate'] ?? 0 }}deg" aria-hidden="true">
                    <x-picture :media="$layer['media']" size="small" sizes="200px" alt=""/>
                </span>
            @endforeach
            @if ($slide->product)
                <a href="{{ $slide->product->url() }}" class="hero__tag">
                    <span>{{ $slide->product->tr('title') }}</span>
                    @if ($w = $slide->product->weightLabel())<small>{{ $w }}</small>@endif
                </a>
            @endif
        </div>
    </div>
    <p class="hero__scroll" aria-hidden="true"><span></span>{{ __('ui.scroll') }}</p>
</section>
