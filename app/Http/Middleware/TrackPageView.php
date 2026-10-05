<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/** Aggregates public page views per day/path/locale after the response is sent. */
class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->isMethod('GET') || $request->ajax() || $response->getStatusCode() !== 200
            || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || preg_match('/bot|crawl|spider|slurp|lighthouse/i', (string) $request->userAgent())) {
            return;
        }

        $viewable = $request->attributes->get('viewable');
        $now = now();
        DB::table('page_views')->upsert([[
            'date' => $now->toDateString(),
            'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 400),
            'locale' => app()->getLocale(),
            'viewable_type' => $viewable?->getMorphClass(),
            'viewable_id' => $viewable?->getKey(),
            'views' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]], ['date', 'path', 'locale'], ['views' => DB::raw('views + 1'), 'updated_at' => $now]);

        if ($viewable && array_key_exists('views_count', $viewable->getAttributes())) {
            $viewable->newQuery()->whereKey($viewable->getKey())->increment('views_count');
        }
    }
}
