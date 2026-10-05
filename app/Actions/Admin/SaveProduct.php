<?php

namespace App\Actions\Admin;

use App\Enums\ProductImageType;
use App\Models\Media;
use App\Models\Product;
use App\Models\Tag;
use App\Support\TextNormalizer;
use Illuminate\Support\Facades\DB;

/** Persists a product and all of its relations from a validated admin form. */
class SaveProduct
{
    public function execute(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->fill(collect($data)->only([
                'sku', 'status', 'published_at', 'price', 'sale_price', 'stock_status', 'stock_qty', 'net_weight', 'weight_unit',
                'theme_color', 'badge', 'is_featured', 'is_new', 'show_inside', 'main_media_id', 'packshot_media_id', 'primary_category_id',
            ])->all());
            $product->sort_order = (int) ($data['sort_order'] ?? 0);
            if ($product->status->value === 'published' && ! $product->published_at) {
                $product->published_at = now();
            }
            // Default theme colour comes from the real packshot when the editor left it empty.
            if (! $product->theme_color && ($mid = $product->packshot_media_id ?: $product->main_media_id)) {
                $product->theme_color = Media::find($mid)?->dominant_color;
            }
            $product->save();
            $product->saveTranslations($data['translations'] ?? []);

            $categories = $data['categories'] ?? [];
            if ($product->primary_category_id && ! in_array($product->primary_category_id, $categories)) {
                $categories[] = $product->primary_category_id;
            }
            $product->categories()->sync($categories);
            if (! $product->primary_category_id && $categories) {
                $product->update(['primary_category_id' => $categories[0]]);
            }
            $product->attributeValues()->sync($data['attribute_values'] ?? []);

            $product->images()->delete();
            foreach ([ProductImageType::Gallery->value => 'gallery', ProductImageType::Lifestyle->value => 'lifestyle'] as $type => $key) {
                foreach (array_values($data[$key] ?? []) as $i => $mediaId) {
                    $product->images()->create(['media_id' => $mediaId, 'type' => $type, 'sort_order' => $i]);
                }
            }

            $ingredients = [];
            foreach (array_values($data['ingredients'] ?? []) as $i => $row) {
                $ingredients[$row['ingredient_id']] = [
                    'amount' => $row['amount'] ?? null,
                    'position_x' => $row['position_x'] ?? null,
                    'position_y' => $row['position_y'] ?? null,
                    'is_highlight' => (bool) ($row['is_highlight'] ?? false),
                    'sort_order' => $i,
                ];
            }
            $product->ingredients()->sync($ingredients);

            $product->nutritionFacts()->delete();
            $seen = [];
            foreach (array_values($data['nutrition'] ?? []) as $i => $row) {
                $key = $row['nutrient'].'|'.$row['per'];
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $product->nutritionFacts()->create($row + ['sort_order' => $i]);
            }

            $this->syncVariants($product, $data['variants'] ?? []);
            $this->syncTags($product, (string) ($data['tags'] ?? ''));

            return $product;
        });
    }

    private function syncVariants(Product $product, array $rows): void
    {
        $keep = [];
        foreach (array_values($rows) as $i => $row) {
            if (blank($row['sku'] ?? null) && blank($row['price'] ?? null) && empty($row['attribute_values'])) {
                continue;
            }
            $variant = $product->variants()->firstOrNew(['id' => $row['id'] ?? null]);
            $variant->fill([
                'sku' => $row['sku'] ?? null,
                'price' => $row['price'] ?? null,
                'sale_price' => $row['sale_price'] ?? null,
                'stock_qty' => $row['stock_qty'] ?? null,
                'stock_status' => $row['stock_status'] ?? 'in_stock',
                'net_weight' => $row['net_weight'] ?? null,
                'weight_unit' => $row['weight_unit'] ?? null,
                'is_default' => (bool) ($row['is_default'] ?? false),
                'sort_order' => $i,
            ]);
            $variant->product_id = $product->id;
            $variant->save();
            $variant->attributeValues()->sync($row['attribute_values'] ?? []);
            $keep[] = $variant->id;
        }
        $product->variants()->whereNotIn('id', $keep)->delete();
    }

    /** Tags are entered as comma-separated Persian names; other locales are translated in the translation manager. */
    private function syncTags(Product $product, string $input): void
    {
        $names = array_unique(array_filter(array_map('trim', preg_split('/[,،]/u', $input))));
        $ids = [];
        foreach ($names as $name) {
            $slug = TextNormalizer::slug($name);
            $tag = Tag::whereHas('translations', fn ($q) => $q->where('locale', 'fa')->where('slug', $slug))->first();
            if (! $tag) {
                $tag = Tag::create(['code' => substr(md5($slug), 0, 12).'-'.mb_substr(preg_replace('/[^a-z0-9-]/', '', $slug), 0, 40)]);
                $tag->saveTranslations(['fa' => ['name' => $name]], 'name');
            }
            $ids[] = $tag->id;
        }
        $product->tags()->sync($ids);
    }
}
