<?php

namespace App\Enums;

enum StockStatus: string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case Preorder = 'preorder';
    case Discontinued = 'discontinued';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'موجود',
            self::OutOfStock => 'ناموجود',
            self::Preorder => 'پیش‌سفارش',
            self::Discontinued => 'توقف تولید',
        };
    }

    public function isPurchasable(): bool
    {
        return in_array($this, [self::InStock, self::Preorder], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
