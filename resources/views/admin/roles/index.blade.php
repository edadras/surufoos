@extends('admin.layout')
@section('title', 'نقش‌ها')
@section('content')
<div class="a-head"><h1>نقش‌ها و دسترسی‌ها</h1><a href="{{ route('admin.roles.create') }}" class="a-btn">+ نقش جدید</a></div>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>نقش</th><th>کلید</th><th>دسترسی‌ها</th><th>کاربران</th><th></th></tr></thead>
    <tbody>@foreach ($roles as $r)<tr><td><a href="{{ route('admin.roles.edit', $r) }}">{{ $r->name }}</a></td><td dir="ltr">{{ $r->key }}</td>
        <td class="a-wrap">{{ $r->key === 'super_admin' ? 'همه دسترسی‌ها' : $r->permissions->pluck('name')->implode('، ') }}</td><td>{{ digits($r->users_count, 'fa') }}</td>
        <td class="a-table__actions"><a href="{{ route('admin.roles.edit', $r) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@if($r->key !== 'super_admin')@include('admin.partials.delete-form', ['action' => route('admin.roles.destroy', $r)])@endif</td></tr>@endforeach</tbody>
</table></div>
@endsection
