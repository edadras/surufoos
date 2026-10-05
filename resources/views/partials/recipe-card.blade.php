<article class="rcard" data-rcard>
    <a href="{{ $recipe->url() }}" class="rcard__media">
        <x-picture :media="$recipe->cover" size="medium" sizes="(max-width: 768px) 90vw, 33vw"/>
        @if ($recipe->totalMinutes())
            <span class="rcard__time"><x-icon name="clock" size="14"/> {{ __('ui.minutes', ['count' => digits($recipe->totalMinutes())]) }}</span>
        @endif
    </a>
    <div class="rcard__body">
        @if ($recipe->difficulty)<p class="rcard__meta">{{ __('ui.recipes.difficulties.'.$recipe->difficulty->value) }}</p>@endif
        <h3><a href="{{ $recipe->url() }}">{{ $recipe->tr('title') }}</a></h3>
    </div>
</article>
