@extends('admin.layout')
@section('title', 'صفحات')
@section('content')
<div class="a-head"><h1>صفحات و صفحه‌ساز</h1><a href="{{ route('admin.pages.create') }}" class="a-btn">+ صفحه جدید</a></div>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>عنوان</th><th>نوع</th><th>سکشن‌ها</th><th>وضعیت</th><th>ترجمه</th><th></th></tr></thead>
    <tbody>
    @foreach ($pages as $page)
        <tr>
            <td><a href="{{ route('admin.pages.edit', $page) }}"><strong>{{ $page->tr('title', 'fa', true) ?? \App\Models\Page::SYSTEM_KEYS[$page->key] ?? '—' }}</strong></a></td>
            <td>{{ $page->isSystem() ? 'صفحه سیستمی: '.\App\Models\Page::SYSTEM_KEYS[$page->key] : 'صفحه آزاد' }}</td>
            <td>{{ digits($page->sections_count, 'fa') }}</td>
            <td>@include('admin.partials.status-badge', ['status' => $page->status])</td>
            <td>@include('admin.partials.tr-status', ['model' => $page])</td>
            <td class="a-table__actions">
                <a href="{{ route('admin.pages.edit', $page) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش و سکشن‌ها</a>
                @if ($u = $page->url('fa'))<a href="{{ $u }}" target="_blank" class="a-btn a-btn--ghost a-btn--sm">نمایش</a>@endif
                @unless ($page->isSystem())@include('admin.partials.delete-form', ['action' => route('admin.pages.destroy', $page)])@endunless
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div>
@endsection
