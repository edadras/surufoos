<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageView extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['date' => 'date'];

    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }
}
