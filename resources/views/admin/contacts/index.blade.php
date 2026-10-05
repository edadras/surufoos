@extends('admin.layout')
@section('title', 'پیام‌ها')
@section('content')
<div class="a-head"><h1>پیام‌های فرم تماس</h1></div>
<form method="get" class="a-filters"><input type="search" name="q" value="{{ request('q') }}" placeholder="جستجو…"><label class="a-check"><input type="checkbox" name="unread" value="1" @checked(request('unread'))> فقط خوانده‌نشده</label><button class="a-btn a-btn--sm">فیلتر</button></form>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>نام</th><th>تماس</th><th>موضوع / پیام</th><th>زبان</th><th>تاریخ</th><th></th></tr></thead>
    <tbody>
    @forelse ($contacts as $c)
        <tr class="{{ $c->read_at ? '' : 'is-unread' }}">
            <td><a href="{{ route('admin.contacts.show', $c) }}"><strong>{{ $c->name }}</strong></a></td>
            <td dir="ltr">{{ $c->mobile }}<br>{{ $c->email }}</td>
            <td>{{ \Illuminate\Support\Str::limit($c->subject ?: $c->message, 90) }}</td>
            <td>{{ strtoupper($c->locale) }}</td>
            <td>{{ jdate($c->created_at, 'd MMM y HH:mm', 'fa') }}</td>
            <td class="a-table__actions"><a href="{{ route('admin.contacts.show', $c) }}" class="a-btn a-btn--ghost a-btn--sm">مشاهده</a>@include('admin.partials.delete-form', ['action' => route('admin.contacts.destroy', $c)])</td>
        </tr>
    @empty
        <tr><td colspan="6" class="a-empty">پیامی دریافت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $contacts])
@endsection
