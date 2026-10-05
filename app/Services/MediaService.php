<?php

namespace App\Services;

use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

class MediaService
{
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
        'image/avif' => 'avif', 'image/svg+xml' => 'svg', 'video/mp4' => 'mp4', 'video/webm' => 'webm',
    ];

    public function storeUpload(UploadedFile $file, ?MediaFolder $folder = null, ?int $userId = null, array $alt = []): Media
    {
        return $this->storeContents(file_get_contents($file->getRealPath()), $file->getClientOriginalName(), $folder, $userId, null, $alt);
    }

    public function storeFromUrl(string $url, ?MediaFolder $folder = null, array $alt = []): Media
    {
        $response = Http::timeout(config('suru.import.timeout'))
            ->withHeaders(['User-Agent' => config('suru.import.user_agent')])
            ->retry(2, 500)
            ->get($url);

        if (! $response->successful()) {
            throw new InvalidArgumentException("Download failed ({$response->status()}): {$url}");
        }

        $name = basename(parse_url($url, PHP_URL_PATH) ?: 'image');

        return $this->storeContents($response->body(), rawurldecode($name), $folder, null, $url, $alt);
    }

    /**
     * Validates and stores a file. The MIME type is sniffed from the bytes (never
     * trusted from the client), raster images must decode, and SVGs containing
     * scripts or event handlers are rejected.
     */
    public function storeContents(string $contents, string $originalName, ?MediaFolder $folder = null, ?int $userId = null, ?string $sourceUrl = null, array $alt = []): Media
    {
        $hash = hash('sha256', $contents);
        if ($existing = Media::where('content_hash', $hash)->first()) {
            return $existing;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: 'application/octet-stream';
        if ($mime === 'text/xml' || $mime === 'text/plain' || $mime === 'image/svg') {
            $mime = str_contains(substr($contents, 0, 2000), '<svg') ? 'image/svg+xml' : $mime;
        }
        if (! isset(self::EXTENSIONS[$mime])) {
            throw new InvalidArgumentException("نوع فایل مجاز نیست: {$mime}");
        }

        $width = $height = null;
        if ($mime === 'image/svg+xml') {
            $this->assertSafeSvg($contents);
        } elseif (str_starts_with($mime, 'image/')) {
            $info = @getimagesizefromstring($contents);
            if (! $info) {
                throw new InvalidArgumentException('فایل تصویر معتبر نیست.');
            }
            [$width, $height] = $info;
        }

        $disk = config('suru.images.disk');
        $directory = 'media/'.now()->format('Y/m').'/'.Str::lower(Str::ulid());
        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'file';
        $filename = Str::limit($base, 60, '').'.'.self::EXTENSIONS[$mime];

        Storage::disk($disk)->put($directory.'/'.$filename, $contents);

        $media = Media::create([
            'folder_id' => $folder?->id,
            'disk' => $disk,
            'directory' => $directory,
            'filename' => $filename,
            'original_name' => Str::limit($originalName, 250, ''),
            'mime_type' => $mime,
            'size' => strlen($contents),
            'width' => $width,
            'height' => $height,
            'content_hash' => $hash,
            'source_url' => $sourceUrl ? Str::limit($sourceUrl, 990, '') : null,
            'uploaded_by' => $userId,
            'conversion_status' => $this->isConvertible($mime) ? 'pending' : 'done',
        ]);

        foreach ($alt as $locale => $text) {
            if (filled($text)) {
                $media->translations()->create(['locale' => $locale, 'alt' => Str::limit(trim(strip_tags($text)), 250, '')]);
            }
        }

        if ($this->isConvertible($mime)) {
            GenerateMediaVariants::dispatch($media->id);
        }

        return $media;
    }

    public function isConvertible(string $mime): bool
    {
        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
    }

    private function assertSafeSvg(string $contents): void
    {
        if (preg_match('/<script|on[a-z]+\s*=|javascript:|<foreignObject|<iframe|<embed|<object/i', $contents)) {
            throw new InvalidArgumentException('فایل SVG حاوی کد اجرایی است و پذیرفته نمی‌شود.');
        }
    }

    /**
     * Generates resized raster, WebP and (where GD supports it) AVIF versions,
     * and extracts the dominant colour / palette used for product theming.
     * The original upload is never modified.
     */
    public function generateVariants(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $contents = $disk->get($media->path());
        $manager = new ImageManager(new Driver);
        $variants = [];
        $transparent = $this->hasTransparency($contents);
        $fallbackExt = $transparent ? 'png' : 'jpg';
        $avif = function_exists('imageavif');

        foreach (config('suru.images.sizes') as $size => $maxWidth) {
            if ($media->width && $media->width < $maxWidth && $size !== 'thumbnail' && ! empty($variants)) {
                continue; // Do not upscale; keep one variant at native size.
            }
            $image = $manager->read($contents);
            $image->scaleDown(width: $maxWidth);
            $w = $image->width();
            $h = $image->height();

            $raster = $transparent ? $image->toPng() : $image->toJpeg(config('suru.images.jpeg_quality'));
            $path = "{$media->directory}/{$size}.{$fallbackExt}";
            $disk->put($path, (string) $raster);
            $variants[$size] = ['path' => $path, 'width' => $w, 'height' => $h];

            $path = "{$media->directory}/{$size}.webp";
            $disk->put($path, (string) $image->toWebp(config('suru.images.webp_quality')));
            $variants["{$size}.webp"] = ['path' => $path, 'width' => $w, 'height' => $h];

            if ($avif) {
                $path = "{$media->directory}/{$size}.avif";
                $disk->put($path, (string) $image->toAvif(config('suru.images.avif_quality')));
                $variants["{$size}.avif"] = ['path' => $path, 'width' => $w, 'height' => $h];
            }
        }

        [$dominant, $palette] = $this->extractPalette($contents);

        $media->forceFill([
            'variants' => $variants,
            'has_transparency' => $transparent,
            'dominant_color' => $dominant,
            'palette' => $palette,
            'conversion_status' => 'done',
        ])->save();
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->deleteDirectory($media->directory);
        $media->delete();
    }

    private function hasTransparency(string $contents): bool
    {
        $img = @imagecreatefromstring($contents);
        if (! $img) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $step = max(1, (int) (min($w, $h) / 40));
        for ($x = 0; $x < $w; $x += $step) {
            foreach ([0, $h - 1] as $y) {
                if (((imagecolorat($img, $x, $y) >> 24) & 0x7F) > 100) {
                    imagedestroy($img);

                    return true;
                }
            }
        }
        imagedestroy($img);

        return false;
    }

    /**
     * Quantises a 48px thumbnail into colour buckets, ignoring transparent and
     * near-white/near-black pixels (packshot backgrounds), and returns the
     * dominant colour plus up to five palette colours.
     *
     * @return array{0: ?string, 1: array<int, string>}
     */
    public function extractPalette(string $contents): array
    {
        $src = @imagecreatefromstring($contents);
        if (! $src) {
            return [null, []];
        }
        $thumb = imagecreatetruecolor(48, 48);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, 48, 48, imagesx($src), imagesy($src));

        $buckets = [];
        for ($x = 0; $x < 48; $x++) {
            for ($y = 0; $y < 48; $y++) {
                $rgba = imagecolorat($thumb, $x, $y);
                if ((($rgba >> 24) & 0x7F) > 60) {
                    continue;
                }
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                if ($min > 235 || $max < 20) {
                    continue;
                }
                $key = (($r >> 4) << 8) | (($g >> 4) << 4) | ($b >> 4);
                $saturation = $max === 0 ? 0 : ($max - $min) / $max;
                $buckets[$key] ??= ['n' => 0, 'r' => 0, 'g' => 0, 'b' => 0, 's' => 0];
                $buckets[$key]['n']++;
                $buckets[$key]['r'] += $r;
                $buckets[$key]['g'] += $g;
                $buckets[$key]['b'] += $b;
                $buckets[$key]['s'] += $saturation;
            }
        }
        imagedestroy($src);
        imagedestroy($thumb);

        if (! $buckets) {
            return [null, []];
        }

        // Weight by frequency and saturation so brand colours beat neutral shadows.
        uasort($buckets, fn ($a, $b) => ($b['n'] * (0.5 + $b['s'] / $b['n'])) <=> ($a['n'] * (0.5 + $a['s'] / $a['n'])));
        $palette = [];
        foreach (array_slice($buckets, 0, 5) as $bucket) {
            $palette[] = sprintf('#%02x%02x%02x', $bucket['r'] / $bucket['n'], $bucket['g'] / $bucket['n'], $bucket['b'] / $bucket['n']);
        }

        return [$palette[0], $palette];
    }
}
