<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkerProfile;

class WorkerDashboardControllerNOUSAR extends Controller
{
    /**
     * Muestra la vista principal del Dashboard del Trabajador.
     * * Esta función solo se ejecuta si el usuario está autenticado y tiene el rol 'trabajador'.
     *
     * @param Request $request La solicitud HTTP actual.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // 1. Obtener el usuario autenticado
        $user = Auth::user();

        // 2. Cargar el WorkerProfile asociado
        // Asumiendo que existe una relación 'workerProfile' en el modelo User.
        $profile = $user->workerProfile;

        // 3. Verificación de existencia del perfil (Medida de seguridad)
        if (!$profile) {
            // Esto no debería pasar después del registro, pero es buena práctica.
            Auth::logout();
            return redirect('/login')->with('error', 'Tu perfil de trabajador no pudo ser encontrado. Por favor, inicia sesión de nuevo.');
        }

        // 4. Cargar datos adicionales para el dashboard
        // Ejemplo: Contar experiencias, educacion y CVs
        $experienceCount = $profile->experiences()->count();
        $educationCount = $profile->educations()->count();
        $cvCount = $profile->cvs()->count();

        // 5. Cargar las candidaturas del usuario (ofertas a las que se ha inscrito)
        // Incluimos la oferta de empleo y el perfil de la empresa para mostrar información completa
        $candidaturas = $profile->candidateSelections()
            ->with(['jobOffer.companyProfile'])
            ->orderBy('selection_date', 'desc')
            ->get();

        // 6. Devolver la vista, pasando los datos necesarios
        return view('worker.dashboard.index', [
            'user' => $user,
            'profile' => $profile,
            'experienceCount' => $experienceCount,
            'educationCount' => $educationCount,
            'cvCount' => $cvCount,
            'candidaturas' => $candidaturas,
            'dashboardMessage' => session('success', 'Bienvenido a tu panel de control.') // Muestra el mensaje de éxito del registro
        ]);
    }
}
