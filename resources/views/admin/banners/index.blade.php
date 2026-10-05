@extends('admin.layout')
@section('title', 'بنرها')
@section('content')
<div class="a-head"><h1>بنرهای تبلیغاتی</h1><a href="{{ route('admin.banners.create') }}" class="a-btn">+ بنر جدید</a></div>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th></th><th>عنوان</th><th>جایگاه</th><th>زبان</th><th>چیدمان</th><th>بازه نمایش</th><th>وضعیت</th><th></th></tr></thead>
    <tbody>
    @forelse ($banners as $b)
        <tr>
            <td class="a-table__thumb a-table__thumb--wide">@if($b->desktopMedia)<img src="{{ $b->desktopMedia->variantUrl('thumbnail') }}" alt="">@endif</td>
            <td><a href="{{ route('admin.banners.edit', $b) }}">{{ $b->title ?: 'بنر #'.$b->id }}</a></td>
            <td dir="ltr">{{ $b->placement }}</td>
            <td>{{ $b->locale ? strtoupper($b->locale) : 'همه' }}</td>
            <td>{{ $b->layout === 'split' ? 'دوتکه' : 'تمام‌عرض' }}</td>
            <td>{{ $b->starts_at ? jdate($b->starts_at, 'd MMM y', 'fa') : '—' }} تا {{ $b->ends_at ? jdate($b->ends_at, 'd MMM y', 'fa') : '—' }}</td>
            <td><span class="a-pill a-pill--{{ $b->isLive() ? 'published' : 'draft' }}">{{ $b->isLive() ? 'در حال نمایش' : 'غیرفعال' }}</span></td>
            <td class="a-table__actions"><a href="{{ route('admin.banners.edit', $b) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>@include('admin.partials.delete-form', ['action' => route('admin.banners.destroy', $b)])</td>
        </tr>
    @empty
        <tr><td colspan="8" class="a-empty">بنری ثبت نشده است.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
