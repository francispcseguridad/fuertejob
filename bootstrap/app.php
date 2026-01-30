<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware del grupo web: tracking de uso del portal
        $middleware->web(append: [
            \App\Http\Middleware\TrackPortalUsage::class,
        ]);

        $middleware->alias([
            'rol' => \App\Http\Middleware\CheckUserRole::class,
            'empresa' => \App\Http\Middleware\EnsureHasCompanyProfile::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
