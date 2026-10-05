@extends('admin.layout')
@section('title', 'سئو')
@section('content')
<form method="post" action="{{ route('admin.seo.update') }}">
    @csrf @method('PUT')
    <div class="a-head"><h1>سئوی صفحات ثابت</h1><button class="a-btn">ذخیره</button></div>
    <p class="a-muted">عنوان و توضیح محصولات، دسته‌ها، مقالات و دستورها در فرم خود آن‌ها تنظیم می‌شود. Sitemap در <a href="{{ route('sitemap.index') }}" target="_blank" dir="ltr">/sitemap.xml</a> و robots در <a href="{{ route('robots') }}" target="_blank" dir="ltr">/robots.txt</a> خودکار ساخته می‌شوند.</p>
    @foreach ($routes as $route => $label)
        @php($key = str_replace('.', '_', $route))
        <details class="a-card a-collapse" @if($loop->first) open @endif>
            <summary><strong>{{ $label }}</strong> <small dir="ltr">{{ $route }}</small></summary>
            <div class="a-tr-columns">
                @foreach (config('suru.locales') as $code => $meta)
                    @php($row = $rows[$route][$code] ?? null)
                    <fieldset class="a-fieldset" dir="{{ $meta['dir'] }}"><legend>{{ $meta['native'] }}</legend>
                        <div class="a-field"><label>عنوان</label><input name="meta[{{ $key }}][{{ $code }}][title]" value="{{ old("meta.$key.$code.title", $row?->title) }}" maxlength="70"></div>
                        <div class="a-field"><label>توضیح</label><textarea name="meta[{{ $key }}][{{ $code }}][description]" rows="2" maxlength="300">{{ old("meta.$key.$code.description", $row?->description) }}</textarea></div>
                        <div class="a-field"><label>شناسه تصویر OG</label><input name="meta[{{ $key }}][{{ $code }}][og_media_id]" value="{{ old("meta.$key.$code.og_media_id", $row?->og_media_id) }}" inputmode="numeric" class="a-sm"></div>
                        <label class="a-check"><input type="checkbox" name="meta[{{ $key }}][{{ $code }}][noindex]" value="1" @checked($row?->noindex)> noindex</label>
                    </fieldset>
                @endforeach
            </div>
        </details>
    @endforeach
</form>
@endsection
