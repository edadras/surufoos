@extends('admin.layout')
@section('title', 'سفارش‌ها')
@section('content')
<div class="a-head"><h1>سفارش‌ها</h1></div>
@if (! settings()->commerceEnabled())<div class="a-alert a-alert--warn">فروش آنلاین در تنظیمات غیرفعال است؛ سفارش جدیدی ثبت نمی‌شود. <a href="{{ route('admin.settings.edit', 'commerce') }}">فعال‌سازی</a></div>@endif
@include('admin.partials.filters', ['statuses' => \App\Enums\OrderStatus::options()])
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>شماره</th><th>مشتری</th><th>اقلام</th><th>مبلغ</th><th>پرداخت</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
    <tbody>
    @forelse ($orders as $o)
        <tr><td><a href="{{ route('admin.orders.show', $o) }}" dir="ltr">{{ $o->number }}</a></td><td>{{ $o->customer_name }}<br><small dir="ltr">{{ $o->customer_mobile }}</small></td>
            <td>{{ digits($o->items_count, 'fa') }}</td><td>{{ digits(number_format($o->grand_total), 'fa') }}</td>
            <td>{{ $o->payment_status->label() }}</td><td><span class="a-pill a-pill--{{ $o->status->value }}">{{ $o->status->label() }}</span></td><td>{{ jdate($o->created_at, 'd MMM y HH:mm', 'fa') }}</td></tr>
    @empty
        <tr><td colspan="7" class="a-empty">سفارشی ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $orders])
@endsection
