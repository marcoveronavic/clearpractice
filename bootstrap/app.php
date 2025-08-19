<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register session-aware middleware in the WEB stack (runs after StartSession)
        $middleware->web(
            append: [
                \App\Http\Middleware\RedirectToPractice::class,
            ],
        );

        // If you previously put anything in app/Http/Kernel.php, it is ignored on Laravel 12.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
