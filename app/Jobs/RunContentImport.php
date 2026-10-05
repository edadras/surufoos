<?php

namespace App\Jobs;

use App\Services\ActivityLogger;
use App\Services\Import\SuruFoodImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunContentImport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(public string $source, public array $only = [], public ?int $userId = null) {}

    public function handle(SuruFoodImporter $importer, ActivityLogger $logger): void
    {
        $counts = $importer->run($this->source, $this->only);
        $logger->log('imported', null, 'درون‌ریزی از '.$this->source.': '.json_encode($counts), $counts, $this->userId);
    }
}
