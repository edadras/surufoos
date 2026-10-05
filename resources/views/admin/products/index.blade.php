@extends('admin.layout')
@section('title', 'محصولات')
@section('content')
<div class="a-head">
    <h1>محصولات <small>{{ digits($products->total(), 'fa') }}</small></h1>
    <a href="{{ route('admin.products.create') }}" class="a-btn">+ محصول جدید</a>
</div>
<form method="get" class="a-filters">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="نام یا کد محصول…">
    <select name="status"><option value="">همه وضعیت‌ها</option>@foreach (\App\Enums\ContentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select>
    <select name="category"><option value="">همه دسته‌ها</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->tr('title', 'fa', true) }}</option>@endforeach</select>
    <select name="stock"><option value="">همه موجودی‌ها</option>@foreach (\App\Enums\StockStatus::options() as $k => $v)<option value="{{ $k }}" @selected(request('stock') === $k)>{{ $v }}</option>@endforeach</select>
    <select name="translation"><option value="">وضعیت ترجمه</option><option value="en" @selected(request('translation') === 'en')>بدون ترجمه انگلیسی</option><option value="ar" @selected(request('translation') === 'ar')>بدون ترجمه عربی</option></select>
    <label class="a-check"><input type="checkbox" name="issue" value="incomplete" @checked(request('issue') === 'incomplete')> فقط ناقص‌ها</label>
    <button class="a-btn a-btn--sm">فیلتر</button>
</form>
<div class="a-card a-card--flush">
    <table class="a-table">
        <thead><tr><th></th><th>محصول</th><th>دسته</th><th>قیمت</th><th>موجودی</th><th>وضعیت</th><th>ترجمه</th><th>بازدید</th><th></th></tr></thead>
        <tbody>
        @forelse ($products as $p)
            <tr>
                <td class="a-table__thumb">@if($img = $p->displayImage())<img src="{{ $img->variantUrl('thumbnail') }}" alt="">@endif</td>
                <td><a href="{{ route('admin.products.edit', $p) }}"><strong>{{ $p->tr('title', 'fa', true) ?? '— بدون عنوان —' }}</strong></a>@if($p->sku)<br><small dir="ltr">{{ $p->sku }}</small>@endif</td>
                <td>{{ $p->primaryCategory?->tr('title', 'fa', true) ?? '—' }}</td>
                <td>{{ $p->price ? digits(number_format($p->price), 'fa') : '—' }}</td>
                <td>{{ $p->stock_status->label() }}</td>
                <td>@include('admin.partials.status-badge', ['status' => $p->status])</td>
                <td>@include('admin.partials.tr-status', ['model' => $p])</td>
                <td>{{ digits($p->views_count, 'fa') }}</td>
                <td class="a-table__actions">
                    <a href="{{ route('admin.products.edit', $p) }}" class="a-btn a-btn--ghost a-btn--sm">ویرایش</a>
                    @if ($url = $p->url('fa'))<a href="{{ $url }}" target="_blank" class="a-btn a-btn--ghost a-btn--sm">نمایش</a>@endif
                    <form action="{{ route('admin.products.duplicate', $p) }}" method="post" class="a-inline">@csrf<button class="a-btn a-btn--ghost a-btn--sm">کپی</button></form>
                    @include('admin.partials.delete-form', ['action' => route('admin.products.destroy', $p)])
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="a-empty">محصولی پیدا نشد. محصولات واقعی را از بخش «درون‌ریزی Suru Food» منتقل کنید یا محصول جدید بسازید.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@include('admin.partials.pagination', ['paginator' => $products])
@endsection
