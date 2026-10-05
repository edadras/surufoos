@extends('admin.layout')
@section('title', 'دسته‌بندی محصولات')
@section('content')
<div class="a-head"><h1>دسته‌بندی محصولات</h1><a href="{{ route('admin.categories.create') }}" class="a-btn">+ دسته جدید</a></div>
<p class="a-muted">برای تغییر ترتیب نمایش در «دنیای محصولات» و منو، ردیف‌ها را بکشید.</p>
<div class="a-card a-card--flush">
    <table class="a-table">
        <thead><tr><th></th><th></th><th>عنوان</th><th>والد</th><th>محصولات</th><th>وضعیت</th><th>ترجمه</th><th></th></tr></thead>
        <tbody data-sortable data-reorder-url="{{ route('admin.categories.reorder') }}">
        @forelse ($categories as $c)
            <tr data-id="{{ $c->id }}">
                <td class="a-drag">⋮⋮</td>
                <td class="a-table__thumb" style="background: {{ $c->theme_color }}">@if($c->image)<img src="{{ $c->image->variantUrl('thumbnail') }}" alt="">@endif</td>
                <td><a href="{{ route('admin.categories.edit', $c) }}"><strong>{{ $c->tr('title', 'fa', true) }}</strong></a></td>
                <td>{{ $c->parent_id ? $categories->firstWhere('id', $c->parent_id)?->tr('title', 'fa', true) : '—' }}</td>
                <td>{{ digits($c->products_count, 'fa') }}</td>
                <td>@include('admin.partials.status-badge', ['status' => $c->status])</td>
                <td>@include('admin.partials.tr-status', ['model' => $c])</td>
                <td class="a-table__actions"><a href="{{ route('admin.categories.edit', $c) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.categories.destroy', $c)])</td>
            </tr>
        @empty
            <tr><td colspan="8" class="a-empty">دسته‌ای ثبت نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
