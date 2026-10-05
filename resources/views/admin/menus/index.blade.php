@extends('admin.layout')
@section('title', 'منوها')
@section('content')
<div class="a-head"><h1>منوها</h1></div>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>نام</th><th>کلید</th><th>آیتم‌ها</th><th></th></tr></thead>
    <tbody>@foreach ($menus as $m)<tr><td>{{ $m->name }}</td><td dir="ltr">{{ $m->key }}</td><td>{{ digits($m->items_count, 'fa') }}</td><td><a href="{{ route('admin.menus.edit', $m) }}" class="a-btn a-btn--ghost a-btn--sm">مدیریت آیتم‌ها</a></td></tr>@endforeach</tbody>
</table></div>
@endsection
