<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales the storefront supports. Indonesian is the default; English
     * is the alternative offered through the navbar language switcher.
     */
    public const SUPPORTED = ['id', 'en'];

    public const DEFAULT = 'id';

    /**
     * Resolve the active locale from the session (set by the language
     * switcher) and apply it for the rest of the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('locale')) {
            $request->session()->put('locale', config('app.locale', self::DEFAULT));
        }

        $locale = $request->session()->get('locale', self::DEFAULT);

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = self::DEFAULT;
            $request->session()->put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
