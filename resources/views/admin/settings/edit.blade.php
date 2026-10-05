@extends('admin.layout')
@section('title', 'تنظیمات')
@php($def = $groups[$group])
@section('content')
<div class="a-tabs">@foreach ($groups as $key => $g)<a href="{{ route('admin.settings.edit', $key) }}" class="{{ $key === $group ? 'is-active' : '' }}">{{ $g['label'] }}</a>@endforeach</div>
<form method="post" action="{{ route('admin.settings.update', $group) }}" class="a-card">
    @csrf @method('PUT')
    <div class="a-head"><h1>{{ $def['label'] }}</h1><button class="a-btn">ذخیره</button></div>
    @if ($group === 'theme')
        <div class="a-alert a-alert--info">رنگ‌های خالی از مقدار پیش‌فرض طراحی استفاده می‌کنند. برای ساخت پالت از روی لوگوی واقعی سورو دکمه «استخراج از لوگو» را بزنید؛ رنگ هر محصول نیز خودکار از عکس بسته‌بندی آن گرفته می‌شود.</div>
    @endif
    <div class="a-settings">
        @foreach ($def['fields'] as $key => $field)
            @if ($field['translatable'] ?? false)
                <fieldset class="a-fieldset"><legend>{{ $field['label'] }}</legend>
                    <div class="a-row">
                        @foreach (config('suru.locales') as $code => $meta)
                            <div class="a-field" dir="{{ $meta['dir'] }}"><label>{{ $meta['native'] }}</label>
                                @if ($field['type'] === 'textarea')<textarea name="{{ $key }}[{{ $code }}]" rows="3">{{ old("$key.$code", $settings->get($key, null, $code)) }}</textarea>
                                @else<input name="{{ $key }}[{{ $code }}]" value="{{ old("$key.$code", $settings->get($key, null, $code)) }}">@endif
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            @else
                @php($val = old($key, $settings->get($key)))
                @switch($field['type'])
                    @case('media') @include('admin.partials.media-field', ['name' => $key, 'label' => $field['label'], 'value' => $val]) @break
                    @case('boolean') <label class="a-check"><input type="checkbox" name="{{ $key }}" value="1" @checked(filter_var($val, FILTER_VALIDATE_BOOLEAN))> {{ $field['label'] }}</label> @break
                    @case('color') <div class="a-field"><label>{{ $field['label'] }}</label><div class="a-color"><input type="color" value="{{ $val ?: '#ffffff' }}" data-color-sync="s-{{ $key }}"><input id="s-{{ $key }}" name="{{ $key }}" value="{{ $val }}" dir="ltr" placeholder="پیش‌فرض"></div></div> @break
                    @case('textarea') @case('code') <div class="a-field"><label>{{ $field['label'] }}</label><textarea name="{{ $key }}" rows="{{ $field['type'] === 'code' ? 5 : 3 }}" @if($field['type'] === 'code') dir="ltr" class="a-code" @endif>{{ $val }}</textarea></div> @break
                    @case('checkboxes') <fieldset class="a-fieldset"><legend>{{ $field['label'] }}</legend>@foreach ($field['options'] as $ok => $ol)<label class="a-check"><input type="checkbox" name="{{ $key }}[]" value="{{ $ok }}" @checked(in_array($ok, $settings->json($key)))> {{ $ol }}</label>@endforeach</fieldset> @break
                    @default <div class="a-field"><label>{{ $field['label'] }}</label><input name="{{ $key }}" value="{{ $val }}" @if(in_array($field['type'], ['url', 'number'])) dir="ltr" @endif @if($field['type'] === 'number') inputmode="numeric" @endif></div>
                @endswitch
            @endif
        @endforeach
    </div>
</form>
@if ($group === 'theme')
    <form method="post" action="{{ route('admin.settings.extract-palette') }}" class="a-card">@csrf
        <h2>استخراج پالت از تصویر برند</h2>
        <p class="a-muted">به‌صورت پیش‌فرض از لوگو؛ می‌توانید شناسه تصویر دیگری (مثلاً عکس بسته‌بندی شاخص) وارد کنید.</p>
        <div class="a-filters"><input name="media_id" inputmode="numeric" placeholder="شناسه تصویر (اختیاری)" class="a-sm"><button class="a-btn a-btn--sm">استخراج از لوگو / تصویر</button></div>
    </form>
@endif
@endsection
