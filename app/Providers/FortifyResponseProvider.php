<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class FortifyResponseProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, fn () => new class implements LoginResponse
        {
            public function toResponse($request): RedirectResponse|JsonResponse
            {
                if ($request->wantsJson()) {
                    return new JsonResponse('', 204);
                }

                $user = $request->user();
                $target = $user && $user->is_admin ? route('dashboard') : route('account.index');

                return redirect()->intended($target);
            }
        });

        $this->app->singleton(RegisterResponse::class, fn () => new class implements RegisterResponse
        {
            public function toResponse($request): RedirectResponse|JsonResponse
            {
                if ($request->wantsJson()) {
                    return new JsonResponse('', 201);
                }

                return redirect()->intended(route('account.index'));
            }
        });
    }
}
