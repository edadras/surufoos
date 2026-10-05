@extends('layouts.site')
@section('body_class', 'page-recipe')
@php
    $t = $recipe->translation();
    $ingredients = $recipe->ingredientsFor();
    $steps = $recipe->stepsFor();
    $embed = null;
    if ($recipe->video_url && preg_match('~aparat\.com/v/([A-Za-z0-9]+)~', $recipe->video_url, $m)) { $embed = 'https://www.aparat.com/video/video/embed/videohash/'.$m[1].'/vt/frame'; }
    elseif ($recipe->video_url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/))([\w-]{11})~', $recipe->video_url, $m)) { $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1'; }
@endphp
@section('content')
<article class="recipe">
    <header class="recipe__head">
        <div class="container recipe__head-grid">
            <div>
                @include('partials.breadcrumbs')
                <h1 class="display-1" data-split="lines">{{ $t->title }}</h1>
                @if ($t->short_description)<p class="lead">{{ strip_tags($t->short_description) }}</p>@endif
                <dl class="recipe__stats">
                    @if ($recipe->prep_minutes)<div><dt>{{ __('ui.recipes.prep') }}</dt><dd>{{ __('ui.minutes', ['count' => digits($recipe->prep_minutes)]) }}</dd></div>@endif
                    @if ($recipe->cook_minutes)<div><dt>{{ __('ui.recipes.cook') }}</dt><dd>{{ __('ui.minutes', ['count' => digits($recipe->cook_minutes)]) }}</dd></div>@endif
                    @if ($recipe->servings)<div><dt>{{ __('ui.recipes.servings') }}</dt><dd>{{ __('ui.recipes.servings_count', ['count' => digits($recipe->servings)]) }}</dd></div>@endif
                    @if ($recipe->difficulty)<div><dt>{{ __('ui.recipes.difficulty') }}</dt><dd>{{ __('ui.recipes.difficulties.'.$recipe->difficulty->value) }}</dd></div>@endif
                </dl>
            </div>
            @if ($recipe->cover)
                <figure class="recipe__cover" data-reveal-mask><x-picture :media="$recipe->cover" size="large" sizes="(max-width: 900px) 100vw, 50vw" :eager="true"/></figure>
            @endif
        </div>
    </header>

    <div class="container recipe__body">
        @if ($ingredients->isNotEmpty())
            <aside class="recipe__ingredients">
                <h2 class="h2">{{ __('ui.recipes.ingredients') }}</h2>
                <ul class="checklist">
                    @foreach ($ingredients as $line)
                        <li>
                            <label><input type="checkbox"> <span>@if($line->quantity)<b>{{ $line->quantity }}</b> @endif
                                @if ($line->product && $line->product->url())<a href="{{ $line->product->url() }}">{{ $line->name }}</a>@else{{ $line->name }}@endif
                                @if($line->note)<small>{{ $line->note }}</small>@endif</span></label>
                        </li>
                    @endforeach
                </ul>
                @if (! empty($recipe->nutrition))
                    <h3 class="h3">{{ __('ui.recipes.nutrition') }}</h3>
                    <dl class="qv__nutrition">
                        @foreach (['calories', 'protein', 'carbs', 'fat'] as $k)
                            @if (! empty($recipe->nutrition[$k]))<div><dt>{{ __('ui.recipes.'.$k) }}</dt><dd>{{ digits($recipe->nutrition[$k]) }}</dd></div>@endif
                        @endforeach
                    </dl>
                @endif
            </aside>
        @endif

        <div class="recipe__steps">
            @if ($t->description)<div class="prose">{!! clean_html($t->description) !!}</div>@endif
            @if ($recipe->video_url)
                <h2 class="h2">{{ __('ui.recipes.video') }}</h2>
                <div class="video-frame" data-video data-embed="{{ $embed }}">
                    @if ($embed)
                        <button type="button" class="video-frame__play" data-video-play aria-label="{{ __('ui.recipes.video') }}">
                            @if($recipe->cover)<x-picture :media="$recipe->cover" size="medium" sizes="60vw" alt=""/>@endif
                            <span class="video-frame__btn"><x-icon name="play" size="28"/></span>
                        </button>
                    @else
                        <video controls preload="none" src="{{ $recipe->video_url }}"></video>
                    @endif
                </div>
            @endif
            @if ($steps->isNotEmpty())
                <h2 class="h2">{{ __('ui.recipes.steps') }}</h2>
                <ol class="steps">
                    @foreach ($steps as $step)
                        <li class="step" data-reveal>
                            <span class="step__num">{{ digits($loop->iteration) }}</span>
                            <div class="step__body">
                                <div class="prose">{!! rich_text($step->body) !!}</div>
                                @if ($step->media)<figure class="step__img"><x-picture :media="$step->media" size="medium" sizes="(max-width: 768px) 90vw, 40vw"/></figure>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
            @if ($t->tips)
                <div class="chapter--note"><p class="eyebrow">{{ __('ui.recipes.tips') }}</p><div class="prose">{!! rich_text($t->tips) !!}</div></div>
            @endif
        </div>
    </div>

    @if ($recipe->products->isNotEmpty())
        <section class="section slider-section">
            <div class="container">@include('partials.section-head', ['eyebrow' => __('ui.recipes.products_used')])</div>
            <div class="h-scroll" data-swipe>@foreach ($recipe->products as $p)<div class="h-scroll__item">@include('partials.product-card', ['product' => $p])</div>@endforeach</div>
        </section>
    @endif

    @if ($recipe->gallery->isNotEmpty())
        <section class="section"><div class="container gallery__grid">
            @foreach ($recipe->gallery as $g)@if($g->media)<figure class="gallery__item"><x-picture :media="$g->media" size="medium" sizes="33vw"/></figure>@endif @endforeach
        </div></section>
    @endif
</article>
@if ($related->isNotEmpty())
    <section class="section"><div class="container">
        @include('partials.section-head', ['eyebrow' => __('ui.recipes.related')])
        <div class="rgrid">@foreach ($related as $r) @include('partials.recipe-card', ['recipe' => $r]) @endforeach</div>
    </div></section>
@endif
@endsection
