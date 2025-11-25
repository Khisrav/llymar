<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Enable CORS and security for API routes
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\RestrictApiAccess::class,
        ]);

        // Add CORS handling and profile completion check
        $middleware->alias([
            'cors' => \Illuminate\Http\Middleware\HandleCors::class,
            'profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        // Regenerate sitemap daily at 2am
        $schedule->command('sitemap:generate')->dailyAt('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
