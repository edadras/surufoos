<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = ['status' => ContentStatus::class, 'published_at' => 'datetime'];

    /** System pages keyed in code: their URL is fixed and their slug not editable. */
    public const SYSTEM_KEYS = ['home' => 'صفحه اصلی', 'about' => 'درباره سورو', 'contact' => 'تماس با ما'];

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published->value);
    }

    public function isSystem(): bool
    {
        return $this->key !== null && array_key_exists($this->key, self::SYSTEM_KEYS);
    }

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return match ($this->key) {
            'home' => route('home', ['locale' => $locale]),
            'about' => route('about', ['locale' => $locale]),
            'contact' => route('contact', ['locale' => $locale]),
            default => ($slug = $this->translation($locale)?->slug) ? route('page.show', ['locale' => $locale, 'slug' => $slug]) : null,
        };
    }
}
