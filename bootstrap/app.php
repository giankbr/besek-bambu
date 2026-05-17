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
        // Reverse proxy / ngrok: honor X-Forwarded-* so URLs, Flux, and
        // Livewire resolve over HTTPS. Trusting "*" lets any client spoof
        // X-Forwarded-For (which the login throttle keys on), so production
        // should pin TRUSTED_PROXIES to the load balancer / proxy CIDR.
        // Read straight from the process env: config/.env are not loaded
        // yet at this point in the bootstrap, but real environment
        // variables (Docker/systemd/Forge/etc.) are, which is how
        // production should set this anyway — also config:cache safe.
        $trustedProxies = env('TRUSTED_PROXIES', '*');

        $middleware->trustProxies(
            at: $trustedProxies === '*'
                ? '*'
                : array_values(array_filter(array_map('trim', explode(',', (string) $trustedProxies)))),
        );

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
