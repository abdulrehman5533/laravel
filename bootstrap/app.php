<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/ai-agent.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust ngrok proxy
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'api/tab-close-logout',
        ]);

        $middleware->alias([
            'permission'    => \App\Http\Middleware\CheckPermission::class,
            'central_admin' => \App\Http\Middleware\CentralAdminMiddleware::class,
            'plan_feature'  => \App\Http\Middleware\CheckPlanFeature::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\IdentifyTenant::class,
            \App\Http\Middleware\TrackUserSession::class,
            \App\Http\Middleware\CheckSecurityEnforcement::class,
            \App\Http\Middleware\RoleDashboardAccess::class,
            \App\Http\Middleware\RestrictPastDateEntry::class,
            \App\Http\Middleware\ValidateSingleSession::class,
            \App\Http\Middleware\CompanyContextMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')->with('error', 'Session expired, please login again.');
        });
    })->create();
