<?php

namespace App\Listeners;

use App\Events\ContentChanged;
use App\Services\ActivityLogger;

/** Logs admin-driven changes to primary content (translation rows are covered by their parent's update). */
class RecordAdminActivity
{
    public function __construct(private ActivityLogger $logger) {}

    public function handle(ContentChanged $event): void
    {
        if (! auth()->check() || str_ends_with(class_basename($event->model), 'Translation')) {
            return;
        }
        $this->logger->log($event->action, $event->model);
    }
}
