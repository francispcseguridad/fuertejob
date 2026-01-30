<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CompanyUserMembership;

class EnsureHasCompanyProfile
{
    /**
     * Asegura que el usuario tenga un perfil de empresa vinculado (ya sea como dueño o colaborador).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        // 1. Intentar resolver el perfil
        $companyProfile = $user->companyProfile;

        if (!$companyProfile) {
            $membership = CompanyUserMembership::where('user_id', $user->id)
                ->with('companyProfile')
                ->latest()
                ->first();

            $companyProfile = $membership?->companyProfile ?? null;
        }

        // 2. Si sigue sin perfil, actuar según el rol
        if (!$companyProfile) {
            // Si es un dueño de empresa, mandarlo a completar el registro
            if ($user->rol === 'empresa') {
                return redirect()->route('company.register.create')
                    ->with('warning', 'Debes completar los datos de tu empresa.');
            }

            // Si es un colaborador sin empresa (raro), mandarlo a la home con error
            return redirect()->route('home')
                ->with('error', 'Tu cuenta no tiene una empresa vinculada. Contacta con tu administrador.');
        }

        // Compartir el perfil en la sesión del usuario para este request
        $user->setRelation('companyProfile', $companyProfile);

        return $next($request);
    }
}
