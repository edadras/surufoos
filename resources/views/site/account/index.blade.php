@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<header class="page-hero">
    <div class="container page-hero__row">
        <h1 class="display-2">{{ __('ui.account.hello', ['name' => auth()->user()->name]) }}</h1>
        <form action="{{ lroute('account.logout') }}" method="post">@csrf<button class="btn btn--ghost btn--small">{{ __('ui.account.logout') }}</button></form>
    </div>
</header>
<section class="section section--tight">
    <div class="container">
        <h2 class="h2">{{ __('ui.account.orders') }}</h2>
        @if ($orders->isEmpty())
            <p class="muted">{{ __('ui.account.no_orders') }}</p>
        @else
            <div class="table-wrap"><table class="table">
                <thead><tr><th>{{ __('ui.account.order_number') }}</th><th>{{ __('ui.account.order_date') }}</th><th>{{ __('ui.account.order_status') }}</th><th>{{ __('ui.account.order_total') }}</th></tr></thead>
                <tbody>@foreach ($orders as $o)<tr><td dir="ltr">{{ $o->number }}</td><td>{{ jdate($o->created_at) }}</td><td>{{ $o->status->label() }}</td><td>{{ money($o->grand_total) }}</td></tr>@endforeach</tbody>
            </table></div>
        @endif
        <h2 class="h2">{{ __('ui.favorites.title') }}</h2>
        @if ($favorites->isEmpty())
            <p class="muted">{{ __('ui.favorites.empty') }}</p>
        @else
            <div class="pgrid">@foreach ($favorites as $product) @include('partials.product-card', ['product' => $product]) @endforeach</div>
        @endif
    </div>
</section>
@endsection
