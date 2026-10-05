@extends('admin.layout')
@section('title', 'درون‌ریزی Suru Food')
@section('content')
<div class="a-head"><h1>انتقال محتوای Suru Food</h1></div>
<div class="a-grid">
    <div class="a-grid__main">
        <section class="a-card">
            <h2>نتایج <small>هر رکورد منتقل‌شده با آدرس منبع و هش محتوا ثبت می‌شود</small></h2>
            <form method="get" class="a-filters">
                <select name="type"><option value="">همه انواع</option>@foreach (['settings', 'category', 'product', 'page', 'post', 'post_category'] as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select>
                <select name="status"><option value="">همه وضعیت‌ها</option>@foreach (\App\Models\ContentImportLog::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select>
                <button class="a-btn a-btn--sm">فیلتر</button>
            </form>
            <table class="a-table">
                <thead><tr><th>نوع</th><th>آدرس منبع</th><th>مقصد</th><th>وضعیت</th><th>زمان</th></tr></thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr><td>{{ $log->source_type }}</td><td dir="ltr" class="a-wrap"><a href="{{ $log->source_url }}" target="_blank" rel="noopener">{{ rawurldecode(\Illuminate\Support\Str::limit($log->source_url, 70)) }}</a></td>
                        <td>@if($log->destination_type && $log->destination_id){{ $log->destination_type }} #{{ $log->destination_id }}@else — @endif</td>
                        <td><span class="a-pill a-pill--{{ $log->status }}">{{ \App\Models\ContentImportLog::STATUSES[$log->status] ?? $log->status }}</span>@if($log->message)<br><small class="a-wrap">{{ \Illuminate\Support\Str::limit($log->message, 160) }}</small>@endif</td>
                        <td>{{ jdate($log->imported_at, 'd MMM HH:mm', 'fa') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="a-empty">هنوز درون‌ریزی انجام نشده است.</td></tr>
                @endforelse
                </tbody>
            </table>
            @include('admin.partials.pagination', ['paginator' => $logs])
        </section>
    </div>
    <aside class="a-grid__side">
        <form method="post" action="{{ route('admin.import.run') }}" class="a-card">@csrf
            <h2>اجرای درون‌ریزی</h2>
            <div class="a-field"><label for="source">آدرس سایت منبع</label><input id="source" name="source" dir="ltr" value="{{ old('source', $source) }}" required></div>
            <fieldset class="a-fieldset"><legend>بخش‌ها (خالی = همه)</legend>
                @foreach (['settings' => 'اطلاعات برند و تماس', 'categories' => 'دسته‌بندی‌ها', 'products' => 'محصولات و تصاویر', 'pages' => 'صفحات (درباره ما، تماس…)', 'posts' => 'مقالات و دسته‌های مجله', 'redirects' => 'ریدایرکت آدرس‌های قدیمی'] as $k => $v)
                    <label class="a-check"><input type="checkbox" name="only[]" value="{{ $k }}"> {{ $v }}</label>
                @endforeach
            </fieldset>
            <button class="a-btn">شروع درون‌ریزی</button>
            <p class="a-hint">ابتدا از API وردپرس/ووکامرس و در صورت نبود، از sitemap و داده‌های ساختاریافته صفحات خوانده می‌شود. تصاویر دانلود و در کتابخانه رسانه بهینه می‌شوند (Hotlink نمی‌شوند). اجرای مجدد فقط موارد تغییرکرده را به‌روزرسانی می‌کند. از خط فرمان: <code dir="ltr">php artisan suru:import</code></p>
        </form>
        <section class="a-card"><h2>خلاصه</h2>
            <table class="a-table a-table--compact">@forelse ($summary as $type => $rows)<tr><td>{{ $type }}</td><td>@foreach ($rows as $r){{ \App\Models\ContentImportLog::STATUSES[$r->status] ?? $r->status }}: {{ digits($r->total, 'fa') }} @endforeach</td></tr>@empty<tr><td class="a-muted">—</td></tr>@endforelse</table>
        </section>
    </aside>
</div>
@endsection
