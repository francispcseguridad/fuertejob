<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\CandidateSelection;
use App\Models\WorkerProfile;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\MailsController;

class JobSearchController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display a listing of job offers with filters.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get the current user's worker profile
        $user = Auth::user();
        $workerProfile = WorkerProfile::where('user_id', $user->id)->first();

        // Get IDs of offers the user has already applied to
        $appliedJobOfferIds = [];
        if ($workerProfile) {
            $appliedJobOfferIds = CandidateSelection::where('worker_profile_id', $workerProfile->id)
                ->pluck('job_offer_id')
                ->toArray();
        }

        // Initialize the query for published job offers (company_visible solo afecta visibilidad de nombre/logo)
        $query = JobOffer::whereIn('status', ['Publicado', 'published'])
            ->with(['companyProfile']); // Load company details

        // 1. Search filter (Title or Description)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Modality filter
        if ($request->filled('modality')) {
            $query->where('modality', $request->input('modality'));
        }

        // 3. Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%" . $request->input('location') . "%");
        }

        // 4. Contract Type filter
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->input('contract_type'));
        }

        // Execute query with pagination
        $jobOffers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('worker.job_search.index', compact('jobOffers', 'appliedJobOfferIds'));
    }

    /**
     * Display the specified job offer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $jobOffer = JobOffer::with(['companyProfile', 'skills', 'tools', 'islandRelation'])->findOrFail($id);

        // Verificar si el usuario ya se ha inscrito
        $user = Auth::user();
        $workerProfile = WorkerProfile::where('user_id', $user->id)->first();
        $hasApplied = false;

        if ($workerProfile) {
            $hasApplied = CandidateSelection::where('worker_profile_id', $workerProfile->id)
                ->where('job_offer_id', $jobOffer->id)
                ->exists();
        }

        // Tracking de vistas (solo una vez por sesión por oferta)
        $this->analyticsService->logJobOfferView($jobOffer, request());

        return view('worker.job_search.show', compact('jobOffer', 'hasApplied'));
    }

    /**
     * Store a newly created application in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        $request->validate([
            'job_offer_id' => 'required|exists:job_offers,id',
            'time_to_hire_days' => 'nullable|integer',
            'initial_assessment' => 'nullable|string|max:1000', // Carta de presentación breve
        ]);

        $user = Auth::user();
        $workerProfile = WorkerProfile::where('user_id', $user->id)->first();

        if (!$workerProfile) {
            return response()->json(['success' => false, 'message' => 'Debes completar tu perfil de trabajador antes de inscribirte.'], 403);
        }

        $jobOffer = JobOffer::with('companyProfile')->findOrFail($request->job_offer_id);

        // Tracking de click en inscribirse (cuenta aunque luego no se complete)
        $this->analyticsService->logJobOfferApplyClick($jobOffer);

        // Verificar si ya existe la inscripción
        $exists = CandidateSelection::where('worker_profile_id', $workerProfile->id)
            ->where('job_offer_id', $jobOffer->id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Ya te has inscrito en esta oferta.'], 422);
        }

        try {
            DB::beginTransaction();

            $application = CandidateSelection::create([
                'job_offer_id' => $jobOffer->id,
                'worker_profile_id' => $workerProfile->id,
                'company_profile_id' => $jobOffer->company_profile_id,
                'selection_date' => Carbon::now(),
                'current_status' => 'En espera', // Estado inicial
                'priority' => 'Medium', // Valor por defecto
                'initial_assessment' => $request->initial_assessment, // Comentarios del candidato
                'expected_salary' => 0,
                'time_to_hire_days' => $request->time_to_hire_days,
            ]);

            DB::commit();

            if ($jobOffer->companyProfile) {
                $companyEmail = $jobOffer->companyProfile->contact_email ?? $jobOffer->companyProfile->email;

                if ($companyEmail) {
                    $candidateName = trim("{$workerProfile->first_name} {$workerProfile->last_name}");
                    if (!$candidateName) {
                        $candidateName = $user->name;
                    }

                    $subject = "Nuevo candidato inscrito en {$jobOffer->title}";
                    $message = "El candidato {$candidateName} se ha inscrito en la oferta \"{$jobOffer->title}\".";

                    MailsController::enviaremail($companyEmail, $candidateName, $user->email, $subject, $message);
                }
            }

            return response()->json(['success' => true, 'message' => 'Te has inscrito correctamente en la oferta.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al procesar tu inscripción: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancela la inscripción del trabajador autenticado a una oferta.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'job_offer_id' => 'required|exists:job_offers,id',
        ]);

        $user = Auth::user();
        $workerProfile = WorkerProfile::where('user_id', $user->id)->first();

        if (!$workerProfile) {
            return response()->json(['success' => false, 'message' => 'No se encontró el perfil de trabajador.'], 403);
        }

        $selection = CandidateSelection::where('worker_profile_id', $workerProfile->id)
            ->where('job_offer_id', $request->job_offer_id)
            ->first();

        if (!$selection) {
            return response()->json(['success' => false, 'message' => 'No existe una inscripción para esta oferta.'], 404);
        }

        try {
            $selection->delete();
            return response()->json(['success' => true, 'message' => 'Inscripción anulada correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se pudo anular la inscripción.'], 500);
        }
    }
}
