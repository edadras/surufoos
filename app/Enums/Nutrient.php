<?php

namespace App\Enums;

enum Nutrient: string
{
    case Energy = 'energy';
    case Protein = 'protein';
    case Carbohydrate = 'carbohydrate';
    case Sugar = 'sugar';
    case Fat = 'fat';
    case SaturatedFat = 'saturated_fat';
    case TransFat = 'trans_fat';
    case Fiber = 'fiber';
    case Salt = 'salt';
    case Sodium = 'sodium';
    case Cholesterol = 'cholesterol';
    case Calcium = 'calcium';
    case Iron = 'iron';

    public function label(): string
    {
        return __('nutrients.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => __('nutrients.'.$c->value, [], 'fa')])->all();
    }
}
