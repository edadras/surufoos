<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEntry extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    /** history = about-page brand timeline, journey = homepage product journey. */
    public const GROUPS = ['history' => 'تاریخچه برند', 'journey' => 'مسیر محصول (صفحه اصلی)'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
