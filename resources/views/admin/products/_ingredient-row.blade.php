<div class="a-repeater__row" data-repeater-row>
    <span class="a-drag" aria-hidden="true">⋮⋮</span>
    <select name="ingredients[{{ $i }}][ingredient_id]" required>
        <option value="">انتخاب ماده اولیه</option>
        @foreach ($ingredients as $ing)<option value="{{ $ing->id }}" @selected(($row['ingredient_id'] ?? null) == $ing->id)>{{ $ing->tr('name', 'fa', true) }}</option>@endforeach
    </select>
    <input name="ingredients[{{ $i }}][amount]" value="{{ $row['amount'] ?? '' }}" placeholder="مقدار / درصد">
    <input name="ingredients[{{ $i }}][position_x]" value="{{ $row['position_x'] ?? '' }}" placeholder="X" class="a-xs" inputmode="decimal">
    <input name="ingredients[{{ $i }}][position_y]" value="{{ $row['position_y'] ?? '' }}" placeholder="Y" class="a-xs" inputmode="decimal">
    <label class="a-check"><input type="checkbox" name="ingredients[{{ $i }}][is_highlight]" value="1" @checked(! empty($row['is_highlight']))> شاخص</label>
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف ردیف">×</button>
</div>
