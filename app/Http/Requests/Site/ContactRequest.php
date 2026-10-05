<?php

namespace App\Http\Requests\Site;

use App\Support\TextNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('mobile')) {
            $this->merge(['mobile' => preg_replace('/\D+/', '', TextNormalizer::normalize($this->input('mobile')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['nullable', 'required_without:email', 'regex:/^(\+?98|0)?9\d{9}$|^\+?\d{8,15}$/'],
            'email' => ['nullable', 'required_without:mobile', 'email:rfc', 'max:191'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function validated($key = null, $default = null)
    {
        return collect(parent::validated())->except('website')->all();
    }

    public function attributes(): array
    {
        return [
            'name' => __('ui.form.name'), 'mobile' => __('ui.form.mobile'), 'email' => __('ui.form.email'),
            'subject' => __('ui.form.subject'), 'message' => __('ui.form.message'),
        ];
    }
}
