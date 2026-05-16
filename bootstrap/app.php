<?php

use App\Http\Middleware\EnsureOrderAccessible;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ngrok / reverse proxy: honor X-Forwarded-Proto so URLs, Flux, and Livewire load over HTTPS
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'payment/notification',
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'order.access' => EnsureOrderAccessible::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
