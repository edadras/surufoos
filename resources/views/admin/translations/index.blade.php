@extends('admin.layout')
@section('title', 'مدیریت ترجمه')
@section('content')
<div class="a-head"><h1>مدیریت ترجمه</h1></div>
<div class="a-card a-card--flush">
    <table class="a-table a-table--compact">
        <thead><tr><th>بخش</th><th>تعداد</th>@foreach (config('suru.locales') as $c => $m)<th>{{ $m['short'] }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach ($summary as $key => $row)
            <tr class="{{ $key === $type ? 'is-current' : '' }}">
                <td><a href="{{ route('admin.translations.index', ['type' => $key]) }}">{{ $row['label'] }}</a></td>
                <td>{{ digits($row['total'], 'fa') }}</td>
                @foreach (config('suru.locales') as $c => $m)
                    @php($missing = $row['total'] - $row[$c])
                    <td>@if($row['total'])<a href="{{ route('admin.translations.index', ['type' => $key, 'locale' => $c]) }}" class="a-trs"><span class="{{ $missing ? 'is-missing' : 'is-done' }}">{{ $missing ? digits($missing, 'fa').' ناقص' : '✓' }}</span></a>@endif</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="a-head"><h2>{{ $label }} @if($locale)<small>بدون ترجمه {{ config("suru.locales.$locale.native") }}</small>@endif</h2></div>
<div class="a-card a-card--flush">
    <table class="a-table">
        <thead><tr><th>#</th><th>عنوان (فارسی)</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $item)
            <tr>
                <td>{{ digits($item->id, 'fa') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($item->tr($primary, 'fa', true) ?? ($item->original_name ?? $item->code ?? '—'), 80) }}</td>
                <td>@include('admin.partials.tr-status', ['model' => $item])</td>
                <td><a href="{{ route('admin.translations.edit', [$type, $item->id]) }}" class="a-btn a-btn--ghost a-btn--sm">ترجمه</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="a-empty">موردی نیست.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $items])
@endsection
