<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Esta línea fuerza a Laravel a generar todas las URLs con HTTPS.
        // Es la solución más efectiva para entornos con proxy como DigitalOcean.
        URL::forceScheme('https'); 
    }

    // ... (El resto del código de la clase)
}