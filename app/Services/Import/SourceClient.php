<?php

namespace App\Services\Import;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** HTTP access to the source site (surufood.ir) with retries and a polite user agent. */
class SourceClient
{
    public function __construct(private string $base)
    {
        $this->base = rtrim($base, '/');
    }

    public function base(): string
    {
        return $this->base;
    }

    public function url(string $path): string
    {
        return str_starts_with($path, 'http') ? $path : $this->base.'/'.ltrim($path, '/');
    }

    public function get(string $path, array $query = []): Response
    {
        return Http::timeout(config('suru.import.timeout'))
            ->withHeaders(['User-Agent' => config('suru.import.user_agent'), 'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.5'])
            ->retry(3, 800, throw: false)
            ->get($this->url($path), $query);
    }

    /** @return array|null decoded JSON, or null when the endpoint is missing */
    public function json(string $path, array $query = []): ?array
    {
        $res = $this->get($path, $query);
        if (! $res->successful()) {
            return null;
        }
        $data = $res->json();

        return is_array($data) ? $data : null;
    }

    /** Walks a paginated WP REST collection. */
    public function paginate(string $path, array $query = []): \Generator
    {
        $page = 1;
        do {
            $res = $this->get($path, $query + ['per_page' => config('suru.import.per_page'), 'page' => $page]);
            if (! $res->successful()) {
                if ($page === 1) {
                    return;
                }
                break;
            }
            $items = $res->json() ?? [];
            foreach ($items as $item) {
                yield $item;
            }
            $totalPages = (int) ($res->header('X-WP-TotalPages') ?: 1);
            $page++;
        } while ($page <= $totalPages && $items);
    }

    public function html(string $path): string
    {
        $res = $this->get($path);
        if (! $res->successful()) {
            throw new RuntimeException("HTTP {$res->status()} for {$this->url($path)}");
        }

        return $res->body();
    }

    public function isReachable(): bool
    {
        try {
            return $this->get('/')->status() < 500;
        } catch (\Throwable) {
            return false;
        }
    }
}
