<?php

namespace App\Http\Requests\Admin;

class IngredientRequest extends TranslatableRequest
{
    protected string $primaryField = 'name';

    protected function translationRules(): array
    {
        return ['name' => ['string', 'max:191'], 'description' => ['string', 'max:2000']];
    }

    public function rules(): array
    {
        return $this->translationValidation() + [
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'color' => ['nullable', 'hex_color'],
        ];
    }
}
