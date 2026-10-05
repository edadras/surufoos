{{-- Single media picker: hidden input + preview + choose/clear. --}}
@php
    $current = $value ? \App\Models\Media::find($value) : null;
    $id = $id ?? 'm-'.str_replace(['[', ']', '.'], '-', $name);
@endphp
<div class="a-field a-media-field" data-media-field>
    <label>{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" id="{{ $id }}" data-media-input>
    <div class="a-media-field__preview" data-media-preview>
        @if ($current)<img src="{{ $current->variantUrl('thumbnail') }}" alt="">@else<span>بدون تصویر</span>@endif
    </div>
    <div class="a-media-field__actions">
        <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-media-choose>انتخاب تصویر</button>
        <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-media-clear @unless($current) hidden @endunless>حذف</button>
    </div>
    @isset($hint)<small class="a-hint">{{ $hint }}</small>@endisset
</div>
