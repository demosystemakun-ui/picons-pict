<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;   // ← INI YANG KURANG
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (
            $this->app->environment('production')
            || request()->header('X-Forwarded-Proto') === 'https'
            || str_contains(request()->header('Host') ?? '', 'ngrok')
        ) {
            URL::forceScheme('https');
        }
    }
}