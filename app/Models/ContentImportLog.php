<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentImportLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['imported_at' => 'datetime'];

    public const STATUSES = ['imported' => 'درون‌ریزی شد', 'updated' => 'به‌روزرسانی شد', 'skipped' => 'بدون تغییر', 'failed' => 'خطا'];

    public function destination(): MorphTo
    {
        return $this->morphTo('destination', 'destination_type', 'destination_id');
    }
}
