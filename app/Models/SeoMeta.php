<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $guarded = ['id'];

    protected $casts = ['noindex' => 'boolean'];

    /** Static listing routes whose meta tags are edited in the SEO screen. */
    public const ROUTES = [
        'home' => 'صفحه اصلی',
        'products.index' => 'فهرست محصولات',
        'categories.index' => 'دسته‌بندی‌ها',
        'magazine.index' => 'مجله',
        'recipes.index' => 'دستورهای غذایی',
        'about' => 'درباره ما',
        'contact' => 'تماس با ما',
        'faq' => 'پرسش‌های متداول',
        'search' => 'جستجو',
    ];

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }
}
