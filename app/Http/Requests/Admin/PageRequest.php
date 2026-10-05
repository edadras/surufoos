<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use Illuminate\Validation\Rule;

class PageRequest extends TranslatableRequest
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
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'cover_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
