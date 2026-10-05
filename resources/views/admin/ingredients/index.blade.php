@extends('admin.layout')
@section('title', 'مواد اولیه')
@section('content')
<div class="a-head"><h1>مواد اولیه</h1><a href="{{ route('admin.ingredients.create') }}" class="a-btn">+ ماده اولیه</a></div>
<div class="a-card a-card--flush">
    <table class="a-table">
        <thead><tr><th></th><th>نام</th><th>محصولات</th><th>ترجمه</th><th></th></tr></thead>
        <tbody>
        @forelse ($ingredients as $ing)
            <tr>
                <td class="a-table__thumb" style="background: {{ $ing->color }}">@if($ing->media)<img src="{{ $ing->media->variantUrl('thumbnail') }}" alt="">@endif</td>
                <td><a href="{{ route('admin.ingredients.edit', $ing) }}"><strong>{{ $ing->tr('name', 'fa', true) }}</strong></a></td>
                <td>{{ digits($ing->products_count, 'fa') }}</td>
                <td>@include('admin.partials.tr-status', ['model' => $ing])</td>
                <td class="a-table__actions"><a href="{{ route('admin.ingredients.edit', $ing) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.ingredients.destroy', $ing)])</td>
            </tr>
        @empty
            <tr><td colspan="5" class="a-empty">ماده اولیه‌ای ثبت نشده است.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $ingredients])
@endsection
