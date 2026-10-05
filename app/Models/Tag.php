<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable');
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    public function recipes(): MorphToMany
    {
        return $this->morphedByMany(Recipe::class, 'taggable');
    }

    public function label(?string $locale = null): string
    {
        return (string) ($this->tr('name', $locale, true) ?? $this->code);
    }
}
