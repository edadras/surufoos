<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSectionTranslation extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['items' => 'array'];
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }
}
