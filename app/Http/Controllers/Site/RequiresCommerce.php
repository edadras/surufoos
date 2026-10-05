<?php

namespace App\Http\Controllers\Site;

use Illuminate\Routing\Controllers\Middleware;

/** Cart/checkout routes exist only while online sales are enabled in settings. */
trait RequiresCommerce
{
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                abort_unless(settings()->commerceEnabled(), 404);

                return $next($request);
            }),
        ];
    }
}
