@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<section class="section">
    <div class="container container--narrow empty-block empty-block--success">
        <span class="success-mark"><x-icon name="check" size="40"/></span>
        <h1 class="display-2">{{ __('ui.checkout.complete') }}</h1>
        <p class="lead">{{ __('ui.checkout.thanks', ['number' => $order->number]) }}</p>
        <p>{{ __('ui.checkout.next_steps') }}</p>
        <ul class="mini-lines">
            @foreach ($order->items as $i)<li><span>{{ $i->product_title }} × {{ digits($i->quantity) }}</span><span>{{ money($i->line_total) }}</span></li>@endforeach
            <li><strong>{{ __('ui.cart.total') }}</strong><strong>{{ money($order->grand_total) }}</strong></li>
        </ul>
        <a href="{{ lroute('products.index') }}" class="btn">{{ __('ui.hero.cta_products') }}</a>
    </div>
</section>
@endsection
