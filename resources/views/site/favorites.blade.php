@extends('layouts.site')
@section('content')
<header class="page-hero"><div class="container"><h1 class="display-2">{{ __('ui.favorites.title') }}</h1></div></header>
<section class="section section--tight">
    <div class="container">
        @if ($products->isEmpty())
            <div class="empty-block"><p>{{ __('ui.favorites.empty') }}</p><a class="btn" href="{{ lroute('products.index') }}">{{ __('ui.hero.cta_products') }}</a></div>
        @else
            <div class="pgrid">@foreach ($products as $product) @include('partials.product-card', ['product' => $product]) @endforeach</div>
        @endif
    </div>
</section>
@endsection
