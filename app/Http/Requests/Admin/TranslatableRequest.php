<?php

namespace App\Http\Requests\Admin;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base for admin forms with FA/EN/AR translation tabs. The Persian (source)
 * primary field is required; other locales are optional but validated when present.
 */
abstract class TranslatableRequest extends FormRequest
{
    protected string $primaryField = 'title';

    /** @return array<string, array> field => rules (applied per locale) */
    abstract protected function translationRules(): array;

    public function authorize(): bool
    {
        return true;
    }

    protected function translationValidation(): array
    {
        $rules = ['translations' => ['required', 'array']];
        foreach (Locales::codes() as $locale) {
            foreach ($this->translationRules() as $field => $fieldRules) {
                $rules["translations.$locale.$field"] = $field === $this->primaryField && $locale === Locales::source()
                    ? array_merge(['required'], $fieldRules)
                    : array_merge(['nullable'], $fieldRules);
            }
            if (array_key_exists('slug', $this->translationRules())) {
                $rules["translations.$locale.slug"] = ['nullable', 'string', 'max:150', 'regex:/^[\p{L}\p{N}\-]+$/u'];
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $names = ['fa' => 'فارسی', 'en' => 'انگلیسی', 'ar' => 'عربی'];
        $labels = ['title' => 'عنوان', 'slug' => 'نامک', 'name' => 'نام', 'short_description' => 'توضیح کوتاه', 'description' => 'توضیحات',
            'seo_title' => 'عنوان سئو', 'seo_description' => 'توضیح سئو', 'value' => 'مقدار', 'label' => 'برچسب', 'question' => 'پرسش', 'answer' => 'پاسخ'];
        $out = [];
        foreach (Locales::codes() as $l) {
            foreach ($labels as $f => $label) {
                $out["translations.$l.$f"] = "$label ({$names[$l]})";
            }
        }

        return $out;
    }

    public function translations(): array
    {
        return $this->validated('translations') ?? [];
    }
}
