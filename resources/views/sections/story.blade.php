<section class="story section" data-story>
    <div class="container story__grid">
        <div class="story__visual">
            @if ($media)
                <div class="story__mask" data-reveal-mask><x-picture :media="$media" size="large" sizes="(max-width: 900px) 100vw, 50vw"/></div>
            @endif
            @if ($secondary)
                <div class="story__inset" data-parallax="-0.15"><x-picture :media="$secondary" size="small" sizes="260px"/></div>
            @endif
        </div>
        <div class="story__copy">
            @include('partials.section-head', ['eyebrow' => $content->eyebrow, 'title' => $content->title, 'num' => $num])
            <div class="prose" data-reveal>{!! rich_text($content->body) !!}</div>
            @if ($content->cta_label)
                <a href="{{ $content->cta_url ?: lroute('about') }}" class="link-arrow">{{ $content->cta_label }} <x-icon name="arrow" size="16" class="flip-rtl"/></a>
            @endif
        </div>
    </div>
    @if ($keywords->isNotEmpty())
        <div class="story__words" aria-hidden="true" data-story-words>
            @foreach ($keywords as $word)
                <span style="--i: {{ $loop->index }}">{{ $word }}</span>
            @endforeach
        </div>
    @endif
</section>
