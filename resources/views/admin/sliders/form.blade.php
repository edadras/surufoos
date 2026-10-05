@extends('admin.layout')
@section('title', $slider->exists ? 'ویرایش اسلاید' : 'اسلاید جدید')
@php($layers = old('layers', $slider->layers ?? []))
@section('content')
<form method="post" action="{{ $slider->exists ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}" data-dirty-guard>
    @csrf @if($slider->exists) @method('PUT') @endif
    <div class="a-head"><h1>{{ $slider->exists ? $slider->tr('title', 'fa', true) : 'اسلاید جدید' }}</h1><button class="a-btn">ذخیره</button></div>
    <div class="a-grid">
        <div class="a-grid__main">
            <section class="a-card">
                <div class="a-tr" data-tr>
                    <div class="a-tr__tabs">@foreach (config('suru.locales') as $code => $meta)<button type="button" class="a-tr__tab {{ $loop->first ? 'is-active' : '' }}" data-tr-tab="{{ $code }}">{{ $meta['short'] }} <span class="a-tr__state {{ $slider->translation($code, false) ? 'is-done' : 'is-missing' }}">{{ $slider->translation($code, false) ? '✓' : '!' }}</span></button>@endforeach</div>
                    @foreach (config('suru.locales') as $code => $meta)
                        @php($t = $slider->translation($code, false))
                        <div class="a-tr__pane" data-tr-pane="{{ $code }}" @unless($loop->first) hidden @endunless dir="{{ $meta['dir'] }}">
                            @if ($code !== 'fa')<button type="button" class="a-btn a-btn--ghost a-btn--sm a-tr__copy" data-tr-copy="{{ $code }}">کپی از فارسی</button>@endif
                            @foreach (['eyebrow' => 'برچسب بالا', 'title' => 'عنوان اصلی', 'subtitle' => 'زیرعنوان', 'cta_label' => 'دکمه اول (پیش‌فرض: مشاهده محصولات)', 'cta_url' => 'لینک دکمه اول', 'cta2_label' => 'دکمه دوم (پیش‌فرض: داستان سورو)', 'cta2_url' => 'لینک دکمه دوم'] as $f => $label)
                                <div class="a-field"><label>{{ $label }} @if($f === 'title' && $code === 'fa')<b class="a-req">*</b>@endif</label>
                                    @if ($f === 'subtitle')<textarea name="translations[{{ $code }}][{{ $f }}]" rows="2" data-tr-field="{{ $f }}">{{ old("translations.$code.$f", $t?->$f) }}</textarea>
                                    @else<input name="translations[{{ $code }}][{{ $f }}]" value="{{ old("translations.$code.$f", $t?->$f) }}" data-tr-field="{{ $f }}" @if(str_ends_with($f, 'url')) dir="ltr" @endif>@endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="a-card">
                <h2>لایه‌های صحنه <small>مواد اولیه یا عناصر گرافیکی اطراف محصول</small></h2>
                <p class="a-hint">X و Y موقعیت (۰ تا ۱۰۰ درصد) در صحنه است. عمق ۰ تا ۱: کمتر از ۰٫۵ پشت محصول و بیشتر از آن جلوی محصول قرار می‌گیرد و حرکت بیشتری با ماوس دارد.</p>
                <div data-repeater>
                    <div class="a-repeater" data-repeater-list>
                        @foreach ($layers as $i => $layer) @include('admin.sliders._layer', ['i' => $i, 'layer' => $layer]) @endforeach
                    </div>
                    <template data-repeater-template>@include('admin.sliders._layer', ['i' => '__INDEX__', 'layer' => []])</template>
                    <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-repeater-add>+ افزودن لایه</button>
                </div>
            </section>
        </div>
        <aside class="a-grid__side">
            <section class="a-card">
                <label class="a-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slider->is_active))> فعال</label>
                <div class="a-field"><label for="placement">جایگاه</label><input id="placement" name="placement" dir="ltr" value="{{ old('placement', $slider->placement) }}"></div>
                <div class="a-field"><label for="sort_order">ترتیب</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $slider->sort_order ?? 0) }}"></div>
                <div class="a-field"><label for="product_id">محصول مرکز صحنه</label><select id="product_id" name="product_id"><option value="">—</option>@foreach ($products as $p)<option value="{{ $p->id }}" @selected(old('product_id', $slider->product_id) == $p->id)>{{ $p->tr('title', 'fa', true) }}</option>@endforeach</select>
                    <small class="a-hint">عکس بسته بدون پس‌زمینه این محصول در مرکز صحنه قرار می‌گیرد.</small></div>
                <div class="a-field"><label for="background_color">رنگ صحنه</label><div class="a-color"><input type="color" value="{{ old('background_color', $slider->background_color ?: '#ede3d1') }}" data-color-sync="background_color"><input id="background_color" name="background_color" dir="ltr" value="{{ old('background_color', $slider->background_color) }}" placeholder="خودکار از محصول"></div></div>
            </section>
            <section class="a-card">
                @include('admin.partials.media-field', ['name' => 'media_id', 'label' => 'تصویر پس‌زمینه / تصویر اصلی (بدون محصول)', 'value' => old('media_id', $slider->media_id)])
                @include('admin.partials.media-field', ['name' => 'mobile_media_id', 'label' => 'تصویر موبایل', 'value' => old('mobile_media_id', $slider->mobile_media_id)])
            </section>
        </aside>
    </div>
</form>
@endsection
