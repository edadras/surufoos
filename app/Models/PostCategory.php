<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function url(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $slug = $this->translation($locale, false)?->slug;

        return $slug ? route('magazine.category', ['locale' => $locale, 'slug' => $slug]) : null;
    }
}
