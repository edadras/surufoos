@extends('admin.layout')
@section('title', 'زبان‌ها')
@section('content')
<div class="a-head"><h1>زبان‌های سایت</h1></div>
<div class="a-card a-card--flush"><table class="a-table">
    <thead><tr><th>زبان</th><th>کد</th><th>جهت</th><th>آدرس</th><th>پوشش ترجمه</th><th></th></tr></thead>
    <tbody>@foreach ($locales as $code => $meta)
        <tr><td>{{ $meta['native'] }}</td><td dir="ltr">{{ $code }}</td><td>{{ $meta['dir'] === 'rtl' ? 'راست‌به‌چپ' : 'چپ‌به‌راست' }}</td><td dir="ltr"><a href="{{ route('home', ['locale' => $code]) }}" target="_blank">/{{ $code }}</a></td>
            <td><div class="a-progress"><span style="width: {{ $coverage[$code] }}%"></span></div> {{ digits($coverage[$code], 'fa') }}٪</td>
            <td>@if($code !== 'fa')<a href="{{ route('admin.translations.index', ['type' => 'products', 'locale' => $code]) }}" class="a-btn a-btn--ghost a-btn--sm">موارد ناقص</a>@endif</td></tr>
    @endforeach</tbody>
</table></div>
<div class="a-card">
    <h2>رفتار محتوای ترجمه‌نشده</h2>
    <p>{{ $fallback ? 'فعال: اگر ترجمه‌ای وجود نداشته باشد متن فارسی نمایش داده می‌شود (با noindex).' : 'غیرفعال: محتوایی که ترجمه ندارد در آن زبان نمایش داده نمی‌شود.' }}</p>
    <p class="a-hint">با متغیر محیطی <code dir="ltr">SURU_CONTENT_FALLBACK</code> قابل تغییر است. متن‌های ثابت رابط کاربری (دکمه‌ها، برچسب‌ها) در فایل‌های <code dir="ltr">lang/fa|en|ar/ui.php</code> قرار دارند.</p>
</div>
@endsection
