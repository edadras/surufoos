@extends('admin.layout')
@section('title', 'ویرایش رسانه')
@section('content')
<form method="post" action="{{ route('admin.media.update', $media) }}">
    @csrf @method('PUT')
    <div class="a-head"><h1>{{ $media->original_name }}</h1><div class="a-head__actions"><a href="{{ route('admin.media.index') }}" class="a-btn a-btn--ghost">بازگشت</a><button class="a-btn">ذخیره</button></div></div>
    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                <h2>نقطه کانونی <small>روی تصویر کلیک کنید؛ در برش‌ها این نقطه دیده می‌ماند</small></h2>
                @if ($media->isImage())
                    <div class="a-focal" data-focal>
                        <img src="{{ $media->variantUrl('large') }}" alt="">
                        <span class="a-focal__dot" style="inset-inline-start: {{ $media->focal_x }}%; inset-block-start: {{ $media->focal_y }}%" data-focal-dot></span>
                    </div>
                @endif
                <input type="hidden" name="focal_x" value="{{ $media->focal_x }}" data-focal-x>
                <input type="hidden" name="focal_y" value="{{ $media->focal_y }}" data-focal-y>
            </section>
            <section class="a-card">
                <h2>متن جایگزین و کپشن</h2>
                @foreach (config('suru.locales') as $code => $meta)
                    @php($t = $media->translations->firstWhere('locale', $code))
                    <fieldset class="a-fieldset" dir="{{ $meta['dir'] }}"><legend>{{ $meta['native'] }}</legend>
                        <div class="a-field"><label>متن جایگزین (ALT)</label><input name="translations[{{ $code }}][alt]" value="{{ old("translations.$code.alt", $t?->alt) }}" maxlength="250"></div>
                        <div class="a-field"><label>کپشن</label><input name="translations[{{ $code }}][caption]" value="{{ old("translations.$code.caption", $t?->caption) }}"></div>
                    </fieldset>
                @endforeach
            </section>
        </div>
        <aside class="a-grid__side">
            <section class="a-card">
                <dl class="a-dl">
                    <dt>نوع</dt><dd dir="ltr">{{ $media->mime_type }}</dd>
                    <dt>ابعاد</dt><dd dir="ltr">{{ $media->width }}×{{ $media->height }}</dd>
                    <dt>حجم</dt><dd dir="ltr">{{ number_format($media->size / 1024) }} KB</dd>
                    <dt>رنگ غالب</dt><dd>@if($media->dominant_color)<span class="a-swatch-view" style="background: {{ $media->dominant_color }}"></span> {{ $media->dominant_color }}@else — @endif</dd>
                    <dt>پالت</dt><dd>@foreach ($media->palette ?? [] as $c)<span class="a-swatch-view" style="background: {{ $c }}" title="{{ $c }}"></span>@endforeach</dd>
                    <dt>پس‌زمینه شفاف</dt><dd>{{ $media->has_transparency ? 'بله' : 'خیر' }}</dd>
                    <dt>نسخه‌ها</dt><dd>{{ digits(count($media->variants ?? []), 'fa') }} ({{ $media->conversion_status }})</dd>
                    @if ($media->source_url)<dt>منبع</dt><dd dir="ltr" class="a-wrap">{{ $media->source_url }}</dd>@endif
                </dl>
                <div class="a-field"><label>پوشه</label><select name="folder_id"><option value="">بدون پوشه</option>@foreach ($folders as $f)<option value="{{ $f->id }}" @selected($media->folder_id == $f->id)>{{ $f->name }}</option>@endforeach</select></div>
                <p><a href="{{ $media->url() }}" target="_blank" class="a-link">مشاهده فایل اصلی</a></p>
            </section>
        </aside>
    </div>
</form>
<div class="a-danger-zone">
    <form method="post" action="{{ route('admin.media.regenerate', $media) }}" class="a-inline">@csrf<button class="a-btn a-btn--ghost a-btn--sm">ساخت مجدد نسخه‌ها</button></form>
    @include('admin.partials.delete-form', ['action' => route('admin.media.destroy', $media), 'confirm' => 'فایل و همه نسخه‌هایش حذف شود؟ اگر جایی استفاده شده باشد، آن بخش بدون تصویر می‌ماند.'])
</div>
@endsection
