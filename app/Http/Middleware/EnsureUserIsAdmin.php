<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_admin) {
            return redirect()->route('account.index')
                ->with('status', 'You do not have access to the admin area.');
        }

        // Optional enforcement: when the store admin has opted in we
        // refuse access to /admin until 2FA is confirmed. The user is
        // pushed to settings/security where they can finish setup.
        $requireTwoFactor = (bool) (function_exists('setting') ? setting('require_admin_2fa', false) : false);
        $hasTwoFactor = ! is_null($user->two_factor_confirmed_at ?? null);

        if ($requireTwoFactor && ! $hasTwoFactor && ! $request->routeIs('security.edit') && ! $request->routeIs('logout')) {
            return redirect()->route('security.edit')
                ->with('status', 'Two-factor authentication is required for admin access. Please finish setup.');
        }

        return $next($request);
    }
}
