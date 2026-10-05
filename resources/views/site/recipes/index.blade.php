@extends('layouts.site')
@section('content')
<header class="page-hero">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-1" data-split="lines">{{ __('ui.recipes.title') }}</h1>
        <p class="lead">{{ __('ui.recipes.intro') }}</p>
    </div>
    <nav class="container chips-row" aria-label="{{ __('ui.recipes.difficulty') }}">
        <a href="{{ lroute('recipes.index') }}" class="chip {{ ! request('difficulty') ? 'is-active' : '' }}">{{ __('ui.recipes.all') }}</a>
        @foreach (['easy', 'medium', 'hard'] as $d)
            <a href="{{ lroute('recipes.index', ['difficulty' => $d]) }}" class="chip {{ request('difficulty') === $d ? 'is-active' : '' }}">{{ __('ui.recipes.difficulties.'.$d) }}</a>
        @endforeach
    </nav>
</header>
<section class="section section--tight">
    <div class="container">
        @if ($recipes->isEmpty())
            <div class="empty-block"><p>{{ __('ui.recipes.empty') }}</p></div>
        @else
            <div class="rgrid">@foreach ($recipes as $recipe) @include('partials.recipe-card', ['recipe' => $recipe]) @endforeach</div>
            {{ $recipes->links() }}
        @endif
    </div>
</section>
@endsection
