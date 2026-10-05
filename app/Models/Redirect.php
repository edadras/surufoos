<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'last_hit_at' => 'datetime'];

    public static function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $path = '/'.trim(rawurldecode($path), '/');

        return $path;
    }
}
