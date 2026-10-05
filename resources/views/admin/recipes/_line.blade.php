<div class="a-repeater__row" data-repeater-row>
    <span class="a-drag" aria-hidden="true">⋮⋮</span>
    <input name="lines[{{ $l }}][{{ $i }}][quantity]" value="{{ $row['quantity'] ?? '' }}" placeholder="مقدار" class="a-sm">
    <input name="lines[{{ $l }}][{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" placeholder="نام ماده" required>
    <input name="lines[{{ $l }}][{{ $i }}][note]" value="{{ $row['note'] ?? '' }}" placeholder="توضیح">
    <select name="lines[{{ $l }}][{{ $i }}][product_id]" aria-label="محصول سورو"><option value="">محصول سورو؟</option>@foreach ($products as $p)<option value="{{ $p->id }}" @selected(($row['product_id'] ?? null) == $p->id)>{{ $p->tr('title', 'fa', true) }}</option>@endforeach</select>
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف">×</button>
</div>
