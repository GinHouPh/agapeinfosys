<?php

use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\LocalisePath;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\RecordTraffic;
use App\Http\Middleware\RedirectPaths;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SignProductApiRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Pages saved with a future date go live on their own.
        $schedule->command('pages:publish-scheduled')->everyMinute()->withoutOverlapping();

        // The traffic table is a row per public request; it is trimmed on a
        // rolling ninety-day window.
        $schedule->command('traffic:prune')->dailyAt('03:17')->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'can' => EnsurePermission::class,
            'product.api' => SignProductApiRequests::class,
        ]);

        // The language in the address is read before the router sees the
        // request, so the routes behind it stay the same ones.
        $middleware->prepend(LocalisePath::class);

        // Appended so the session has started and the admin can be found
        // before the gate decides whether to let them through.
        $middleware->appendToGroup('web', MaintenanceMode::class);

        // Legacy addresses are redirected before any route sees them.
        $middleware->prependToGroup('web', RedirectPaths::class);

        // The language the request is served in.
        $middleware->appendToGroup('web', SetLocale::class);

        // What visitors read. Appended, so the view is already rendered and
        // the response status is known.
        $middleware->appendToGroup('web', RecordTraffic::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
