<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\CandidateSelection;
use App\Models\JobOffer;
use App\Models\CompanyCvViewLog;
use App\Models\WorkerProfile; // Asegúrate de importar WorkerProfile
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\CandidateInterestNotifier;

class CandidateSelectionController extends Controller
{


    public function editSelection(JobOffer $jobOffer, CandidateSelection $candidateSelection)
    {
        // 1. Verificación de Autorización y Relaciones
        $companyProfileId = Auth::user()->companyProfile->id ?? null;

        if (!$companyProfileId || $jobOffer->company_profile_id !== $companyProfileId || $candidateSelection->job_offer_id !== $jobOffer->id) {
            return redirect()->route('home')->with('error', 'Acceso no autorizado o registro inválido.');
        }

        // Cargar las relaciones necesarias para la vista
        $candidateSelection->load('workerProfile.user', 'jobOffer');

        // Definir los estados disponibles para los selectores
        $allStatuses = [
            'Seleccionado',
            'En Entrevista',
            'Prueba Técnica',
            'Oferta Enviada',
            'Contratado',
            'Rechazado',
            'En Espera'
        ];

        return view('company.job_offers.manage_selection', [
            'jobOffer' => $jobOffer,
            'selection' => $candidateSelection,
            'worker' => $candidateSelection->workerProfile,
            'allStatuses' => $allStatuses,
        ]);
    }

