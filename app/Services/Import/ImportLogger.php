<?php

namespace App\Services\Import;

use App\Models\ContentImportLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Throwable;

/**
 * Audit trail for the migration: one row per source item with its URL,
 * destination record and content hash, so re-runs update changed items,
 * skip unchanged ones and every imported record can be traced to its source.
 */
class ImportLogger
{
    /** @var callable|null */
    private $output;

    public array $counts = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

    public function __construct(?callable $output = null)
    {
        $this->output = $output;
    }

    public function key(string $sourceType, string|int $id): string
    {
        return hash('sha256', $sourceType.'|'.$id);
    }

    public function find(string $sourceType, string|int $id): ?ContentImportLog
    {
        return ContentImportLog::where('source_type', $sourceType)->where('source_key', $this->key($sourceType, $id))->first();
    }

    public function hash(mixed $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    /** True when the item was imported before with the same content. */
    public function unchanged(string $sourceType, string|int $id, string $hash): bool
    {
        $log = $this->find($sourceType, $id);

        return $log && $log->content_hash === $hash && $log->status !== 'failed' && $log->destination_id
            && class_exists($class = Relation::getMorphedModel($log->destination_type) ?? '')
            && $class::whereKey($log->destination_id)->exists();
    }

    public function destination(string $sourceType, string|int $id): ?Model
    {
        $log = $this->find($sourceType, $id);
        if (! $log?->destination_type || ! $log->destination_id) {
            return null;
        }
        $class = Relation::getMorphedModel($log->destination_type);

        return $class ? $class::find($log->destination_id) : null;
    }

    public function record(string $sourceType, string|int $id, string $url, ?Model $destination, string $hash, string $status, ?string $message = null): void
    {
        ContentImportLog::updateOrCreate(
            ['source_type' => $sourceType, 'source_key' => $this->key($sourceType, $id)],
            [
                'source_url' => mb_substr($url, 0, 1000),
                'destination_type' => $destination?->getMorphClass(),
                'destination_id' => $destination?->getKey(),
                'content_hash' => $hash,
                'status' => $status,
                'message' => $message ? mb_substr($message, 0, 2000) : null,
                'imported_at' => now(),
            ]
        );
        $this->counts[$status] = ($this->counts[$status] ?? 0) + 1;
        $this->say(sprintf('[%s] %s %s', $status, $sourceType, $url));
    }

    public function failed(string $sourceType, string|int $id, string $url, Throwable|string $error): void
    {
        $this->record($sourceType, $id, $url, null, '', 'failed', $error instanceof Throwable ? $error->getMessage() : $error);
    }

    public function say(string $message): void
    {
        if ($this->output) {
            ($this->output)($message);
        }
    }
}
