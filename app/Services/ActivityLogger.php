<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = [], ?int $userId = null): void
    {
        if ($subject && $action === 'updated') {
            $changes = array_diff_key($subject->getChanges(), array_flip(['updated_at', 'views_count', 'remember_token', 'password', 'two_factor_secret', 'two_factor_recovery_codes']));
            if (! $changes) {
                return;
            }
            $properties['changed'] = array_keys($changes);
        }

        ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description ?? ($subject ? $this->describe($subject) : null),
            'properties' => $properties ?: null,
            'ip' => request()?->ip(),
        ]);
    }

    private function describe(Model $subject): string
    {
        $title = method_exists($subject, 'tr') ? ($subject->tr('title', 'fa', true) ?? $subject->tr('name', 'fa', true) ?? $subject->tr('label', 'fa', true)) : null;
        $title ??= $subject->getAttribute('title') ?? $subject->getAttribute('name') ?? $subject->getAttribute('key');

        return trim(class_basename($subject).' #'.$subject->getKey().' '.($title ?? ''));
    }
}
