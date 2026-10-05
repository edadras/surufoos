<section class="timeline section" data-timeline>
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.about.history'), 'title' => $content?->title, 'num' => $num])
        <ol class="timeline__list">
            @foreach ($entries as $entry)
                <li class="timeline__item" data-reveal>
                    @if ($entry->label)<span class="timeline__label">{{ digits($entry->label) }}</span>@endif
                    <div class="timeline__body">
                        <h3>{{ $entry->tr('title') }}</h3>
                        @if ($b = $entry->tr('body'))<p>{{ $b }}</p>@endif
                    </div>
                    @if ($entry->media)
                        <div class="timeline__img" data-reveal-mask><x-picture :media="$entry->media" size="small" sizes="(max-width: 768px) 90vw, 30vw"/></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
