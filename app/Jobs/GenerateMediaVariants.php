<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateMediaVariants implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $mediaId) {}

    public function handle(MediaService $media): void
    {
        $item = Media::find($this->mediaId);
        if ($item) {
            $media->generateVariants($item);
        }
    }

    public function failed(Throwable $e): void
    {
        Media::whereKey($this->mediaId)->update(['conversion_status' => 'failed']);
    }
}
