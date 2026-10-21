<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (app()->isProduction() || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
        Blade::if('permiso', function (int|string $permiso): bool {
            return Auth::check() && Auth::user()->tienePermiso($permiso);
        });

        Blade::if('anypermiso', function (array $permisos): bool {
            return Auth::check() && Auth::user()->tieneAlgunPermiso($permisos);
        });
    }
}
