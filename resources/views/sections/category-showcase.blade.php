{{-- Category "universe": each world is a tall tinted panel; hover expands it and lifts its image. --}}
<section class="universe section" data-universe>
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.categories.title'), 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num, 'link' => lroute('categories.index')])
    </div>
    <div class="universe__track" data-swipe>
        @foreach ($categories as $cat)
            @if ($url = $cat->url())
                <a href="{{ $url }}" class="world" style="--tint: {{ $cat->themeColor() ?? 'var(--c-surface-warm)' }}; --i: {{ $loop->index }}" data-world>
                    <span class="world__index">{{ digits(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)) }}</span>
                    @if ($cat->cover)
                        <span class="world__cover"><x-picture :media="$cat->cover" size="medium" sizes="(max-width: 768px) 80vw, 30vw" alt=""/></span>
                    @endif
                    @if ($cat->image)
                        <span class="world__img" data-world-img><x-picture :media="$cat->image" size="small" sizes="(max-width: 768px) 60vw, 20vw"/></span>
                    @endif
                    <span class="world__text">
                        <strong class="world__title">{{ $cat->tr('title') }}</strong>
                        @if ($d = $cat->tr('short_description'))<span class="world__desc">{{ \Illuminate\Support\Str::limit(strip_tags($d), 90) }}</span>@endif
                        <span class="world__cta">{{ __('ui.categories.explore') }} <x-icon name="arrow" size="16" class="flip-rtl"/></span>
                    </span>
                </a>
            @endif
        @endforeach
    </div>
</section>
