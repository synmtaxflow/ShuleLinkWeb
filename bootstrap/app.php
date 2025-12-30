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
    ->withMiddleware(function (Middleware $middleware): void {
        // Use custom CSRF middleware to exclude Push SDK routes
        $middleware->validateCsrfTokens(except: [
            'iclock/getrequest',
            'iclock/cdata',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
