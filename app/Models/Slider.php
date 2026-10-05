<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slider extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = ['layers' => 'array', 'is_active' => 'boolean'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function mobileMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'mobile_media_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Layer images (ingredients etc.) with depth for the hero stage. */
    public function layerMedia()
    {
        $layers = collect($this->layers ?? []);
        $media = Media::with('translations')->whereIn('id', $layers->pluck('media_id')->filter())->get()->keyBy('id');

        return $layers->map(fn ($l) => array_merge($l, ['media' => $media->get($l['media_id'] ?? 0)]))->filter(fn ($l) => $l['media']);
    }
}
