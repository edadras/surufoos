@extends('admin.layout')
@section('title', 'دسته‌های مجله')
@section('content')
<div class="a-head"><h1>دسته‌های مجله</h1><a href="{{ route('admin.post-categories.create') }}" class="a-btn">+ دسته جدید</a></div>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>عنوان</th><th>مقالات</th><th>ترتیب</th><th>ترجمه</th><th></th></tr></thead>
    <tbody>
    @forelse ($categories as $c)
        <tr><td><a href="{{ route('admin.post-categories.edit', $c) }}">{{ $c->tr('title', 'fa', true) }}</a></td><td>{{ digits($c->posts_count, 'fa') }}</td><td>{{ digits($c->sort_order, 'fa') }}</td>
            <td>@include('admin.partials.tr-status', ['model' => $c])</td>
            <td class="a-table__actions"><a href="{{ route('admin.post-categories.edit', $c) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.post-categories.destroy', $c)])</td></tr>
    @empty
        <tr><td colspan="5" class="a-empty">دسته‌ای ثبت نشده. دسته‌ها باید براساس محتوای واقعی مجله Suru ساخته شوند.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
