<?php

namespace App\Providers;

use App\Listeners\SendEmailVerifiedWelcome;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureUrlSchemeForProxiedHttps();
        $this->configureRateLimiting();

        Event::listen(Verified::class, SendEmailVerifiedWelcome::class);
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('cart', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('shipping', fn (Request $request) => Limit::perMinute(180)->by($request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('newsletter', fn (Request $request) => Limit::perMinute(15)->by($request->ip()));
        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('payment-notification', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
    }

    /**
     * When APP_URL is https (e.g. ngrok), force generated URLs to https even if
     * the upstream request to php artisan serve arrived as http://127.0.0.1.
     */
    protected function configureUrlSchemeForProxiedHttps(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $appUrl = (string) config('app.url');

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
