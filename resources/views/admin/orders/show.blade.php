@extends('admin.layout')
@section('title', 'سفارش '.$order->number)
@section('content')
<div class="a-head"><h1>سفارش <span dir="ltr">{{ $order->number }}</span></h1><a href="{{ route('admin.orders.index') }}" class="a-btn a-btn--ghost">بازگشت</a></div>
<div class="a-grid">
    <div class="a-grid__main">
        <section class="a-card a-card--flush"><table class="a-table">
            <thead><tr><th>محصول</th><th>SKU</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th></tr></thead>
            <tbody>
                @foreach ($order->items as $i)<tr><td>@if($i->product_id)<a href="{{ route('admin.products.edit', $i->product_id) }}">{{ $i->product_title }}</a>@else{{ $i->product_title }}@endif</td><td dir="ltr">{{ $i->sku }}</td><td>{{ digits(number_format($i->unit_price), 'fa') }}</td><td>{{ digits($i->quantity, 'fa') }}</td><td>{{ digits(number_format($i->line_total), 'fa') }}</td></tr>@endforeach
                <tr><td colspan="4">هزینه ارسال</td><td>{{ digits(number_format($order->shipping_total), 'fa') }}</td></tr>
                <tr><th colspan="4">جمع کل (تومان)</th><th>{{ digits(number_format($order->grand_total), 'fa') }}</th></tr>
            </tbody>
        </table></section>
        <section class="a-card"><h2>ارسال</h2>
            <p>{{ $order->customer_name }} — <span dir="ltr">{{ $order->customer_mobile }}</span> {{ $order->customer_email }}</p>
            <p>{{ $order->province }}، {{ $order->city }} — {{ $order->address }} @if($order->postal_code)— کد پستی <span dir="ltr">{{ $order->postal_code }}</span>@endif</p>
            @if ($order->note)<p class="a-muted">توضیح مشتری: {{ $order->note }}</p>@endif
        </section>
    </div>
    <aside class="a-grid__side">
        <form method="post" action="{{ route('admin.orders.update', $order) }}" class="a-card">@csrf @method('PUT')
            <div class="a-field"><label>وضعیت سفارش</label><select name="status">@foreach (\App\Enums\OrderStatus::options() as $k => $v)<option value="{{ $k }}" @selected($order->status->value === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="a-field"><label>وضعیت پرداخت</label><select name="payment_status">@foreach (\App\Enums\PaymentStatus::options() as $k => $v)<option value="{{ $k }}" @selected($order->payment_status->value === $k)>{{ $v }}</option>@endforeach</select></div>
            <div class="a-field"><label>یادداشت داخلی</label><textarea name="admin_note" rows="3">{{ $order->admin_note }}</textarea></div>
            <button class="a-btn a-btn--sm">ذخیره</button>
        </form>
        <section class="a-card"><dl class="a-dl"><dt>روش پرداخت</dt><dd>{{ __('ui.checkout.methods.'.$order->payment_method, [], 'fa') }}</dd><dt>زبان</dt><dd>{{ strtoupper($order->locale) }}</dd><dt>تاریخ</dt><dd>{{ jdate($order->created_at, 'd MMMM y HH:mm', 'fa') }}</dd><dt>حساب کاربری</dt><dd>{{ $order->user?->email ?? 'مهمان' }}</dd></dl></section>
    </aside>
</div>
@endsection
