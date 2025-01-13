<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\RateLimit;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(RateLimit::API_MAX_ATTEMPTS->value)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(RateLimit::WEB_MAX_ATTEMPTS->value)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
