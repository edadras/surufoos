@extends('admin.layout')
@section('title', 'اسلایدر')
@section('content')
<div class="a-head"><h1>اسلایدهای هیرو</h1><a href="{{ route('admin.sliders.create') }}" class="a-btn">+ اسلاید جدید</a></div>
<p class="a-muted">اولین اسلاید فعال جایگاه <code>home_hero</code> در صحنه هیرو صفحه اصلی نمایش داده می‌شود: محصول در مرکز و لایه‌های مواد اولیه در عمق‌های مختلف.</p>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th></th><th>عنوان</th><th>محصول مرکزی</th><th>جایگاه</th><th>ترتیب</th><th>فعال</th><th>ترجمه</th><th></th></tr></thead>
    <tbody>
    @forelse ($sliders as $s)
        <tr>
            <td class="a-table__thumb">@if($m = $s->product?->displayImage() ?? $s->media)<img src="{{ $m->variantUrl('thumbnail') }}" alt="">@endif</td>
            <td><a href="{{ route('admin.sliders.edit', $s) }}"><strong>{{ $s->tr('title', 'fa', true) }}</strong></a></td>
            <td>{{ $s->product?->tr('title', 'fa', true) ?? '—' }}</td>
            <td dir="ltr">{{ $s->placement }}</td><td>{{ digits($s->sort_order, 'fa') }}</td><td>{{ $s->is_active ? '✓' : '—' }}</td>
            <td>@include('admin.partials.tr-status', ['model' => $s])</td>
            <td class="a-table__actions"><a href="{{ route('admin.sliders.edit', $s) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.sliders.destroy', $s)])</td>
        </tr>
    @empty
        <tr><td colspan="8" class="a-empty">اسلایدی ساخته نشده است. تا وقتی اسلاید فعالی وجود نداشته باشد، سکشن هیرو نمایش داده نمی‌شود.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
