<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\Cv;
use App\Models\CompanyProfile;
use App\Models\JobOffer;
use App\Models\CandidateSelection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CompanyCreditLedger;
use App\Models\CompanyResourceBalance;
use App\Http\Controllers\Company\JobOfferController as CompanyJobOfferController;
use App\Http\Controllers\MailsController;
use App\Models\Skill;
use App\Models\Tool;
use App\Models\JobSector;
use App\Models\CompanyCvViewLog;
use App\Services\JobOfferPublicationNotifier;
use App\Services\CandidateInterestNotifier;

class AdminController extends Controller
{
    private JobOfferPublicationNotifier $publicationNotifier;

    public function __construct(JobOfferPublicationNotifier $publicationNotifier)
    {
        $this->publicationNotifier = $publicationNotifier;
    }

    /**
     * Muestra el dashboard principal del administrador.
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $totalWorkers = User::where('rol', 'trabajador')->count();
        $verifiedWorkers = User::where('rol', 'trabajador')->whereNotNull('email_verified_at')->count();
        $totalCvs = Cv::count();
        $companies = CompanyProfile::select('id', 'company_name')->orderBy('company_name')->get();

        return view('admin.dashboard', compact('totalWorkers', 'verifiedWorkers', 'totalCvs', 'companies'));
    }

    /**
     * Muestra la página de configuración del sistema.
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Procesa y guarda los ajustes del sistema.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveSettings(Request $request)
    {
        // 1. Validación de los datos de entrada
        $request->validate([
            'site_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'max_experiences' => 'required|integer|min:1',
            'cv_upload_limit' => 'required|numeric|min:1',
        ]);

        // 2. Lógica para guardar la configuración (ej. en la base de datos o en el archivo .env)
        // Aquí iría la lógica real para persistir los datos (ej. usando un modelo Setting o Config::set).

        // Simulación de guardado:
        // Config::set('app.name', $request->site_name);
        // Setting::updateOrCreate(['key' => 'max_experiences'], ['value' => $request->max_experiences]);

        // 3. Redirigir con mensaje de éxito
        return redirect()->route('admin.settings')->with('success', 'La configuración del sistema ha sido actualizada exitosamente.');
    }
    /**
     * Muestra el formulario de edición del perfil del administrador.
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Actualiza el perfil del administrador (nombre y contraseña).
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Creación rápida de oferta desde el panel admin (company_profile_id puede ser 0).
     */
    public function storeJobOffer(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'modality' => ['required', 'string', Rule::in(['presencial', 'remoto', 'hibrido'])],
            'location' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'island' => ['nullable', 'string', 'max:255'],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['required', 'string', Rule::in(['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'])],
            'status' => ['required', 'string', Rule::in(['Borrador', 'Publicado', 'Finalizada', 'Anulado'])],
            'company_profile_id' => ['nullable', 'integer', 'min:0'],
            'required_languages' => ['nullable', 'array'],
            'required_languages.*' => ['string'],
        ]);

        $companyId = $data['company_profile_id'] ?? 0;
        $languageJson = json_encode($data['required_languages'] ?? []);

        JobOffer::create([
            'company_profile_id' => $companyId,
            'title' => $data['title'],
            'description' => $data['description'],
            'requirements' => $data['requirements'],
            'benefits' => $data['benefits'] ?? null,
            'modality' => $data['modality'],
            'location' => $data['location'],
            'province' => $data['province'] ?? null,
            'island' => $data['island'] ?? null,
            'salary_range' => $data['salary_range'] ?? null,
            'contract_type' => $data['contract_type'],
            'status' => $data['status'],
            'company_visible' => true,
            'required_languages' => $languageJson,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Oferta creada correctamente.');
    }

    /**
     * Listado de ofertas creadas para gestionarlas desde admin.
     */
    public function jobOffersIndex(Request $request)
    {
        $query = JobOffer::with('companyProfile')->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($companyId = $request->input('company_profile_id')) {
            $query->where('company_profile_id', $companyId);
        }

        if ($island = $request->input('island')) {
            $query->where('island', $island);
        }

        if ($from = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        $offers = $query->paginate(15)->withQueryString();
        $companies = CompanyProfile::select('id', 'company_name')->orderBy('company_name')->get();
        $islands = JobOffer::select('island')->whereNotNull('island')->distinct()->pluck('island');
        $pendingCount = JobOffer::where('pending_review', true)->count();

        return view('admin.ofertas.index', compact('offers', 'companies', 'islands', 'pendingCount'));
    }

    public function pendingJobOffers(Request $request)
    {
        $query = JobOffer::with('companyProfile')
            ->where('pending_review', true)
            ->orderByDesc('pending_review_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $offers = $query->paginate(15)->withQueryString();
        return view('admin.ofertas.pendientes', compact('offers'));
    }

    public function acceptPendingJobOffer(JobOffer $jobOffer)
    {
        if (!$jobOffer->pending_review) {
            return back()->with('warning', 'La oferta ya no está pendiente de revisión.');
        }

        $this->publishJobOffer($jobOffer);

        return back()->with('success', 'Oferta aprobada y publicada correctamente.');
    }

    public function approveJobOffer(JobOffer $jobOffer)
    {
        if ($jobOffer->status === 'Publicado' && $jobOffer->is_published) {
            return back()->with('info', 'La oferta ya estaba publicada.');
        }

        $this->publishJobOffer($jobOffer);

        return back()->with('success', 'Oferta aprobada y publicada correctamente.');
    }

    public function rejectPendingJobOffer(Request $request, JobOffer $jobOffer)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if (!$jobOffer->pending_review) {
            return back()->with('warning', 'La oferta ya no está pendiente de revisión.');
        }

        $this->handleRejection($jobOffer, $data['reason']);

        return back()->with('success', 'Oferta rechazada y la empresa ha sido notificada.');
    }

    public function rejectJobOffer(Request $request, JobOffer $jobOffer)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $this->handleRejection($jobOffer, $data['reason']);

        return back()->with('success', 'Oferta rechazada, el crédito se ha devuelto y la empresa fue notificada.');
    }

    /**
     * Editar oferta como admin.
     */
    public function editJobOffer(JobOffer $jobOffer)
    {
        $companies = CompanyProfile::select('id', 'company_name')->orderBy('company_name')->get();
        $allSkills = Skill::orderBy('name')->get(['id', 'name']);
        $allTools = Tool::orderBy('name')->get(['id', 'name']);
        $allSectors = JobSector::orderBy('name')->get(['id', 'name']);
        $allLanguages = CompanyJobOfferController::COMMON_LANGUAGES;
        $cost = CompanyJobOfferController::CREDIT_COST;

        $companyProfile = $jobOffer->companyProfile;
        $currentBalance = 0;
        $availableCvViews = 0;
        $hasUnlockedCvViews = false;

        if ($companyProfile) {
            $balance = CompanyResourceBalance::firstOrCreate(
                ['company_profile_id' => $companyProfile->id],
                [
                    'total_offer_credits' => 0,
                    'used_offer_credits' => 0,
                    'available_offer_credits' => 0,
                    'total_cv_views' => 0,
                    'used_cv_views' => 0,
                    'available_cv_views' => 0,
                    'total_user_seats' => 0,
                    'used_user_seats' => 0,
                    'available_user_seats' => 0,
                    'offer_visibility_days' => 0,
                ]
            );
            $currentBalance = (int) $balance->available_offer_credits;
            $availableCvViews = (int) $balance->available_cv_views;
            $hasUnlockedCvViews = CompanyCvViewLog::where('company_profile_id', $companyProfile->id)
                ->where('job_offer_id', $jobOffer->id)
                ->exists();
        }

        $availableWorkers = WorkerProfile::with('user')
            ->orderByDesc('created_at')
            ->take(60)
            ->get();

        return view('admin.ofertas.edit', compact(
            'jobOffer',
            'companies',
            'allSkills',
            'allTools',
            'allSectors',
            'allLanguages',
            'cost',
            'currentBalance',
            'availableCvViews',
            'hasUnlockedCvViews',
            'availableWorkers'
        ));
    }

    public function jobOfferCandidates(JobOffer $jobOffer, Request $request)
    {
        $term = trim((string) $request->input('search'));

        $query = WorkerProfile::query()
            ->with(['user'])
            ->whereHas('candidateSelections', function ($candidateQuery) use ($jobOffer) {
                $candidateQuery->where('job_offer_id', $jobOffer->id);
            });

        if ($term !== '') {
            $query->where(function ($searchQuery) use ($term) {
                $searchQuery->whereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                })->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('province', 'like', "%{$term}%")
                    ->orWhere('phone_number', 'like', "%{$term}%");
            });
        }

        $candidates = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.ofertas.candidates', compact('jobOffer', 'candidates', 'term'));
    }

    public function toggleCandidateSelection(Request $request, JobOffer $jobOffer)
    {
        $data = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
        ]);

        $workerProfile = WorkerProfile::findOrFail($data['worker_profile_id']);

        $selection = CandidateSelection::where('job_offer_id', $jobOffer->id)
            ->where('worker_profile_id', $workerProfile->id)
            ->first();

        if ($selection) {
            $selection->delete();
            return back()->with('warning', 'Candidato deseleccionado correctamente.');
        }

        CandidateSelection::create([
            'job_offer_id' => $jobOffer->id,
            'worker_profile_id' => $workerProfile->id,
            'company_profile_id' => $jobOffer->company_profile_id ?? 0,
            'selection_date' => Carbon::now(),
            'current_status' => 'Seleccionado',
            'priority' => 3,
            'initial_assessment' => 'Candidato pre-seleccionado por el equipo de administración.',
            'expected_salary' => 0,
            'time_to_hire_days' => 0,
        ]);

        CandidateInterestNotifier::notify($workerProfile, $jobOffer);

        return back()->with('success', 'Candidato seleccionado y notificado al trabajador.');
    }

    public function removeCandidateFromOffer(JobOffer $jobOffer, WorkerProfile $workerProfile)
    {
        $jobOffer->candidates()->detach($workerProfile->id);
        return back()->with('success', 'Candidato eliminado de la oferta.');
    }

    /**
     * Actualizar oferta como admin.
     */
    public function updateJobOffer(Request $request, JobOffer $jobOffer)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'modality' => ['required', 'string', Rule::in(['presencial', 'remoto', 'hibrido'])],
            'location' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'island' => ['nullable', 'string', 'max:255'],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['required', 'string', Rule::in(['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'])],
            'status' => ['required', 'string', Rule::in(['Borrador', 'Publicado', 'Finalizada', 'Anulado'])],
            'company_profile_id' => ['nullable', 'integer', 'min:0'],
            'company_visible' => ['nullable', 'boolean'],
        ]);

        $oldStatus = $jobOffer->status;
        $targetStatus = $data['status'];
        $refundRequired = $oldStatus === 'Publicado' && $targetStatus === 'Borrador';
        $wasPublishedBefore = $jobOffer->is_published;

        DB::beginTransaction();
        try {
            $jobOffer->update([
                'company_profile_id' => $data['company_profile_id'] ?? 0,
                'title' => $data['title'],
                'description' => $data['description'],
                'requirements' => $data['requirements'],
                'benefits' => $data['benefits'] ?? null,
                'modality' => $data['modality'],
                'location' => $data['location'],
                'province' => $data['province'] ?? null,
                'island' => $data['island'] ?? null,
                'salary_range' => $data['salary_range'] ?? null,
                'contract_type' => $data['contract_type'],
                'status' => $targetStatus,
                'company_visible' => $request->boolean('company_visible', true),
            ]);

            if ($refundRequired && $jobOffer->company_profile_id) {
                $this->refundOfferCredit($jobOffer);
            }

            DB::commit();
            $this->dispatchPublicationNotificationIfNeeded($jobOffer, $wasPublishedBefore);
            return redirect()->route('admin.ofertas.index')->with('success', 'Oferta actualizada correctamente.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error al actualizar oferta desde Admin', ['exception' => $th, 'offer_id' => $jobOffer->id]);
            return back()->withInput()->with('error', 'Ocurrió un error al actualizar la oferta.');
        }
    }

    private function dispatchPublicationNotificationIfNeeded(JobOffer $jobOffer, bool $wasPublishedBefore): void
    {
        if (!$wasPublishedBefore && $jobOffer->status === 'Publicado' && $jobOffer->is_published) {
            $this->publicationNotifier->notify($jobOffer);
        }
    }

    private function refundOfferCredit(JobOffer $jobOffer): void
    {
        $balance = CompanyResourceBalance::firstOrCreate(
            ['company_profile_id' => $jobOffer->company_profile_id],
            [
                'total_offer_credits' => 0,
                'used_offer_credits' => 0,
                'available_offer_credits' => 0,
                'total_cv_views' => 0,
                'used_cv_views' => 0,
                'available_cv_views' => 0,
                'total_user_seats' => 0,
                'used_user_seats' => 0,
                'available_user_seats' => 0,
                'offer_visibility_days' => 0,
            ]
        );

        $creditDelta = CompanyJobOfferController::CREDIT_COST;
        $balance->available_offer_credits = max(0, (int)$balance->available_offer_credits + $creditDelta);
        $balance->used_offer_credits = max(0, (int)$balance->used_offer_credits - $creditDelta);
        $balance->save();

        CompanyCreditLedger::create([
            'company_id' => $jobOffer->company_profile_id,
            'amount' => $creditDelta,
            'description' => "Reintegro por cambio a Borrador de oferta: {$jobOffer->title} (ID: {$jobOffer->id})",
            'related_type' => JobOffer::class,
            'related_id' => $jobOffer->id,
        ]);
    }

    private function publishJobOffer(JobOffer $jobOffer): void
    {
        $wasPublishedBefore = $jobOffer->is_published;
        $jobOffer->update([
            'is_published' => true,
            'status' => 'Publicado',
            'pending_review' => false,
            'pending_review_at' => null,
            'pending_review_reason' => null,
        ]);
        $this->dispatchPublicationNotificationIfNeeded($jobOffer, $wasPublishedBefore);
    }

    private function handleRejection(JobOffer $jobOffer, string $reason): void
    {
        $jobOffer->update([
            'status' => 'Borrador',
            'is_published' => false,
            'pending_review' => false,
            'pending_review_at' => null,
            'pending_review_reason' => $reason,
        ]);

        if ($jobOffer->company_profile_id) {
            $this->refundOfferCredit($jobOffer);
        }

        $this->notifyCompanyOfRejection($jobOffer, $reason);
    }

    private function notifyCompanyOfRejection(JobOffer $jobOffer, string $reason): void
    {
        $companyProfile = $jobOffer->companyProfile;
        if (!$companyProfile) {
            return;
        }

        $companyEmail = $companyProfile->contact_email ?? $companyProfile->email ?? $companyProfile->user->email ?? null;
        if (!$companyEmail) {
            return;
        }

        $offerEditUrl = route('admin.ofertas.edit', $jobOffer);
        $mensajeEmpresa = "Tu oferta <strong>{$jobOffer->title}</strong> ha sido rechazada por el equipo de FuerteJob.<br>"
            . "Motivo: {$reason}<br>Se ha devuelto el crédito correspondiente y puedes modificarla aquí: <a href=\"{$offerEditUrl}\">Editar oferta</a>.";

        MailsController::enviaremail(
            $companyEmail,
            $companyProfile->company_name ?? 'Empresa',
            'info@fuertejob.com',
            "Oferta rechazada: {$jobOffer->title}",
            $mensajeEmpresa
        );
    }
}
