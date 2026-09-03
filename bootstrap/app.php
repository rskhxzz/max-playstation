<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\AuthAdmin;
use App\Http\Middleware\AuthDriver;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Guard-based middleware — Admin dan Driver benar-benar terpisah
            'auth.admin'  => AuthAdmin::class,
            'auth.driver' => AuthDriver::class,
        ]);

        // Exclude webhook from CSRF
        $middleware->validateCsrfTokens(except: [
            '/webhooks/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
