<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use App\Support\CacheGroups;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Applies admin-managed redirects (e.g. old surufood.ir URLs) before routing. */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $request->is('admin*', 'build/*', 'storage/*')) {
            return $next($request);
        }

        $map = CacheGroups::remember(CacheGroups::CONTENT, 'redirects', fn () => Redirect::where('is_active', true)
            ->get(['id', 'from_path', 'to_path', 'status_code'])->keyBy('from_path')->map->only(['id', 'to_path', 'status_code'])->all());

        $path = Redirect::normalizePath($request->getPathInfo());
        if ($hit = $map[$path] ?? null) {
            Redirect::whereKey($hit['id'])->update(['hits' => \DB::raw('hits + 1'), 'last_hit_at' => now()]);
            $target = str_starts_with($hit['to_path'], 'http') ? $hit['to_path'] : url($hit['to_path']);

            return redirect()->to($target, in_array((int) $hit['status_code'], [301, 302, 307, 308], true) ? (int) $hit['status_code'] : 301);
        }

        return $next($request);
    }
}
