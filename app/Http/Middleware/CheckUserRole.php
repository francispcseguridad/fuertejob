<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        // Normalize middleware arguments so comma-separated lists are split into individual roles.
        $normalizedRoles = [];
        foreach ($roles as $role) {
            $parts = array_filter(array_map('trim', explode(',', $role)));
            $normalizedRoles = array_merge($normalizedRoles, $parts);
        }
        $normalizedRoles = array_unique($normalizedRoles);
        // Si la ruta pide rol 'empresa', dejamos pasar a cualquiera que sea empresa o colaborador
        if (in_array('empresa', $normalizedRoles) && $user->hasCompanyRole()) {
            return $next($request);
        }

        // Para cualquier otro rol (admin, trabajador), comprobación exacta
        if (in_array($user->rol, $normalizedRoles)) {
            return $next($request);
        }

        return redirect()->route('home')->with('error', 'Acceso denegado: tu perfil (' . $user->rol . ') no tiene permiso para esta sección.');
    }
}
