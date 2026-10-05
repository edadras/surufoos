<?php

namespace App\Http\Requests\Site;

use App\Support\TextNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['customer_mobile', 'postal_code'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => preg_replace('/\D+/', '', TextNormalizer::normalize($this->input($field)))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_mobile' => ['required', 'regex:/^(98|0)?9\d{9}$|^\d{8,15}$/'],
            'customer_email' => ['nullable', 'email:rfc', 'max:191'],
            'province' => ['required', 'string', 'max:64'],
            'city' => ['required', 'string', 'max:64'],
            'address' => ['required', 'string', 'max:1000'],
            'postal_code' => ['nullable', 'digits_between:5,10'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(settings()->json('payment_methods', ['cod']))],
        ];
    }

    public function attributes(): array
    {
        return collect(['customer_name' => 'name', 'customer_mobile' => 'mobile', 'customer_email' => 'email', 'province' => 'province',
            'city' => 'city', 'address' => 'address', 'postal_code' => 'postal_code', 'payment_method' => 'payment_method'])
            ->map(fn ($k) => __('ui.form.'.$k))->all();
    }
}
