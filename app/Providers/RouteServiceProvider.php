<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * La constante de ruta para la página de "inicio".
     * Ahora la definimos como '/' para que no falle.
     */
    public const HOME = '/';

    /**
     * Método estático para obtener la redirección según el rol.
     * Útil para usar en middlewares y controladores.
     */
    public static function redirectTo()
    {
        $user = Auth::user();
        if (!$user) {
            return '/';
        }

        if ($user->rol === 'admin') {
            return '/administracion/dashboard';
        }

        if ($user->hasCompanyRole()) {
            return '/empresa/dashboard';
        }

        return '/candidatos/dashboard';
    }

    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting()
    {
        RateLimiting::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
