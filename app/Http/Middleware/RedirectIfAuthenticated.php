<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {

        $guards = empty($guards) ? [null] : $guards;
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // DEBUG: Vamos a ver qué está pasando
                Log::info('RedirectIfAuthenticated: Usuario logueado detectado', [
                    'id' => $user->id,
                    'rol' => $user->rol,
                    'hasCompanyRole' => $user->hasCompanyRole()
                ]);

                return redirect(RouteServiceProvider::redirectTo());
            }
        }

        return $next($request);
    }
}
