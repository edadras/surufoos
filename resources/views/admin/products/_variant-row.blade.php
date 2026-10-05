<div class="a-repeater__row a-repeater__row--wrap" data-repeater-row>
    <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
    <input name="variants[{{ $i }}][sku]" value="{{ $row['sku'] ?? '' }}" placeholder="SKU" dir="ltr" class="a-sm">
    <input name="variants[{{ $i }}][price]" value="{{ $row['price'] ?? '' }}" placeholder="قیمت" inputmode="numeric" class="a-sm">
    <input name="variants[{{ $i }}][sale_price]" value="{{ $row['sale_price'] ?? '' }}" placeholder="قیمت تخفیف" inputmode="numeric" class="a-sm">
    <input name="variants[{{ $i }}][stock_qty]" value="{{ $row['stock_qty'] ?? '' }}" placeholder="تعداد" inputmode="numeric" class="a-xs">
    <select name="variants[{{ $i }}][stock_status]">@foreach (\App\Enums\StockStatus::options() as $k => $v)<option value="{{ $k }}" @selected(($row['stock_status'] ?? 'in_stock') === $k)>{{ $v }}</option>@endforeach</select>
    <input name="variants[{{ $i }}][net_weight]" value="{{ $row['net_weight'] ?? '' }}" placeholder="وزن" inputmode="decimal" class="a-xs">
    <select name="variants[{{ $i }}][weight_unit]"><option value=""></option>@foreach (['g', 'kg', 'ml', 'l', 'pcs'] as $u)<option value="{{ $u }}" @selected(($row['weight_unit'] ?? '') === $u)>{{ __('units.'.$u, [], 'fa') }}</option>@endforeach</select>
    <select name="variants[{{ $i }}][attribute_values][]" multiple size="2" class="a-multi">@foreach ($allValues as $v)<option value="{{ $v['id'] }}" @selected(in_array($v['id'], $row['attribute_values'] ?? []))>{{ $v['label'] }}</option>@endforeach</select>
    <label class="a-check"><input type="checkbox" name="variants[{{ $i }}][is_default]" value="1" @checked(! empty($row['is_default']))> پیش‌فرض</label>
    <button type="button" class="a-icon-btn" data-repeater-remove aria-label="حذف ردیف">×</button>
</div>
