<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleRedirects::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'locale' => SetLocale::class,
            'track' => TrackPageView::class,
            'admin' => AdminAuthenticate::class,
            'admin.can' => AdminPermission::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin*')
            ? route('admin.login')
            : route('account.login', ['locale' => $request->route('locale') ?? 'fa']));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('admin*')
            ? route('admin.dashboard')
            : route('account.index', ['locale' => app()->getLocale()]));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Admin errors render inside the Persian admin shell, not the public site layout.
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, Request $request) {
            if ($request->is('admin', 'admin/*') && ! $request->expectsJson() && in_array($e->getStatusCode(), [403, 404, 422], true)) {
                return response()->view('admin.error', [
                    'code' => $e->getStatusCode(),
                    'message' => $e->getMessage() ?: ($e->getStatusCode() === 403 ? 'شما به این بخش دسترسی ندارید.' : 'صفحه پیدا نشد.'),
                ], $e->getStatusCode());
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
