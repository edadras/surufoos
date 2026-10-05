@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<header class="page-hero"><div class="container"><h1 class="display-2">{{ __('ui.cart.title') }}</h1></div></header>
<section class="section section--tight">
    <div class="container" data-cart-body>
        @error('cart')<div class="form-errors" role="alert">{{ $message }}</div>@enderror
        @include('site._cart-body')
    </div>
</section>
@endsection
