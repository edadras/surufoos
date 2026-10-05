<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Post extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected array $searchableTranslationFields = ['title', 'short_description', 'description'];

    protected $casts = [
        'status' => ContentStatus::class,
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'post_product');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value)
            ->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $slug = $this->translation($locale)?->slug;

        return $slug ? route('magazine.show', ['locale' => $locale, 'slug' => $slug]) : null;
    }

    public function authorName(): ?string
    {
        return $this->author_name ?: $this->author?->name;
    }

    public function readingMinutes(): int
    {
        if ($this->reading_minutes) {
            return $this->reading_minutes;
        }
        $words = count(preg_split('/\s+/u', strip_tags((string) $this->tr('description')), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 200));
    }
}
