<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\StockStatus;
use App\Models\Concerns\HasTranslations;
use App\Support\TextNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected array $searchableTranslationFields = ['title', 'short_description', 'description'];

    protected $casts = [
        'status' => ContentStatus::class,
        'stock_status' => StockStatus::class,
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'show_inside' => 'boolean',
        'published_at' => 'datetime',
        'net_weight' => 'float',
    ];

    public function mainImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'main_media_id');
    }

    public function packshot(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'packshot_media_id');
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
            ->withPivot(['amount', 'position_x', 'position_y', 'is_highlight', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function nutritionFacts(): HasMany
    {
        return $this->hasMany(NutritionFact::class)->orderBy('sort_order');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_products');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_product');
    }

    public function favoritedBy(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeForCard(Builder $query): Builder
    {
        return $query->withTranslation()->with([
            'mainImage.translations', 'packshot.translations',
            'primaryCategory.translations',
        ]);
    }

    public function displayImage(): ?Media
    {
        return $this->packshot ?? $this->mainImage;
    }

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $slug = $this->translation($locale)?->slug;

        return $slug ? route('products.show', ['locale' => $locale, 'slug' => $slug]) : null;
    }

    public function effectivePrice(): ?int
    {
        return $this->sale_price && $this->sale_price < $this->price ? $this->sale_price : $this->price;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price && $this->price && $this->sale_price < $this->price;
    }

    public function isPurchasable(): bool
    {
        return $this->effectivePrice() !== null && $this->stock_status->isPurchasable();
    }

    public function themeColor(): string
    {
        return $this->theme_color
            ?: $this->displayImage()?->dominant_color
            ?: $this->primaryCategory?->themeColor()
            ?: '#E9E2D3';
    }

    public function weightLabel(): ?string
    {
        if (! $this->net_weight) {
            return null;
        }
        $value = rtrim(rtrim(number_format($this->net_weight, 2, '.', ''), '0'), '.');

        return TextNormalizer::localizeDigits($value).' '.__('units.'.($this->weight_unit ?: 'g'));
    }
}
