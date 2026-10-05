<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\Difficulty;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Recipe extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected array $searchableTranslationFields = ['title', 'short_description'];

    protected $casts = [
        'status' => ContentStatus::class,
        'difficulty' => Difficulty::class,
        'nutrition' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function videoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function ingredientLines(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recipe_products');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(RecipeMedia::class)->orderBy('sort_order');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function totalMinutes(): ?int
    {
        $total = (int) $this->prep_minutes + (int) $this->cook_minutes;

        return $total ?: null;
    }

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $slug = $this->translation($locale)?->slug;

        return $slug ? route('recipes.show', ['locale' => $locale, 'slug' => $slug]) : null;
    }

    public function ingredientsFor(?string $locale = null)
    {
        $locale ??= app()->getLocale();
        $lines = $this->ingredientLines->where('locale', $locale);

        return $lines->isEmpty() && config('suru.content_fallback') ? $this->ingredientLines->where('locale', 'fa') : $lines;
    }

    public function stepsFor(?string $locale = null)
    {
        $locale ??= app()->getLocale();
        $steps = $this->steps->where('locale', $locale);

        return $steps->isEmpty() && config('suru.content_fallback') ? $this->steps->where('locale', 'fa') : $steps;
    }
}
