<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

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
        RateLimiter::for('movies', function ($request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
