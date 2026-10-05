{{-- Multiple media picker (gallery). Sortable thumbnails with hidden inputs. --}}
@php($items = \App\Models\Media::whereIn('id', $values ?? [])->get()->keyBy('id'))
<div class="a-field" data-media-multi data-name="{{ $name }}">
    <label>{{ $label }}</label>
    <ul class="a-thumbs" data-sortable>
        @foreach ($values ?? [] as $mid)
            @if ($m = $items->get($mid))
                <li class="a-thumb" data-id="{{ $m->id }}">
                    <img src="{{ $m->variantUrl('thumbnail') }}" alt="">
                    <input type="hidden" name="{{ $name }}[]" value="{{ $m->id }}">
                    <button type="button" class="a-thumb__x" data-thumb-remove aria-label="حذف">×</button>
                </li>
            @endif
        @endforeach
    </ul>
    <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-media-add>افزودن تصویر</button>
    @isset($hint)<small class="a-hint">{{ $hint }}</small>@endisset
</div>
