{{--
    Translatable field group: one tab per locale with completion status and a
    "copy from Persian" action. $fields = [name => [label, type('text'|'textarea'|'rich'|'slug'), required(bool)]].
    $model may be null (create) — values then come from old() only.
--}}
@php
    $locales = config('suru.locales');
    $prefix = $prefix ?? 'translations';
    $primary = $primary ?? array_key_first($fields);
@endphp
<div class="a-tr" data-tr>
    <div class="a-tr__tabs" role="tablist">
        @foreach ($locales as $code => $meta)
            @php
                $value = old("$prefix.$code.$primary", $model?->translation($code, false)?->{$primary});
                $done = filled($value);
            @endphp
            <button type="button" role="tab" class="a-tr__tab {{ $loop->first ? 'is-active' : '' }}" data-tr-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                {{ $meta['short'] }} <span class="a-tr__state {{ $done ? 'is-done' : 'is-missing' }}">{{ $done ? '✓' : '!' }}</span>
            </button>
        @endforeach
    </div>
    @foreach ($locales as $code => $meta)
        @php $tr = $model?->translation($code, false); @endphp
        <div class="a-tr__pane" data-tr-pane="{{ $code }}" @unless($loop->first) hidden @endunless lang="{{ $code }}" dir="{{ $meta['dir'] }}">
            @if ($code !== 'fa')
                <button type="button" class="a-btn a-btn--ghost a-btn--sm a-tr__copy" data-tr-copy="{{ $code }}">کپی از فارسی</button>
            @endif
            @foreach ($fields as $name => $def)
                @php
                    [$label, $type, $required] = array_pad($def, 3, false);
                    $fieldName = "{$prefix}[{$code}][{$name}]";
                    $val = old("$prefix.$code.$name", $tr?->{$name});
                    $id = "tr-{$prefix}-{$code}-{$name}";
                @endphp
                <div class="a-field">
                    <label for="{{ $id }}">{{ $label }} @if($required && $code === 'fa')<b class="a-req">*</b>@endif</label>
                    @if ($type === 'textarea')
                        <textarea id="{{ $id }}" name="{{ $fieldName }}" rows="3" data-tr-field="{{ $name }}">{{ $val }}</textarea>
                    @elseif ($type === 'rich')
                        <textarea id="{{ $id }}" name="{{ $fieldName }}" rows="10" class="a-rich" data-tr-field="{{ $name }}" data-rich>{{ $val }}</textarea>
                        <small class="a-hint">HTML مجاز است (تیتر، پاراگراف، لیست، لینک، تصویر). کدهای اجرایی حذف می‌شوند.</small>
                    @elseif ($type === 'slug')
                        <input id="{{ $id }}" name="{{ $fieldName }}" value="{{ $val }}" dir="auto" data-tr-field="{{ $name }}" data-slug>
                        <small class="a-hint">اگر خالی بماند از عنوان ساخته می‌شود. نامک هر زبان مستقل است.</small>
                    @else
                        <input id="{{ $id }}" name="{{ $fieldName }}" value="{{ $val }}" data-tr-field="{{ $name }}" @if($name === 'seo_title') maxlength="70" @endif>
                    @endif
                    @if ($name === 'seo_description')<small class="a-hint">حداکثر ۱۶۰ نویسه توصیه می‌شود.</small>@endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
