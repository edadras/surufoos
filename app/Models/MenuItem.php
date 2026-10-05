<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = ['is_mega' => 'boolean', 'mega' => 'array', 'is_active' => 'boolean'];

    /** Routes editors can link to without typing URLs. */
    public const ROUTES = [
        'home' => 'خانه',
        'products.index' => 'محصولات',
        'categories.index' => 'دسته‌بندی‌ها',
        'about' => 'درباره ما',
        'magazine.index' => 'مجله',
        'recipes.index' => 'دستورهای غذایی',
        'contact' => 'تماس با ما',
        'faq' => 'پرسش‌های متداول',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function label(?string $locale = null): ?string
    {
        return $this->tr('label', $locale, true);
    }

    public function href(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return match ($this->type) {
            'route' => $this->route_name && Route::has($this->route_name) ? route($this->route_name, ['locale' => $locale]) : null,
            'category', 'product', 'page', 'post', 'recipe' => $this->linkable?->url($locale),
            default => $this->tr('url', $locale, true),
        };
    }

    public function isCurrent(): bool
    {
        $href = $this->href();

        return $href && rtrim(url()->current(), '/') === rtrim($href, '/');
    }
}
