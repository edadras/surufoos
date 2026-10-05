@extends('admin.layout')
@section('title', 'خبرنامه')
@section('content')
<div class="a-head"><h1>اعضای خبرنامه <small>{{ digits($subscribers->total(), 'fa') }}</small></h1><a href="{{ route('admin.newsletter.export') }}" class="a-btn a-btn--ghost">خروجی CSV</a></div>
@include('admin.partials.filters', ['statuses' => ['active' => 'فعال', 'unsubscribed' => 'لغو عضویت']])
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>ایمیل</th><th>زبان</th><th>وضعیت</th><th>تاریخ عضویت</th><th></th></tr></thead>
    <tbody>
    @forelse ($subscribers as $s)
        <tr><td dir="ltr">{{ $s->email }}</td><td>{{ strtoupper($s->locale) }}</td><td>{{ $s->status === 'active' ? 'فعال' : 'لغو عضویت' }}</td><td>{{ jdate($s->created_at, 'd MMM y', 'fa') }}</td>
            <td>@include('admin.partials.delete-form', ['action' => route('admin.newsletter.destroy', $s)])</td></tr>
    @empty
        <tr><td colspan="5" class="a-empty">عضوی ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $subscribers])
@endsection
