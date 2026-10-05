<?php

namespace App\Models;

use App\Enums\SectionType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = ['type' => SectionType::class, 'settings' => 'array', 'is_active' => 'boolean'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
