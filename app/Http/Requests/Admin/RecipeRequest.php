<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Enums\Difficulty;
use App\Support\Locales;
use Illuminate\Validation\Rule;

class RecipeRequest extends TranslatableRequest
{
    protected function translationRules(): array
    {
        return [
            'title' => ['string', 'max:191'], 'slug' => [], 'short_description' => ['string', 'max:2000'], 'description' => ['string', 'max:100000'],
            'tips' => ['string', 'max:5000'], 'seo_title' => ['string', 'max:191'], 'seo_description' => ['string', 'max:500'],
        ];
    }

    public function rules(): array
    {
        $rules = $this->translationValidation() + [
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'prep_minutes' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'cook_minutes' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'servings' => ['nullable', 'integer', 'min:1', 'max:200'],
            'difficulty' => ['nullable', Rule::enum(Difficulty::class)],
            'nutrition' => ['nullable', 'array'],
            'nutrition.*' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'is_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'products' => ['nullable', 'array'], 'products.*' => ['integer', 'exists:products,id'],
            'gallery' => ['nullable', 'array'], 'gallery.*' => ['integer', 'exists:media,id'],
        ];
        foreach (Locales::codes() as $l) {
            $rules["lines.$l"] = ['nullable', 'array'];
            $rules["lines.$l.*.name"] = ['required', 'string', 'max:191'];
            $rules["lines.$l.*.quantity"] = ['nullable', 'string', 'max:64'];
            $rules["lines.$l.*.note"] = ['nullable', 'string', 'max:191'];
            $rules["lines.$l.*.product_id"] = ['nullable', 'integer', 'exists:products,id'];
            $rules["steps.$l"] = ['nullable', 'array'];
            $rules["steps.$l.*.body"] = ['required', 'string', 'max:5000'];
            $rules["steps.$l.*.media_id"] = ['nullable', 'integer', 'exists:media,id'];
            $rules["steps.$l.*.duration_minutes"] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured')]);
    }
}
