<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('flash', function () {
            return new \App\Services\Flash();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Solo configurar proxies confiables para Railway
        if (app()->environment('production')) {
            // Configurar proxies confiables para Railway
            request()->setTrustedProxies(['*'], 
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
            );
            
            // Forzar URLs HTTPS para que las cookies funcionen
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
