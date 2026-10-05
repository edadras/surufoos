<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['properties' => 'array'];

    public const ACTIONS = [
        'created' => 'ایجاد', 'updated' => 'ویرایش', 'deleted' => 'حذف', 'login' => 'ورود',
        'login_failed' => 'ورود ناموفق', 'logout' => 'خروج', 'imported' => 'درون‌ریزی', 'settings' => 'تنظیمات',
        '2fa_enabled' => 'فعال‌سازی ورود دومرحله‌ای', '2fa_disabled' => 'غیرفعال‌سازی ورود دومرحله‌ای',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
