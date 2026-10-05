{{-- "Inside Suru": the pack in the centre, its real ingredients orbiting it at admin-set positions. --}}
<section class="inside section" data-inside>
    <div class="container">
        @include('partials.section-head', ['eyebrow' => $content?->eyebrow ?: __('ui.inside.title'), 'title' => $content?->title, 'subtitle' => $content?->subtitle ?: __('ui.inside.intro'), 'num' => $num])
    </div>
    @if ($products->count() > 1)
        <div class="container inside__tabs" role="tablist">
            @foreach ($products as $p)
                <button type="button" role="tab" class="chip {{ $loop->first ? 'is-active' : '' }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        aria-controls="inside-{{ $p->id }}" data-inside-tab="{{ $p->id }}">{{ $p->tr('title') }}</button>
            @endforeach
        </div>
    @endif
    @foreach ($products as $p)
        <div class="inside__stage container" id="inside-{{ $p->id }}" role="tabpanel" style="--tint: {{ $p->themeColor() }}" @unless($loop->first) hidden @endunless data-inside-stage>
            <div class="inside__orbit">
                <a href="{{ $p->url() }}" class="inside__product" data-inside-product>
                    <x-picture :media="$p->displayImage()" size="medium" sizes="(max-width: 768px) 60vw, 28vw"/>
                </a>
                @foreach ($p->ingredients as $ing)
                    @php
                        $count = max(1, $p->ingredients->count());
                        $angle = (360 / $count) * $loop->index - 90;
                        $x = $ing->pivot->position_x ?? round(50 + 40 * cos(deg2rad($angle)), 2);
                        $y = $ing->pivot->position_y ?? round(50 + 40 * sin(deg2rad($angle)), 2);
                    @endphp
                    <button type="button" class="ingredient {{ $ing->pivot->is_highlight ? 'is-highlight' : '' }}" style="--x: {{ $x }}%; --y: {{ $y }}%; --c: {{ $ing->color ?? 'var(--c-accent)' }}"
                            aria-describedby="ing-{{ $p->id }}-{{ $ing->id }}" data-ingredient>
                        @if ($ing->media)
                            <span class="ingredient__img"><x-picture :media="$ing->media" size="thumbnail" sizes="96px" alt=""/></span>
                        @else
                            <span class="ingredient__dot"></span>
                        @endif
                        <span class="ingredient__name">{{ $ing->tr('name', null, true) }}</span>
                        <span class="ingredient__card" role="tooltip" id="ing-{{ $p->id }}-{{ $ing->id }}">
                            <strong>{{ $ing->tr('name', null, true) }}</strong>
                            @if ($ing->pivot->amount)<em>{{ $ing->pivot->amount }}</em>@endif
                            @if ($d = $ing->tr('description', null, true))<span>{{ $d }}</span>@endif
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach
</section>
