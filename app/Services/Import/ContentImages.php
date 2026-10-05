<?php

namespace App\Services\Import;

use App\Models\Media;
use App\Services\MediaService;
use Throwable;

/**
 * Downloads source images into the media library (never hotlinks) and
 * rewrites <img> tags inside imported HTML to the local copies.
 */
class ContentImages
{
    /** @var array<string, ?Media> */
    private array $cache = [];

    public function __construct(private MediaService $media, private ImportLogger $log) {}

    public function fetch(?string $url, ?string $alt = null): ?Media
    {
        if (! $url || ! preg_match('~^https?://~i', $url)) {
            return null;
        }
        $url = html_entity_decode($url);
        // Prefer the full-size original over WordPress' resized "-300x300" copies.
        $original = preg_replace('~-\d{2,4}x\d{2,4}(\.(?:jpe?g|png|webp|gif))$~i', '$1', $url);

        if (array_key_exists($original, $this->cache)) {
            return $this->cache[$original];
        }
        if ($existing = Media::where('source_url', $original)->orWhere('source_url', $url)->first()) {
            return $this->cache[$original] = $existing;
        }

        foreach (array_unique([$original, $url]) as $candidate) {
            try {
                $alt = $alt !== null ? trim(html_entity_decode(strip_tags($alt))) : null;

                return $this->cache[$original] = $this->media->storeFromUrl($candidate, null, $alt ? ['fa' => $alt] : []);
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
        $this->log->say('  ! image failed: '.$url.' — '.($error ?? ''));

        return $this->cache[$original] = null;
    }

    public function localizeHtml(?string $html, string $sourceHost): ?string
    {
        if (! $html) {
            return $html;
        }

        return preg_replace_callback('~<img\b[^>]*>~i', function ($m) use ($sourceHost) {
            $tag = $m[0];
            if (! preg_match('~\ssrc=["\']([^"\']+)["\']~i', $tag, $src)) {
                return $tag;
            }
            $url = $src[1];
            if (str_starts_with($url, '/')) {
                $url = 'https://'.$sourceHost.$url;
            }
            preg_match('~\salt=["\']([^"\']*)["\']~i', $tag, $alt);
            $media = $this->fetch($url, $alt[1] ?? null);
            if (! $media) {
                return ''; // Remote image could not be copied; drop it rather than hotlink.
            }

            return sprintf('<img src="%s" alt="%s" width="%s" height="%s" loading="lazy">', e($media->variantUrl('large')), e($alt[1] ?? $media->alt('fa')), $media->width, $media->height);
        }, $html);
    }
}
