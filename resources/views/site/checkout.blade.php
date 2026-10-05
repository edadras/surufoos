@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<header class="page-hero"><div class="container"><h1 class="display-2">{{ __('ui.checkout.title') }}</h1></div></header>
<section class="section section--tight">
    <div class="container cart">
        <form action="{{ lroute('checkout.store') }}" method="post" class="form-card" novalidate>
            @csrf
            <h2 class="h3">{{ __('ui.checkout.shipping_info') }}</h2>
            @if ($errors->any())<div class="form-errors" role="alert"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <div class="field-row">
                <div class="field"><label for="k-name">{{ __('ui.form.name') }}</label><input id="k-name" name="customer_name" required value="{{ old('customer_name', $address?->recipient_name ?? auth()->user()?->name) }}" autocomplete="name"></div>
                <div class="field"><label for="k-mobile">{{ __('ui.form.mobile') }}</label><input id="k-mobile" name="customer_mobile" required dir="ltr" inputmode="tel" value="{{ old('customer_mobile', $address?->recipient_mobile ?? auth()->user()?->mobile) }}" autocomplete="tel"></div>
            </div>
            <div class="field"><label for="k-email">{{ __('ui.form.email') }} <small>({{ __('ui.form.optional') }})</small></label><input id="k-email" type="email" name="customer_email" dir="ltr" value="{{ old('customer_email', auth()->user()?->email) }}"></div>
            <div class="field-row">
                <div class="field"><label for="k-prov">{{ __('ui.form.province') }}</label><input id="k-prov" name="province" required value="{{ old('province', $address?->province) }}"></div>
                <div class="field"><label for="k-city">{{ __('ui.form.city') }}</label><input id="k-city" name="city" required value="{{ old('city', $address?->city) }}"></div>
            </div>
            <div class="field"><label for="k-addr">{{ __('ui.form.address') }}</label><textarea id="k-addr" name="address" rows="3" required>{{ old('address', $address?->address) }}</textarea></div>
            <div class="field"><label for="k-post">{{ __('ui.form.postal_code') }}</label><input id="k-post" name="postal_code" dir="ltr" inputmode="numeric" value="{{ old('postal_code', $address?->postal_code) }}"></div>
            <div class="field"><label for="k-note">{{ __('ui.form.note') }}</label><textarea id="k-note" name="note" rows="2">{{ old('note') }}</textarea></div>
            <fieldset class="filter-group">
                <legend>{{ __('ui.form.payment_method') }}</legend>
                @foreach ($methods as $m)
                    <label class="radio"><input type="radio" name="payment_method" value="{{ $m }}" @checked(old('payment_method', $methods[0]) === $m)> <span>{{ __('ui.checkout.methods.'.$m) }}</span></label>
                @endforeach
                @if (in_array('bank_transfer', $methods, true) && ($info = settings('bank_transfer_info')))
                    <div class="prose muted">{!! rich_text($info) !!}</div>
                @endif
            </fieldset>
            <button type="submit" class="btn btn--wide">{{ __('ui.checkout.submit') }}</button>
        </form>
        <aside class="cart__summary">
            <h2 class="h3">{{ __('ui.checkout.summary') }}</h2>
            <ul class="mini-lines">
                @foreach ($items as $item)<li><span>{{ $item->product->tr('title') }} × {{ digits($item->quantity) }}</span><span>{{ money($item->unitPrice() * $item->quantity) }}</span></li>@endforeach
            </ul>
            <dl>
                <div><dt>{{ __('ui.cart.shipping') }}</dt><dd>{{ $totals['shipping'] ? money($totals['shipping']) : __('ui.cart.free') }}</dd></div>
                <div class="cart__total"><dt>{{ __('ui.cart.total') }}</dt><dd>{{ money($totals['total']) }}</dd></div>
            </dl>
        </aside>
    </div>
</section>
@endsection
