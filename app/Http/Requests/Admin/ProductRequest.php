<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Enums\Nutrient;
use App\Enums\StockStatus;
use App\Support\TextNormalizer;
use Illuminate\Validation\Rule;

class ProductRequest extends TranslatableRequest
{
    protected function translationRules(): array
    {
        return [
            'title' => ['string', 'max:191'],
            'slug' => [],
            'short_description' => ['string', 'max:2000'],
            'description' => ['string', 'max:100000'],
            'story' => ['string', 'max:100000'],
            'features' => ['string', 'max:5000'],
            'how_to_use' => ['string', 'max:5000'],
            'storage' => ['string', 'max:3000'],
            'serving_suggestion' => ['string', 'max:5000'],
            'badge_label' => ['string', 'max:64'],
            'seo_title' => ['string', 'max:191'],
            'seo_description' => ['string', 'max:500'],
        ];
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return $this->translationValidation() + [
            'sku' => ['nullable', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($id)],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'price' => ['nullable', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lte:price'],
            'stock_status' => ['required', Rule::enum(StockStatus::class)],
            'stock_qty' => ['nullable', 'integer'],
            'net_weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', Rule::in(['g', 'kg', 'ml', 'l', 'pcs'])],
            'theme_color' => ['nullable', 'hex_color'],
            'badge' => ['nullable', Rule::in(['new', 'sale', 'bestseller', 'organic', 'limited'])],
            'is_featured' => ['boolean'],
            'is_new' => ['boolean'],
            'show_inside' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'main_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'packshot_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['integer', 'exists:media,id'],
            'lifestyle' => ['nullable', 'array'],
            'lifestyle.*' => ['integer', 'exists:media,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'attribute_values' => ['nullable', 'array'],
            'attribute_values.*' => ['integer', 'exists:attribute_values,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'ingredients.*.amount' => ['nullable', 'string', 'max:32'],
            'ingredients.*.position_x' => ['nullable', 'numeric', 'between:0,100'],
            'ingredients.*.position_y' => ['nullable', 'numeric', 'between:0,100'],
            'ingredients.*.is_highlight' => ['nullable', 'boolean'],
            'nutrition' => ['nullable', 'array'],
            'nutrition.*.nutrient' => ['required', Rule::enum(Nutrient::class)],
            'nutrition.*.amount' => ['required', 'numeric', 'min:0'],
            'nutrition.*.unit' => ['required', Rule::in(['g', 'mg', 'kcal', 'ml'])],
            'nutrition.*.per' => ['required', 'string', 'max:32'],
            'nutrition.*.daily_value_percent' => ['nullable', 'numeric', 'between:0,1000'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['nullable', 'string', 'max:64'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_qty' => ['nullable', 'integer'],
            'variants.*.stock_status' => ['nullable', Rule::enum(StockStatus::class)],
            'variants.*.net_weight' => ['nullable', 'numeric', 'min:0'],
            'variants.*.weight_unit' => ['nullable', Rule::in(['g', 'kg', 'ml', 'l', 'pcs'])],
            'variants.*.is_default' => ['nullable', 'boolean'],
            'variants.*.attribute_values' => ['nullable', 'array'],
            'variants.*.attribute_values.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_new' => $this->boolean('is_new'),
            'show_inside' => $this->boolean('show_inside'),
        ]);
        foreach (['price', 'sale_price'] as $f) {
            if ($this->filled($f)) {
                $this->merge([$f => (int) preg_replace('/\D/', '', TextNormalizer::normalize((string) $this->input($f)))]);
            }
        }
    }
}
