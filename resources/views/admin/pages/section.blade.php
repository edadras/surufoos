@extends('admin.layout')
@section('title', 'ویرایش سکشن: '.$section->type->label())
@section('content')
<form method="post" action="{{ route('admin.pages.sections.update', [$page, $section]) }}" data-dirty-guard>
    @csrf @method('PUT')
    <div class="a-head"><h1>{{ $section->type->label() }} <small>در صفحه «{{ $page->tr('title', 'fa', true) ?? $page->key }}»</small></h1>
        <div class="a-head__actions"><a href="{{ route('admin.pages.edit', $page) }}" class="a-btn a-btn--ghost">بازگشت</a><button class="a-btn">ذخیره</button></div></div>
    <div class="a-grid">
        <div class="a-grid__main"><section class="a-card">
            <h2>متن‌ها</h2>
            <div class="a-tr" data-tr>
                <div class="a-tr__tabs" role="tablist">
                    @foreach (config('suru.locales') as $code => $meta)
                        @php($done = (bool) $section->translation($code, false))
                        <button type="button" role="tab" class="a-tr__tab {{ $loop->first ? 'is-active' : '' }}" data-tr-tab="{{ $code }}">{{ $meta['short'] }} <span class="a-tr__state {{ $done ? 'is-done' : 'is-missing' }}">{{ $done ? '✓' : '!' }}</span></button>
                    @endforeach
                </div>
                @foreach (config('suru.locales') as $code => $meta)
                    @php($t = $section->translation($code, false))
                    <div class="a-tr__pane" data-tr-pane="{{ $code }}" @unless($loop->first) hidden @endunless dir="{{ $meta['dir'] }}">
                        @if ($code !== 'fa')<button type="button" class="a-btn a-btn--ghost a-btn--sm a-tr__copy" data-tr-copy="{{ $code }}">کپی از فارسی</button>@endif
                        @foreach (['eyebrow' => 'برچسب بالای عنوان', 'title' => 'عنوان', 'subtitle' => 'زیرعنوان', 'cta_label' => 'متن دکمه', 'cta_url' => 'لینک دکمه'] as $f => $label)
                            <div class="a-field"><label>{{ $label }}</label><input name="translations[{{ $code }}][{{ $f }}]" value="{{ old("translations.$code.$f", $t?->$f) }}" data-tr-field="{{ $f }}" @if($f === 'cta_url') dir="ltr" @endif></div>
                        @endforeach
                        <div class="a-field"><label>متن اصلی</label><textarea name="translations[{{ $code }}][body]" rows="8" data-tr-field="body" data-rich>{{ old("translations.$code.body", $t?->body) }}</textarea></div>
                        @if ($section->type === \App\Enums\SectionType::Story)
                            <div class="a-field"><label>کلمات کلیدی داستان (هر خط یک کلمه — به صورت تایپوگرافی بزرگ نمایش داده می‌شوند)</label>
                                <textarea name="translations[{{ $code }}][items]" rows="4" data-tr-field="items">{{ old("translations.$code.items", collect($t?->items ?? [])->pluck('text')->implode("\n")) }}</textarea></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section></div>
        <aside class="a-grid__side"><section class="a-card">
            <h2>تنظیمات</h2>
            <label class="a-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active))> فعال</label>
            @forelse ($schema as $key => $type)
                @php($val = old("settings.$key", $section->setting($key)))
                @switch($type)
                    @case('media') @include('admin.partials.media-field', ['name' => "settings[$key]", 'label' => $key === 'poster_media_id' ? 'تصویر پوستر' : ($key === 'secondary_media_id' ? 'تصویر دوم' : 'تصویر'), 'value' => $val]) @break
                    @case('media_multi') @include('admin.partials.media-multi', ['name' => "settings[$key]", 'label' => 'تصاویر', 'values' => (array) $val]) @break
                    @case('number') <div class="a-field"><label>تعداد آیتم‌ها</label><input type="number" name="settings[{{ $key }}]" value="{{ $val }}" min="1" max="48"></div> @break
                    @case('boolean') <label class="a-check"><input type="checkbox" name="settings[{{ $key }}]" value="1" @checked($val)> چیدمان معکوس</label> @break
                    @case('category') <div class="a-field"><label>دسته (برای منبع «دسته»)</label><select name="settings[{{ $key }}]"><option value="">—</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected($val == $c->id)>{{ $c->tr('title', 'fa', true) }}</option>@endforeach</select></div> @break
                    @case('product_source') <div class="a-field"><label>منبع محصولات</label><select name="settings[{{ $key }}]">@foreach (['featured' => 'محصولات منتخب', 'new' => 'جدیدترین', 'popular' => 'پربازدیدترین', 'category' => 'از یک دسته'] as $k => $v)<option value="{{ $k }}" @selected($val === $k)>{{ $v }}</option>@endforeach</select></div> @break
                    @default <div class="a-field"><label>{{ ['slider_placement' => 'جایگاه اسلایدر', 'group' => 'گروه', 'placement' => 'جایگاه بنر', 'video_url' => 'لینک ویدیو'][$key] ?? $key }}</label><input name="settings[{{ $key }}]" value="{{ $val }}" dir="ltr"></div>
                @endswitch
            @empty
                <p class="a-muted">این سکشن تنظیم اضافه ندارد؛ داده‌هایش خودکار از بخش مربوطه خوانده می‌شود.</p>
            @endforelse
        </section></aside>
    </div>
</form>
@endsection
