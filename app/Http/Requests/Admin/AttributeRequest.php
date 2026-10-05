<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class AttributeRequest extends TranslatableRequest
{
    protected string $primaryField = 'name';

    protected function translationRules(): array
    {
        return ['name' => ['string', 'max:191'], 'quiz_question' => ['string', 'max:191']];
    }

    public function rules(): array
    {
        return $this->translationValidation() + [
            'code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_-]+$/', Rule::unique('attributes', 'code')->ignore($this->route('attribute')?->id)],
            'type' => ['required', Rule::in(['select', 'multi', 'color'])],
            'is_filterable' => ['boolean'],
            'is_quiz' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'values' => ['nullable', 'array'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.code' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_-]+$/', 'distinct'],
            'values.*.color' => ['nullable', 'hex_color'],
            'values.*.fa' => ['required', 'string', 'max:191'],
            'values.*.en' => ['nullable', 'string', 'max:191'],
            'values.*.ar' => ['nullable', 'string', 'max:191'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_filterable' => $this->boolean('is_filterable'), 'is_quiz' => $this->boolean('is_quiz')]);
    }

    public function attributes(): array
    {
        return parent::attributes() + ['values.*.code' => 'کد مقدار', 'values.*.fa' => 'مقدار فارسی', 'code' => 'کد'];
    }
}
