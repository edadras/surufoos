<?php

namespace App\Enums;

enum ProductImageType: string
{
    case Gallery = 'gallery';
    case Lifestyle = 'lifestyle';
    case Packshot = 'packshot';
    case Ingredient = 'ingredient';

    public function label(): string
    {
        return match ($this) {
            self::Gallery => 'گالری',
            self::Lifestyle => 'سبک زندگی',
            self::Packshot => 'عکس بسته (بدون پس‌زمینه)',
            self::Ingredient => 'مواد اولیه',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
