@extends('admin.layout')
@section('title', 'دستورهای غذایی')
@section('content')
<div class="a-head"><h1>دستورهای غذایی <small>{{ digits($recipes->total(), 'fa') }}</small></h1><a href="{{ route('admin.recipes.create') }}" class="a-btn">+ دستور جدید</a></div>
@include('admin.partials.filters', ['statuses' => \App\Enums\ContentStatus::options()])
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th></th><th>عنوان</th><th>زمان</th><th>محصولات سورو</th><th>وضعیت</th><th>ترجمه</th><th></th></tr></thead>
    <tbody>
    @forelse ($recipes as $r)
        <tr>
            <td class="a-table__thumb">@if($r->cover)<img src="{{ $r->cover->variantUrl('thumbnail') }}" alt="">@endif</td>
            <td><a href="{{ route('admin.recipes.edit', $r) }}"><strong>{{ $r->tr('title', 'fa', true) }}</strong></a></td>
            <td>{{ $r->totalMinutes() ? digits($r->totalMinutes(), 'fa').' دقیقه' : '—' }}</td>
            <td>{{ digits($r->products_count, 'fa') }}</td>
            <td>@include('admin.partials.status-badge', ['status' => $r->status])</td>
            <td>@include('admin.partials.tr-status', ['model' => $r])</td>
            <td class="a-table__actions"><a href="{{ route('admin.recipes.edit', $r) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.recipes.destroy', $r)])</td>
        </tr>
    @empty
        <tr><td colspan="7" class="a-empty">دستوری ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@include('admin.partials.pagination', ['paginator' => $recipes])
@endsection
