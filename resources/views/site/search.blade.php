@extends('layouts.site')
@section('content')
<header class="page-hero">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-2">{{ $term ? __('ui.search.results_for', ['term' => $term]) : __('ui.search.title') }}</h1>
        <form action="{{ lroute('search') }}" method="get" class="inline-form inline-form--big" role="search">
            <label for="s-q" class="visually-hidden">{{ __('ui.search.placeholder') }}</label>
            <input id="s-q" type="search" name="q" value="{{ $term }}" placeholder="{{ __('ui.search.placeholder') }}" maxlength="100">
            <button class="btn" type="submit"><x-icon name="search" size="18"/> {{ __('ui.nav.search') }}</button>
        </form>
    </div>
</header>
@if ($results)
    <section class="section section--tight">
        <div class="container">
            @if ($results['total'] === 0)
                <div class="empty-block"><p>{{ __('ui.search.empty', ['term' => $term]) }}</p></div>
            @else
                @if ($results['products']->isNotEmpty())
                    <h2 class="eyebrow">{{ __('ui.search.products') }}</h2>
                    <div class="pgrid">@foreach ($results['products'] as $product) @include('partials.product-card', ['product' => $product]) @endforeach</div>
                @endif
                @if ($results['categories']->isNotEmpty())
                    <h2 class="eyebrow">{{ __('ui.search.categories') }}</h2>
                    <div class="chips-row">@foreach ($results['categories'] as $c)<a class="chip" href="{{ $c->url() }}">{{ $c->tr('title') }}</a>@endforeach</div>
                @endif
                @if ($results['recipes']->isNotEmpty())
                    <h2 class="eyebrow">{{ __('ui.search.recipes') }}</h2>
                    <div class="rgrid">@foreach ($results['recipes'] as $recipe) @include('partials.recipe-card', ['recipe' => $recipe]) @endforeach</div>
                @endif
                @if ($results['posts']->isNotEmpty())
                    <h2 class="eyebrow">{{ __('ui.search.posts') }}</h2>
                    <div class="agrid">@foreach ($results['posts'] as $post) @include('partials.post-card', ['post' => $post]) @endforeach</div>
                @endif
            @endif
        </div>
    </section>
@endif
@endsection
