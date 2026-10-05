{{-- Product journey: an SVG path draws itself with scroll and lights each real step as it is reached. --}}
<section class="journey section" data-journey>
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.journey.title'), 'title' => $content?->title, 'subtitle' => $content?->subtitle, 'num' => $num])
        <div class="journey__track">
            <svg class="journey__line" viewBox="0 0 1000 120" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 60 C 120 0, 220 120, 333 60 S 546 0, 666 60 S 880 120, 1000 60" fill="none" stroke="currentColor" stroke-width="2" data-journey-path/>
            </svg>
            <ol class="journey__steps" style="--n: {{ $entries->count() }}">
                @foreach ($entries as $entry)
                    <li class="journey__step" data-journey-step>
                        <span class="journey__num">{{ digits(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)) }}</span>
                        @if ($entry->media)
                            <span class="journey__img"><x-picture :media="$entry->media" size="thumbnail" sizes="120px" alt=""/></span>
                        @endif
                        <h3>{{ $entry->tr('title') }}</h3>
                        @if ($b = $entry->tr('body'))<p>{{ $b }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
