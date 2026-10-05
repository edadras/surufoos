<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasTranslations;

    protected $table = 'media';

    protected $guarded = ['id'];

    protected $casts = [
        'variants' => 'array',
        'palette' => 'array',
        'has_transparency' => 'boolean',
        'focal_x' => 'float',
        'focal_y' => 'float',
    ];

    public function translationForeignKey(): string
    {
        return 'media_id';
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function path(): string
    {
        return trim($this->directory.'/'.$this->filename, '/');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path());
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isSvg(): bool
    {
        return $this->mime_type === 'image/svg+xml';
    }

    /**
     * URL of a generated variant, e.g. variantUrl('medium', 'webp').
     * Falls back to the closest larger size and finally to the original.
     */
    public function variantUrl(string $size = 'medium', ?string $format = null): string
    {
        $variants = $this->variants ?? [];
        $order = array_keys(config('suru.images.sizes'));
        $start = array_search($size, $order, true);
        $candidates = $start === false ? $order : array_reverse(array_slice($order, 0, $start + 1));

        foreach ($candidates as $candidate) {
            $key = $format ? "{$candidate}.{$format}" : $candidate;
            if (isset($variants[$key])) {
                return Storage::disk($this->disk)->url($variants[$key]['path']);
            }
        }

        return $this->url();
    }

    /** srcset string for a format ("webp", "avif" or null for the fallback raster). */
    public function srcset(?string $format = null): string
    {
        $parts = [];
        foreach ($this->variants ?? [] as $key => $variant) {
            [$size, $fmt] = array_pad(explode('.', $key, 2), 2, null);
            if ($fmt === $format && isset($variant['width'])) {
                $parts[] = Storage::disk($this->disk)->url($variant['path']).' '.$variant['width'].'w';
            }
        }

        return implode(', ', $parts);
    }

    public function hasFormat(string $format): bool
    {
        foreach (array_keys($this->variants ?? []) as $key) {
            if (str_ends_with($key, '.'.$format)) {
                return true;
            }
        }

        return false;
    }

    public function alt(?string $locale = null): string
    {
        return (string) ($this->tr('alt', $locale, true) ?? '');
    }

    public function caption(?string $locale = null): ?string
    {
        return $this->tr('caption', $locale, true);
    }

    public function objectPosition(): string
    {
        return "{$this->focal_x}% {$this->focal_y}%";
    }

    public function scopeMissingAlt($query)
    {
        return $query->where('mime_type', 'like', 'image/%')
            ->whereDoesntHave('translations', fn ($q) => $q->where('locale', 'fa')->whereNotNull('alt')->where('alt', '!=', ''));
    }
}