    public function updateSelection(Request $request, CandidateSelection $candidateSelection)
    {
        // 1. Verificación de Autorización
        $companyProfileId = Auth::user()->companyProfile->id ?? null;

        if (!$companyProfileId || $candidateSelection->company_profile_id !== $companyProfileId) {
            return response()->json(['error' => 'No autorizado para modificar este registro.'], 403);
        }

        // 2. Validación Manual (Validator) para evitar redirecciones automáticas de Laravel
        // Usamos Validator::make en lugar de $request->validate() para tener control total
        // y asegurar siempre una respuesta JSON, incluso si la validación falla.
        $validator = Validator::make($request->all(), [
            'current_status' => 'required|string|in:Seleccionado,En Entrevista,Prueba Técnica,Oferta Enviada,Contratado,Rechazado,En Espera',
            'priority' => 'required|integer|min:1|max:5',
            'initial_assessment' => 'nullable|string|max:1000',
            'expected_salary' => 'required|integer|min:0',
            'time_to_hire_days' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        // 3. Actualización del Registro
        try {
            $candidateSelection->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'El proceso de selección fue actualizado correctamente.',
                'updatedAt' => Carbon::parse($candidateSelection->updated_at)->format('H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar los datos: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Muestra la lista de candidatos seleccionados con su estado de selección.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */

    public function indexSelected(Request $request, JobOffer $jobOffer)
    {
        // 1. Verificación de Autorización
        // Aseguramos que la oferta pertenezca a la compañía autenticada
        $companyProfileId = Auth::user()->companyProfile->id ?? null;

        if (!$companyProfileId || $jobOffer->company_profile_id !== $companyProfileId) {
            return redirect()->route('home')->with('error', 'No tienes permiso para ver los candidatos de esta oferta.');
        }

        // Definir los estados disponibles para el filtro
        $allStatuses = [
            'Seleccionado',
            'En Entrevista',
            'Prueba Técnica',
            'Oferta Enviada',
            'Contratado',
            'Rechazado',
            'En Espera'
        ];

        // 2. Aplicar filtros (Búsqueda y Estado)
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        $sortBy = $request->get('sort_by', 'selection_date');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = 12; // Número de resultados por página

        // 3. Consulta de Candidatos Seleccionados
        $query = CandidateSelection::where('job_offer_id', $jobOffer->id) // <-- FILTRO CLAVE: por el ID de la Oferta
            ->where('company_profile_id', $companyProfileId) // Y por la empresa para doble seguridad
            ->with([
                'workerProfile.user',
                'workerProfile.skills',
                'workerProfile.tools',
                'workerProfile.languages',
                'jobOffer' // Opcional, pero útil si se navega a esta vista desde otro lado
            ]);


        // Filtrar por estado
        if (!empty($statusFilter) && in_array($statusFilter, $allStatuses)) {
            $query->where('current_status', $statusFilter);
        }

        // Filtrar por búsqueda (nombre, título profesional o bio del trabajador)
        if ($search) {
            $query->whereHas('workerProfile', function ($wpQuery) use ($search) {
                $wpQuery->where(function ($q) use ($search) {
                    $q->where('profession_title', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        // Buscar por nombre de usuario
                        ->orWhereHas('user', function ($uQuery) use ($search) {
                            $uQuery->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        // 4. Ordenación
        if (in_array($sortBy, ['selection_date', 'current_status', 'priority'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            // Ordenación por defecto
            $query->orderBy('selection_date', 'desc');
        }

        // 5. Ejecutar la consulta con paginación
        $selectedCandidates = $query->paginate($perPage)->withQueryString();

        $companyProfile = Auth::user()->companyProfile;
        $availableCvViews = (int) ($companyProfile?->resourceBalance?->available_cv_views ?? 0);
        $unlockedCvCount = CompanyCvViewLog::where('company_profile_id', $companyProfileId)
            ->where('job_offer_id', $jobOffer->id)
            ->count();
        $hasUnlockedCvViews = $unlockedCvCount > 0;
        $cvUnlockedWorkerIds = [];

        if ($companyProfileId) {
            $cvUnlockedWorkerIds = CompanyCvViewLog::where('company_profile_id', $companyProfileId)
                ->pluck('worker_profile_id')
                ->toArray();
        }

        // 6. Retornar la vista
        return view('company.job_offers.selected_workers', [
            'jobOffer' => $jobOffer, // Pasamos la oferta a la vista
            'selectedCandidates' => $selectedCandidates,
            'allStatuses' => $allStatuses,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'availableCvViews' => $availableCvViews,
            'unlockedCvCount' => $unlockedCvCount,
            'hasUnlockedCvViews' => $hasUnlockedCvViews,
            'cvUnlockedWorkerIds' => $cvUnlockedWorkerIds,
        ]);
    }

    /**
     * Almacena o elimina la selección de un candidato para una oferta de trabajo (Toggle).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        // Validación básica de los IDs necesarios
        $validator = Validator::make($request->all(), [
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'job_offer_id' => 'required|exists:job_offers,id',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Datos inválidos.'], 422);
            }
            return back()->withErrors($validator);
        }

        $workerProfileId = $request->input('worker_profile_id');
        $jobOfferId = $request->input('job_offer_id');

        $jobOffer = JobOffer::find($jobOfferId);

        if (!$jobOffer) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Oferta no encontrada.'], 404);
            }
            return back()->with('error', 'Oferta de trabajo no encontrada.');
        }

        // Security Check: Ensure the job offer belongs to the authenticated company
        $user = Auth::user();
        if (!$user || !$user->companyProfile || $jobOffer->company_profile_id !== $user->companyProfile->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'No autorizado.'], 403);
            }
            return back()->with('error', 'No autorizado.');
        }

        // Busca si ya existe la selección
        $selection = CandidateSelection::where('job_offer_id', $jobOfferId)
            ->where('worker_profile_id', $workerProfileId)
            ->first();

        $action = '';

        if ($selection) {
            // Si ya existe, lo eliminamos (Deselección)
            $selection->delete();
            $message = 'Candidato deseleccionado correctamente.';
            $type = 'warning';
            $action = 'deselected';
        } else {
            // Si no existe, lo creamos (Selección), proporcionando valores por defecto
            CandidateSelection::create([
                'job_offer_id' => $jobOfferId,
                'worker_profile_id' => $workerProfileId,
                'company_profile_id' => $jobOffer->company_profile_id,
                // -- Campos adicionales obligatorios con valores por defecto --
                'selection_date' => Carbon::now(),
                // Usamos 'Seleccionado' como el estado inicial basado en tu lista
                'current_status' => 'Seleccionado',
                'priority' => 3, // Prioridad media por defecto
                'initial_assessment' => 'Candidato pre-seleccionado por la empresa.', // Evaluación inicial en español
                'expected_salary' => 0, // Valor por defecto
                'time_to_hire_days' => 0, // Valor por defecto
            ]);
            $message = 'Candidato seleccionado y guardado.';
            $type = 'success';
            $action = 'selected';
            $workerProfile = WorkerProfile::findOrFail($workerProfileId);
            CandidateInterestNotifier::notify($workerProfile, $jobOffer);
        }

        // Calculate new count for the frontend to update
        $newCount = CandidateSelection::where('job_offer_id', $jobOfferId)->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'action' => $action,
                'message' => $message,
                'type' => $type,
                'new_count' => $newCount
            ]);
        }

        // Redirige de vuelta a la página con un mensaje flash (fallback for non-AJAX requests)
        return back()->with($type, $message);
    }
}
