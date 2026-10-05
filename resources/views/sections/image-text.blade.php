<section class="section image-text {{ $section->setting('reverse') ? 'image-text--reverse' : '' }}">
    <div class="container image-text__grid">
        @if ($media)
            <div class="image-text__media" data-reveal-mask><x-picture :media="$media" size="large" sizes="(max-width: 900px) 100vw, 50vw"/></div>
        @endif
        <div class="image-text__copy">
            @include('partials.section-head', ['eyebrow' => $content->eyebrow, 'title' => $content->title, 'subtitle' => $content->subtitle, 'num' => $num])
            @if ($content->body)<div class="prose">{!! rich_text($content->body) !!}</div>@endif
            @if ($content->cta_label && $content->cta_url)
                <a href="{{ $content->cta_url }}" class="btn">{{ $content->cta_label }}</a>
            @endif
        </div>
    </div>
</section>
