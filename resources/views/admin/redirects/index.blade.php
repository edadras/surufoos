@extends('admin.layout')
@section('title', 'ریدایرکت‌ها')
@section('content')
<div class="a-head"><h1>ریدایرکت‌ها</h1><a href="{{ route('admin.redirects.create') }}" class="a-btn">+ ریدایرکت</a></div>
<p class="a-muted">آدرس‌های قدیمی surufood.ir هنگام درون‌ریزی خودکار به آدرس‌های جدید ریدایرکت ۳۰۱ می‌شوند تا رتبه سئو حفظ شود.</p>
@include('admin.partials.filters')
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>از</th><th>به</th><th>کد</th><th>بازدید</th><th>فعال</th><th></th></tr></thead>
    <tbody>
    @forelse ($redirects as $r)
        <tr><td dir="ltr" class="a-wrap">{{ $r->from_path }}</td><td dir="ltr" class="a-wrap">{{ $r->to_path }}</td><td>{{ $r->status_code }}</td><td>{{ digits($r->hits, 'fa') }}</td><td>{{ $r->is_active ? '✓' : '—' }}</td>
            <td class="a-table__actions"><a href="{{ route('admin.redirects.edit', $r) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.redirects.destroy', $r)])</td></tr>
    @empty
        <tr><td colspan="6" class="a-empty">ریدایرکتی تعریف نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $redirects])
@endsection
