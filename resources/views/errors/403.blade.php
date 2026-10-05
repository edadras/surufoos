@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<section class="section error-page">
    <div class="container container--narrow">
        <p class="error-page__code" aria-hidden="true">{{ digits('403') }}</p>
        <h1 class="display-2">{{ __('ui.errors.403_title') }}</h1>
        @if ('403' === '404')<p class="lead">{{ __('ui.errors.404_text') }}</p>@endif
        <div class="hero__ctas">
            <a href="{{ lroute('home') }}" class="btn">{{ __('ui.errors.home') }}</a>
            @if ('403' === '404')<a href="{{ lroute('products.index') }}" class="btn btn--ghost">{{ __('ui.nav.products') }}</a>@endif
        </div>
    </div>
</section>
@endsection
