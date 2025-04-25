<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Añadir esta línea

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
        // Forzar HTTPS para todas las URLs generadas solo en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Puedes añadir más código aquí si lo necesitas en el futuro
    }
}
