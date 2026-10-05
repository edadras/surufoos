@extends('admin.layout')
@section('title', 'کاربران')
@section('content')
<div class="a-head"><h1>کاربران</h1><a href="{{ route('admin.users.create') }}" class="a-btn">+ کاربر جدید</a></div>
<form method="get" class="a-filters"><input type="search" name="q" value="{{ request('q') }}" placeholder="نام یا ایمیل…">
    <select name="type"><option value="">همه</option><option value="admins" @selected(request('type') === 'admins')>مدیران</option><option value="customers" @selected(request('type') === 'customers')>مشتریان</option></select>
    <button class="a-btn a-btn--sm">فیلتر</button></form>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>نام</th><th>ایمیل</th><th>نقش‌ها</th><th>2FA</th><th>آخرین ورود</th><th>فعال</th><th></th></tr></thead>
    <tbody>
    @foreach ($users as $u)
        <tr><td><a href="{{ route('admin.users.edit', $u) }}">{{ $u->name }}</a> @if($u->is_admin)<span class="a-pill a-pill--published">مدیر</span>@endif</td><td dir="ltr">{{ $u->email }}</td>
            <td>{{ $u->roles->pluck('name')->implode('، ') ?: '—' }}</td><td>{{ $u->hasTwoFactorEnabled() ? '✓' : '—' }}</td>
            <td>{{ $u->last_login_at ? jdate($u->last_login_at, 'd MMM y HH:mm', 'fa') : '—' }}</td><td>{{ $u->is_active ? '✓' : '—' }}</td>
            <td class="a-table__actions"><a href="{{ route('admin.users.edit', $u) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@unless($u->is(auth()->user()))@include('admin.partials.delete-form', ['action' => route('admin.users.destroy', $u)])@endunless</td></tr>
    @endforeach
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $users])
@endsection
