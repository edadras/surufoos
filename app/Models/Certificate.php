<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasTranslations;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'issued_at' => 'date'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
