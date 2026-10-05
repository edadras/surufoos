<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin area gate: an active admin account, and — when two-factor is enabled
 * on the account — a session that has passed the TOTP challenge.
 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->is_admin || ! $user->is_active) {
            if ($user) {
                auth()->logout();
            }

            return redirect()->guest(route('admin.login'));
        }

        if ($user->hasTwoFactorEnabled() && ! $request->session()->get('admin_2fa_passed')) {
            return redirect()->route('admin.two-factor.challenge');
        }

        app()->setLocale('fa');

        return $next($request);
    }
}
