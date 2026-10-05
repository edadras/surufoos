@extends('layouts.site')
@section('content')
<header class="page-hero">
    <div class="container">
        @include('partials.breadcrumbs')
        <h1 class="display-1" data-split="lines">{{ __('ui.categories.title') }}</h1>
        <p class="lead">{{ __('ui.categories.intro') }}</p>
    </div>
</header>
@if ($categories->isEmpty())
    <div class="container empty-block"><p>{{ __('ui.empty_site') }}</p></div>
@else
    @include('sections.product-universe', ['categories' => $categories, 'content' => null, 'num' => ''])
@endif
@endsection
