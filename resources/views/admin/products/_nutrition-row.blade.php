<div class="a-repeater__row" data-repeater-row>
    <span class="a-drag" aria-hidden="true">⋮⋮</span>
    <select name="nutrition[{{ $i }}][nutrient]" required>
        @foreach (\App\Enums\Nutrient::options() as $k => $v)<option value="{{ $k }}" @selected(($row['nutrient'] ?? null) === $k)>{{ $v }}</option>@endforeach
    </select>
    <input name="nutrition[{{ $i }}][amount]" value="{{ $row['amount'] ?? '' }}" placeholder="مقدار" inputmode="decimal" required class="a-sm">
    <select name="nutrition[{{ $i }}][unit]">@foreach (['g' => 'گرم', 'mg' => 'میلی‌گرم', 'kcal' => 'کیلوکالری', 'ml' => 'میلی‌لیتر'] as $k => $v)<option value="{{ $k }}" @selected(($row['unit'] ?? 'g') === $k)>{{ $v }}</option>@endforeach</select>
    <input name="nutrition[{{ $i }}][per]" value="{{ $row['per'] ?? '100g' }}" placeholder="به ازای" class="a-sm" dir="ltr">
    <input name="nutrition[{{ $i }}][daily_value_percent]" value="{{ $row['daily_value_percent'] ?? '' }}" placeholder="٪ نیاز روزانه" class="a-sm" inputmode="decimal">
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف ردیف">×</button>
</div>
