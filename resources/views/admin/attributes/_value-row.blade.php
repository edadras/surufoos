<div class="a-repeater__row" data-repeater-row>
    <span class="a-drag" aria-hidden="true">⋮⋮</span>
    <input type="hidden" name="values[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
    <input name="values[{{ $i }}][fa]" value="{{ $row['fa'] ?? '' }}" placeholder="فارسی" required>
    <input name="values[{{ $i }}][en]" value="{{ $row['en'] ?? '' }}" placeholder="English" dir="ltr">
    <input name="values[{{ $i }}][ar]" value="{{ $row['ar'] ?? '' }}" placeholder="العربية">
    <input name="values[{{ $i }}][code]" value="{{ $row['code'] ?? '' }}" placeholder="code" dir="ltr" class="a-sm" required pattern="[a-z0-9_-]+">
    <input type="color" name="values[{{ $i }}][color]" value="{{ $row['color'] ?? '#e3a43a' }}" class="a-swatch" aria-label="رنگ">
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف">×</button>
</div>
