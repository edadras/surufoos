<div class="a-repeater__row a-repeater__row--wrap" data-repeater-row>
    <div class="a-media-inline" data-media-field>
        <input type="hidden" name="layers[{{ $i }}][media_id]" value="{{ $layer['media_id'] ?? '' }}" data-media-input>
        <span class="a-media-inline__preview" data-media-preview>@if(! empty($layer['media_id']) && ($m = \App\Models\Media::find($layer['media_id'])))<img src="{{ $m->variantUrl('thumbnail') }}" alt="">@endif</span>
        <button type="button" class="a-btn a-btn--ghost a-btn--sm" data-media-choose>تصویر</button>
    </div>
    <label>X <input name="layers[{{ $i }}][x]" value="{{ $layer['x'] ?? 20 }}" class="a-xs" inputmode="decimal"></label>
    <label>Y <input name="layers[{{ $i }}][y]" value="{{ $layer['y'] ?? 30 }}" class="a-xs" inputmode="decimal"></label>
    <label>عمق <input name="layers[{{ $i }}][depth]" value="{{ $layer['depth'] ?? 0.3 }}" class="a-xs" inputmode="decimal"></label>
    <label>اندازه <input name="layers[{{ $i }}][scale]" value="{{ $layer['scale'] ?? 1 }}" class="a-xs" inputmode="decimal"></label>
    <label>چرخش <input name="layers[{{ $i }}][rotate]" value="{{ $layer['rotate'] ?? 0 }}" class="a-xs" inputmode="decimal"></label>
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف">×</button>
</div>
