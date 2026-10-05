<div class="a-repeater__row a-repeater__row--wrap" data-repeater-row>
    <span class="a-drag" aria-hidden="true">⋮⋮</span>
    <textarea name="steps[{{ $l }}][{{ $i }}][body]" rows="2" placeholder="شرح مرحله" required>{{ $row['body'] ?? '' }}</textarea>
    <input name="steps[{{ $l }}][{{ $i }}][duration_minutes]" value="{{ $row['duration_minutes'] ?? '' }}" placeholder="دقیقه" class="a-xs" inputmode="numeric">
    <input name="steps[{{ $l }}][{{ $i }}][media_id]" value="{{ $row['media_id'] ?? '' }}" placeholder="شناسه تصویر" class="a-xs" inputmode="numeric" title="شناسه تصویر از کتابخانه رسانه">
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف">×</button>
</div>
