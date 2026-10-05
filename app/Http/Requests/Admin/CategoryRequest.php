<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use Illuminate\Validation\Rule;

class CategoryRequest extends TranslatableRequest
{
    protected function translationRules(): array
    {
        return [
            'title' => ['string', 'max:191'],
            'slug' => [],
            'short_description' => ['string', 'max:1000'],
            'description' => ['string', 'max:50000'],
            'seo_title' => ['string', 'max:191'],
            'seo_description' => ['string', 'max:500'],
        ];
    }

    public function rules(): array
    {
        return $this->translationValidation() + [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$this->route('category')?->id])],
            'image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'icon_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'theme_color' => ['nullable', 'hex_color'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'show_in_menu' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['show_in_menu' => $this->boolean('show_in_menu')]);
    }
}
