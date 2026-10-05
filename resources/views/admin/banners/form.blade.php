@extends('admin.layout')
@section('title', $banner->exists ? 'ویرایش بنر' : 'بنر جدید')
@section('content')
<form method="post" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}">
    @csrf @if($banner->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $banner->exists ? ($banner->title ?: 'بنر') : 'بنر جدید' }}</h1><button class="a-btn">ذخیره</button></div>
    <div class="a-grid">
        <div class="a-grid__main"><section class="a-card">
            <div class="a-field"><label for="title">عنوان</label><input id="title" name="title" value="{{ old('title', $banner->title) }}"></div>
            <div class="a-field"><label for="subtitle">زیرعنوان</label><textarea id="subtitle" name="subtitle" rows="2">{{ old('subtitle', $banner->subtitle) }}</textarea></div>
            <div class="a-row">
                <div class="a-field"><label for="cta_label">متن دکمه</label><input id="cta_label" name="cta_label" value="{{ old('cta_label', $banner->cta_label) }}"></div>
                <div class="a-field"><label for="url">لینک (URL)</label><input id="url" name="url" dir="ltr" value="{{ old('url', $banner->url) }}"></div>
            </div>
            <div class="a-row">
                @include('admin.partials.media-field', ['name' => 'desktop_media_id', 'label' => 'تصویر دسکتاپ *', 'value' => old('desktop_media_id', $banner->desktop_media_id)])
                @include('admin.partials.media-field', ['name' => 'mobile_media_id', 'label' => 'تصویر موبایل', 'value' => old('mobile_media_id', $banner->mobile_media_id)])
            </div>
        </section></div>
        <aside class="a-grid__side"><section class="a-card">
            <label class="a-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active))> فعال</label>
            <div class="a-field"><label for="locale">زبان</label><select id="locale" name="locale"><option value="">همه زبان‌ها</option>@foreach (config('suru.locales') as $c => $m)<option value="{{ $c }}" @selected(old('locale', $banner->locale) === $c)>{{ $m['native'] }}</option>@endforeach</select></div>
            <div class="a-field"><label for="placement">جایگاه</label><input id="placement" name="placement" dir="ltr" value="{{ old('placement', $banner->placement) }}"><small class="a-hint">در سکشن «بنرها» همین جایگاه را انتخاب کنید (پیش‌فرض: home).</small></div>
            <div class="a-field"><label for="layout">چیدمان</label><select id="layout" name="layout"><option value="full" @selected(old('layout', $banner->layout) === 'full')>تمام‌عرض</option><option value="split" @selected(old('layout', $banner->layout) === 'split')>دوتکه (Split)</option></select></div>
            <div class="a-row">
                <div class="a-field"><label>رنگ پس‌زمینه</label><input type="color" name="color" value="{{ old('color', $banner->color ?: '#ede3d1') }}"></div>
                <div class="a-field"><label>رنگ متن</label><input type="color" name="text_color" value="{{ old('text_color', $banner->text_color ?: '#1d2620') }}"></div>
            </div>
            <div class="a-field"><label for="starts_at">شروع نمایش</label><input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i')) }}"></div>
            <div class="a-field"><label for="ends_at">پایان نمایش</label><input type="datetime-local" id="ends_at" name="ends_at" value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i')) }}"></div>
            <div class="a-field"><label for="sort_order">ترتیب</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}"></div>
        </section></aside>
    </div>
</form>
@endsection
