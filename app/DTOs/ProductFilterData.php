<?php

namespace App\DTOs;

use Illuminate\Http\Request;

/** Validated, normalised product listing filters (all shareable via the query string). */
final class ProductFilterData
{
    public const SORTS = ['featured', 'newest', 'popular', 'price_asc', 'price_desc', 'title'];

    /**
     * @param  array<string, array<int, string>>  $attributes  attribute code => value codes
     */
    public function __construct(
        public readonly ?string $query = null,
        public readonly ?string $categorySlug = null,
        public readonly ?int $categoryId = null,
        public readonly ?int $priceMin = null,
        public readonly ?int $priceMax = null,
        public readonly array $attributes = [],
        public readonly bool $inStockOnly = false,
        public readonly string $sort = 'featured',
        public readonly int $perPage = 12,
    ) {}

    public static function fromRequest(Request $request, ?int $categoryId = null): self
    {
        $attributes = [];
        foreach ((array) $request->query('attr', []) as $code => $values) {
            if (! is_string($code) || ! preg_match('/^[a-z0-9_-]{1,64}$/', $code)) {
                continue;
            }
            $values = is_array($values) ? $values : explode(',', (string) $values);
            $values = array_values(array_filter($values, fn ($v) => is_string($v) && preg_match('/^[a-z0-9_-]{1,64}$/', $v)));
            if ($values) {
                $attributes[$code] = $values;
            }
        }

        $sort = (string) $request->query('sort', 'featured');
        $min = $request->query('min');
        $max = $request->query('max');

        return new self(
            query: filled($request->query('q')) ? mb_substr(trim((string) $request->query('q')), 0, 100) : null,
            categorySlug: is_string($request->query('category')) ? $request->query('category') : null,
            categoryId: $categoryId,
            priceMin: is_numeric($min) ? max(0, (int) $min) : null,
            priceMax: is_numeric($max) ? max(0, (int) $max) : null,
            attributes: $attributes,
            inStockOnly: $request->boolean('in_stock'),
            sort: in_array($sort, self::SORTS, true) ? $sort : 'featured',
            perPage: 12,
        );
    }

    public function isFiltered(): bool
    {
        return $this->query || $this->categorySlug || $this->priceMin !== null || $this->priceMax !== null || $this->attributes || $this->inStockOnly;
    }
}
