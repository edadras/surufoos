<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use Illuminate\Validation\Rule;

class PostRequest extends TranslatableRequest
{
    protected function translationRules(): array
    {
        return [
            'title' => ['string', 'max:191'], 'slug' => [], 'short_description' => ['string', 'max:2000'],
            'description' => ['string', 'max:300000'], 'seo_title' => ['string', 'max:191'], 'seo_description' => ['string', 'max:500'],
        ];
    }

    public function rules(): array
    {
        return $this->translationValidation() + [
            'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'author_name' => ['nullable', 'string', 'max:191'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'is_featured' => ['boolean'],
            'reading_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'published_at' => ['nullable', 'date'],
            'gallery' => ['nullable', 'array'], 'gallery.*' => ['integer', 'exists:media,id'],
            'products' => ['nullable', 'array'], 'products.*' => ['integer', 'exists:products,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured')]);
    }
}
