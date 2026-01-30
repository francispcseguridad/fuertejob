<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailsController;
use App\Models\JobOffer;
use App\Models\CompanyCreditLedger;
use App\Models\CompanyCreditUsageLog;
use App\Models\CompanyResourceBalance;
use App\Models\CompanyCvViewLog;
use App\Models\WorkerProfile;
use App\Models\Skill;
use App\Models\Tool;
use App\Models\JobOffersSkill;
use App\Models\JobOffersTool;
use App\Models\JobSector;
use App\Models\AnalyticsModel;
use App\Models\BonoPurchase;
use App\Services\JobOfferMatcher;
use App\Services\JobOfferPublicationNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class JobOfferController extends Controller
{
    // Coste de publicar una oferta en créditos
    const CREDIT_COST = 1;

    // Lista de idiomas comunes para la selección
    const COMMON_LANGUAGES = [
        'Español',
        'Inglés',
        'Alemán',
        'Francés',
        'Portugués',
        'Italiano',
        'Chino',
        'Japonés'
    ];

    private JobOfferMatcher $matcher;
    private JobOfferPublicationNotifier $publicationNotifier;

    public function __construct(JobOfferMatcher $matcher, JobOfferPublicationNotifier $publicationNotifier)
    {
        $this->matcher = $matcher;
        $this->publicationNotifier = $publicationNotifier;
    }


    /**
     * Muestra el listado de todas las ofertas de trabajo de la empresa, aplicando filtros y ordenación.
     */
    public function index(Request $request)
    {
        $companyProfile = Auth::user()->companyProfile;

        // Si no hay perfil, redirigir
        if (!$companyProfile) {
            return redirect()->route('empresa.profile.index')
                ->with('warning', 'Debes completar tu perfil de empresa antes de acceder a la gestión de ofertas.');
        }

        // Obtener parámetros de filtro
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        // Parámetros de ordenación
        $sortBy = $request->input('sort_by', 'created_at'); // Campo por defecto
        $sortDir = $request->input('sort_dir', 'desc'); // Dirección por defecto

        // Validar parámetros de ordenación
        if (!in_array($sortBy, ['created_at', 'title'])) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $offersQuery = $companyProfile->jobOffers();

        // Aplicar filtro de búsqueda por título o ubicación
        if ($search) {
            $offersQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        // Aplicar filtro por estado (valores almacenados: Borrador, Publicado, Finalizada, paused, closed)
        $validStatuses = ['Borrador', 'Publicado', 'Finalizada', 'paused', 'closed'];
        if ($statusFilter && in_array($statusFilter, $validStatuses)) {
            $effectiveStatus = $statusFilter === 'closed' ? 'Finalizada' : $statusFilter;
            $offersQuery->where('status', $effectiveStatus);
        }

        // Aplicar ordenación
        $offers = $offersQuery->orderBy($sortBy, $sortDir)->get();

        // Pasamos los parámetros de filtro y ordenación a la vista para mantener el estado
        return view('company.job_offers.index', compact('offers', 'search', 'statusFilter', 'sortBy', 'sortDir'));
    }

    /**
     * Muestra el formulario para crear una nueva oferta de trabajo.
     */
    public function create()
    {
        $user = Auth::user();
        $companyProfile = $user->companyProfile;

        if (!$companyProfile) {
            return redirect()->route('empresa.profile.index')
                ->with('warning', 'Debes completar tu perfil de empresa antes de publicar ofertas.');
        }

        $balance = $this->resolveResourceBalance($companyProfile);
        $currentBalance = (int) $balance->available_offer_credits;
        $cost = self::CREDIT_COST;

        // Cargamos datos para el formulario
        $allSkills = Skill::all(['id', 'name']);
        $allTools = Tool::all(['id', 'name']);
        $allSectors = JobSector::orderBy('name')->get(['id', 'name']);
        $allLanguages = self::COMMON_LANGUAGES; // Usamos la constante

        return view('company.job_offers.create', compact('currentBalance', 'cost', 'allSkills', 'allTools', 'allLanguages', 'allSectors'));
    }

    /**
     * Almacena una nueva oferta de trabajo (puede ser Borrador o Publicado).
     */
    public function store(Request $request)
    {
        $companyProfile = Auth::user()->companyProfile;

        // Modificamos las reglas de validación para incluir required_languages
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'modality' => ['required', 'string', Rule::in(['presencial', 'remoto', 'hibrido'])],
            'location' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'island' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'contract_type' => ['required', 'string', Rule::in(['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'])],
            'status' => ['required', 'string', Rule::in(['Borrador', 'Publicado', 'Finalizada', 'Anulado'])],
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'string', // Cambiado para permitir IDs temporales
            'tool_ids' => 'nullable|array',
            'tool_ids.*' => 'string', // Cambiado para permitir IDs temporales
            // NUEVA VALIDACIÓN: required_languages
            'required_languages' => 'nullable|array',
            'required_languages.*' => 'string', // Permitimos cualquier string para idiomas
            'company_visible' => 'nullable|boolean',
        ], [
            'title.required' => 'El título de la oferta es obligatorio.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'description.required' => 'La descripción del puesto es obligatoria.',
            'modality.required' => 'Debes seleccionar una modalidad de trabajo.',
            'modality.in' => 'La modalidad seleccionada no es válida.',
            'location.required' => 'La ubicación de la oferta es obligatoria.',
            'location.max' => 'La ubicación no puede exceder los 255 caracteres.',
            'salary_range.max' => 'El rango salarial no puede exceder los 255 caracteres.',
            'contract_type.required' => 'Debes seleccionar un tipo de contrato.',
            'contract_type.in' => 'El tipo de contrato seleccionado no es válido.',
            'status.required' => 'El estado de la oferta es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
        ]);

        $targetStatus = $validated['status'];

        try {
            DB::beginTransaction();

            $skillIds = $validated['skill_ids'] ?? [];
            $toolIds = $validated['tool_ids'] ?? [];
            $requiredLanguages = $validated['required_languages'] ?? [];
            $companyVisible = $request->has('company_visible') ? $request->boolean('company_visible') : true;

            // Procesar skills: crear los que no existen
            $processedSkillIds = [];
            foreach ($skillIds as $skillId) {
                if (str_starts_with($skillId, 'new_')) {
                    // Caso 1: Tiene prefijo 'new_' - es un skill nuevo
                    $skillName = strtolower(trim(substr($skillId, 4)));

                    if (strlen($skillName) > 255 || empty($skillName)) {
                        continue;
                    }

                    $skill = Skill::firstOrCreate(['name' => $skillName]);
                    $processedSkillIds[] = $skill->id;
                } elseif (is_numeric($skillId)) {
                    // Caso 2: Es un ID numérico - skill existente
                    $processedSkillIds[] = (int)$skillId;
                } else {
                    // Caso 3: Es un nombre sin prefijo 'new_' - crear/buscar
                    $skillName = strtolower(trim($skillId));

                    if (strlen($skillName) > 255 || empty($skillName)) {
                        continue;
                    }

                    $skill = Skill::firstOrCreate(['name' => $skillName]);
                    $processedSkillIds[] = $skill->id;
                }
            }

            // Procesar tools: crear las que no existen
            $processedToolIds = [];
            foreach ($toolIds as $toolId) {
                if (str_starts_with($toolId, 'new_')) {
                    // Caso 1: Tiene prefijo 'new_' - es una tool nueva
                    $toolName = strtolower(trim(substr($toolId, 4)));

                    if (strlen($toolName) > 255 || empty($toolName)) {
                        continue;
                    }

                    $tool = Tool::firstOrCreate(['name' => $toolName]);
                    $processedToolIds[] = $tool->id;
                } elseif (is_numeric($toolId)) {
                    // Caso 2: Es un ID numérico - tool existente
                    $processedToolIds[] = (int)$toolId;
                } else {
                    // Caso 3: Es un nombre sin prefijo 'new_' - crear/buscar
                    $toolName = strtolower(trim($toolId));

                    if (strlen($toolName) > 255 || empty($toolName)) {
                        continue;
                    }

                    $tool = Tool::firstOrCreate(['name' => $toolName]);
                    $processedToolIds[] = $tool->id;
                }
            }

            // Convertimos el array de idiomas a un string JSON
            $languageJson = json_encode($requiredLanguages);

            // Eliminamos campos que no van a la tabla principal o ya fueron procesados
            unset($validated['skill_ids'], $validated['tool_ids'], $validated['required_languages']);

            // ACTUALIZACIÓN: Asegurar que province e island se guarden
            $createData = array_merge($validated, [
                'company_profile_id' => $companyProfile->id,
                'status' => $targetStatus,
                'required_languages' => $languageJson,
                'job_sector_id' => $request->input('job_sector_id'),
                'company_visible' => $companyVisible,
            ]);

            $publishRequested = $targetStatus === 'Publicado';
            $createData['is_published'] = false;
            $createData['pending_review'] = $publishRequested;
            $createData['pending_review_at'] = $publishRequested ? now() : null;
            $createData['pending_review_reason'] = null;

            if ($request->has('province')) $createData['province'] = $request->input('province');
            if ($request->has('island')) $createData['island'] = $request->input('island');
            // Asignar el modelo de analítica según el bono vigente, si existe; si no, el activo de mayor nivel
            $latestBono = BonoPurchase::with('bonoCatalog')
                ->where('company_profile_id', $companyProfile->id)
                ->latest('purchase_date')
                ->latest()
                ->first();

            if ($latestBono?->bonoCatalog?->analytics_model_id) {
                $createData['analytics_model_id'] = $latestBono->bonoCatalog->analytics_model_id;
            } else {
                $analyticsModel = AnalyticsModel::where('is_active', true)->orderByDesc('level')->first();
                $createData['analytics_model_id'] = $analyticsModel?->id;
            }

            $offer = JobOffer::create($createData);

            // ASOCIACIÓN DE HABILIDADES Y HERRAMIENTAS (con IDs procesados)
            $offer->skills()->sync($processedSkillIds);
            $offer->tools()->sync($processedToolIds);

            $message = "Oferta '{$offer->title}' guardada como **Borrador** con éxito.";

            // Lógica de publicación y débito (sin cambios)
            if ($targetStatus === 'Publicado') {
                $balance = $this->resolveResourceBalance($companyProfile);
                if ($balance->available_offer_credits < self::CREDIT_COST) {
                    $offer->update(['status' => 'Borrador']);
                    DB::rollBack();
                    return back()->with('error', 'Saldo insuficiente para publicar la oferta. Se requiere ' . self::CREDIT_COST . ' crédito. El borrador ha sido guardado.')->withInput();
                }

                $balance->available_offer_credits = max(0, (int) $balance->available_offer_credits - self::CREDIT_COST);
                $balance->used_offer_credits += self::CREDIT_COST;
                $balance->save();

                if (!$offer->expires_at && (int) $balance->offer_visibility_days > 0) {
                    $offer->update([
                        'expires_at' => now()->addDays((int) $balance->offer_visibility_days),
                    ]);
                }

                CompanyCreditLedger::create([
                    'company_id' => $companyProfile->id,
                    'amount' => -self::CREDIT_COST,
                    'description' => "Débito por publicación de oferta: {$offer->title} (ID: {$offer->id})",
                    'related_type' => JobOffer::class,
                    'related_id' => $offer->id,
                ]);

                CompanyCreditUsageLog::recordUsage(
                    $companyProfile->id,
                    'offer_publication',
                    self::CREDIT_COST,
                    [
                        'related_type' => JobOffer::class,
                        'related_id' => $offer->id,
                        'description' => "Crédito usado para publicar la oferta: {$offer->title} (ID: {$offer->id})",
                        'metadata' => [
                            'offer_title' => $offer->title,
                            'offer_status' => $offer->status,
                        ],
                    ]
                );

                if ($publishRequested) {
                    $adminLink = route('admin.ofertas.pendientes');
                    $contactEmail = $companyProfile->contact_email ?? $companyProfile->email ?? 'info@fuertejob.com';
                    $mensajeAdmin = "La empresa <strong>{$companyProfile->company_name}</strong> ha solicitado publicar la oferta <strong>{$offer->title}</strong>. "
                        . "Revisa y acepta o rechaza la publicación en el panel de administración: <a href=\"{$adminLink}\">Ofertas pendientes</a>.";
                    MailsController::enviaremail(
                        'info@fuertejob.com',
                        'FuerteJob Administradores',
                        $contactEmail,
                        "Nueva oferta pendiente: {$offer->title}",
                        $mensajeAdmin
                    );
                }

                $this->notifyMatchingWorkers($offer);
                $message = "¡Oferta '{$offer->title}' publicada con éxito! Se ha descontado 1 crédito de tu saldo.";
            }

            DB::commit();

            $this->handlePublicationNotification($offer, false);

            if ($offer->pending_review) {
                $message = "Oferta '{$offer->title}' está publicada y pendiente de revisión por FuerteJob. Se ha restado 1 crédito.";
            }

            return redirect()->route('empresa.ofertas.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al almacenar oferta: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al intentar guardar o publicar la oferta.')->withInput();
        }
    }

    /**
     * Muestra el formulario para editar una oferta existente.
     */
    public function edit(JobOffer $oferta)
    {
        // Verificar que la oferta pertenezca a la empresa autenticada
        if ($oferta->company_profile_id !== Auth::user()->companyProfile->id) {
            abort(403, 'No tienes permiso para editar esta oferta.');
        }

        $companyProfile = Auth::user()->companyProfile;
        $balance = $this->resolveResourceBalance($companyProfile);
        $currentBalance = (int) $balance->available_offer_credits;
        $cost = self::CREDIT_COST;

        // Cargamos datos para el formulario
        $allSkills = Skill::all(['id', 'name']);
        $allTools = Tool::all(['id', 'name']);
        $allSectors = JobSector::orderBy('name')->get(['id', 'name']);
        $allLanguages = self::COMMON_LANGUAGES; // Usamos la constante

        // Cargamos los IDs actualmente seleccionados
        $selectedSkillIds = $oferta->skills->pluck('id')->toArray();
        $selectedToolIds = $oferta->tools->pluck('id')->toArray();
        // Cargamos los idiomas seleccionados (asumiendo que está guardado como JSON)
        $selectedLanguages = json_decode($oferta->required_languages ?? '[]', true);

        $availableCvViews = (int) ($companyProfile?->resourceBalance?->available_cv_views ?? 0);
        $hasUnlockedCvViews = CompanyCvViewLog::where('company_profile_id', $companyProfile?->id ?? 0)
            ->where('job_offer_id', $oferta->id)
            ->exists();

        return view('company.job_offers.edit', [
            'oferta' => $oferta,
            'currentBalance' => $currentBalance,
            'cost' => $cost,
            'allSkills' => $allSkills,
            'allTools' => $allTools,
            'selectedSkillIds' => $selectedSkillIds,
            'selectedToolIds' => $selectedToolIds,
            'allLanguages' => $allLanguages,
            'selectedLanguages' => $selectedLanguages,
            'allSectors' => $allSectors,
            'availableCvViews' => $availableCvViews,
            'hasUnlockedCvViews' => $hasUnlockedCvViews,
        ]);
    }

    /**
     * Actualiza una oferta de trabajo existente.
     */
    public function update(Request $request, JobOffer $oferta)
    {


        // Verificar que la oferta pertenezca a la empresa autenticada
        if ($oferta->company_profile_id !== Auth::user()->companyProfile->id) {
            abort(403, 'No tienes permiso para editar esta oferta.');
        }

        $companyProfile = Auth::user()->companyProfile;

        // Modificamos las reglas de validación para incluir required_languages
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'modality' => ['required', 'string', Rule::in(['presencial', 'remoto', 'hibrido'])],
            'location' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'island' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'contract_type' => ['required', 'string', Rule::in(['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'])],
            'status' => ['required', 'string', Rule::in(['Borrador', 'Publicado', 'Finalizada', 'Anulado'])],
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'string', // Cambiado para permitir IDs temporales
            'tool_ids' => 'nullable|array',
            'tool_ids.*' => 'string', // Cambiado para permitir IDs temporales
            // NUEVA VALIDACIÓN: required_languages
            'required_languages' => 'nullable|array',
            'required_languages.*' => 'string', // Permitimos cualquier string para idiomas
            'company_visible' => 'nullable|boolean',
        ], [
            'title.required' => 'El título de la oferta es obligatorio.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'description.required' => 'La descripción del puesto es obligatoria.',
            'modality.required' => 'Debes seleccionar una modalidad de trabajo.',
            'modality.in' => 'La modalidad seleccionada no es válida.',
            'location.required' => 'La ubicación de la oferta es obligatoria.',
            'location.max' => 'La ubicación no puede exceder los 255 caracteres.',
            'salary_range.max' => 'El rango salarial no puede exceder los 255 caracteres.',
            'contract_type.required' => 'Debes seleccionar un tipo de contrato.',
            'contract_type.in' => 'El tipo de contrato seleccionado no es válido.',
            'status.required' => 'El estado de la oferta es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
        ]);


        $targetStatus = $validated['status'];
        $oldStatus = $oferta->status;
        $needsDebit = false;

        if ($oldStatus !== 'Publicado' && $targetStatus === 'Publicado') {
            $needsDebit = true;
        }

        $wasPublishedBefore = $oferta->is_published;

        try {
            DB::beginTransaction();

            $skillIds = $validated['skill_ids'] ?? [];
            $toolIds = $validated['tool_ids'] ?? [];
            $requiredLanguages = $validated['required_languages'] ?? [];
            $companyVisible = $request->has('company_visible') ? $request->boolean('company_visible') : true;

            // Procesar skills: crear los que no existen
            $processedSkillIds = [];
            foreach ($skillIds as $skillId) {
                if (str_starts_with($skillId, 'new_')) {
                    // Caso 1: Tiene prefijo 'new_' - es un skill nuevo
                    $skillName = strtolower(trim(substr($skillId, 4)));

                    if (strlen($skillName) > 255 || empty($skillName)) {
                        continue;
                    }

                    $skill = Skill::firstOrCreate(['name' => $skillName]);
                    $processedSkillIds[] = $skill->id;
                } elseif (is_numeric($skillId)) {
                    // Caso 2: Es un ID numérico - skill existente
                    $processedSkillIds[] = (int)$skillId;
                } else {
                    // Caso 3: Es un nombre sin prefijo 'new_' - crear/buscar
                    $skillName = strtolower(trim($skillId));

                    if (strlen($skillName) > 255 || empty($skillName)) {
                        continue;
                    }

                    $skill = Skill::firstOrCreate(['name' => $skillName]);
                    $processedSkillIds[] = $skill->id;
                }
            }

            // Procesar tools: crear las que no existen
            $processedToolIds = [];
            foreach ($toolIds as $toolId) {
                if (str_starts_with($toolId, 'new_')) {
                    // Caso 1: Tiene prefijo 'new_' - es una tool nueva
                    $toolName = strtolower(trim(substr($toolId, 4)));

                    if (strlen($toolName) > 255 || empty($toolName)) {
                        continue;
                    }

                    $tool = Tool::firstOrCreate(['name' => $toolName]);
                    $processedToolIds[] = $tool->id;
                } elseif (is_numeric($toolId)) {
                    // Caso 2: Es un ID numérico - tool existente
                    $processedToolIds[] = (int)$toolId;
                } else {
                    // Caso 3: Es un nombre sin prefijo 'new_' - crear/buscar
                    $toolName = strtolower(trim($toolId));

                    if (strlen($toolName) > 255 || empty($toolName)) {
                        continue;
                    }

                    $tool = Tool::firstOrCreate(['name' => $toolName]);
                    $processedToolIds[] = $tool->id;
                }
            }

            // Convertimos el array de idiomas a JSON
            $languageJson = json_encode($requiredLanguages);

            // Eliminamos campos que no van a la tabla principal o ya fueron procesados
            unset($validated['skill_ids'], $validated['tool_ids'], $validated['required_languages']);

            // ACTUALIZACIÓN: Asegurar que province e island se guarden
            $updateData = array_merge($validated, [
                'status' => $targetStatus,
                'required_languages' => $languageJson,
                'job_sector_id' => $request->input('job_sector_id'),
                'company_visible' => $companyVisible,
            ]);

            // Forzar la inclusión de province e island si están en el request (por seguridad)
            if ($request->has('province')) $updateData['province'] = $request->input('province');
            if ($request->has('island')) $updateData['island'] = $request->input('island');

            if ($targetStatus === 'Publicado') {
                if ($needsDebit) {
                    $updateData['is_published'] = false;
                    $updateData['pending_review'] = true;
                    $updateData['pending_review_at'] = now();
                    $updateData['pending_review_reason'] = null;
                } elseif ($oferta->pending_review) {
                    $updateData['is_published'] = false;
                    // mantenemos el estado pendiente existente
                } else {
                    $updateData['is_published'] = true;
                    $updateData['pending_review'] = false;
                    $updateData['pending_review_at'] = null;
                    $updateData['pending_review_reason'] = null;
                }
            } else {
                $updateData['is_published'] = false;
                $updateData['pending_review'] = false;
                $updateData['pending_review_at'] = null;
                $updateData['pending_review_reason'] = null;
            }

            // Actualizamos la oferta
            $oferta->update($updateData);

            // ACTUALIZACIÓN DE HABILIDADES Y HERRAMIENTAS (con IDs procesados)
            $oferta->skills()->sync($processedSkillIds);
            $oferta->tools()->sync($processedToolIds);

            $message = "Oferta '{$oferta->title}' actualizada con éxito. Estado: **{$oferta->status_display}**.";

            if ($needsDebit) {
                $balance = $this->resolveResourceBalance($companyProfile);
                if ($balance->available_offer_credits < self::CREDIT_COST) {
                    $oferta->update(['status' => 'Borrador']);
                    DB::rollBack();
                    return back()->with('error', 'Saldo insuficiente para publicar la oferta. El borrador ha sido guardado, pero no publicado.')->withInput();
                }

                $balance->available_offer_credits = max(0, (int) $balance->available_offer_credits - self::CREDIT_COST);
                $balance->used_offer_credits += self::CREDIT_COST;
                $balance->save();

                if (!$oferta->expires_at && (int) $balance->offer_visibility_days > 0) {
                    $oferta->update([
                        'expires_at' => now()->addDays((int) $balance->offer_visibility_days),
                    ]);
                }

                CompanyCreditLedger::create([
                    'company_id' => $companyProfile->id,
                    'amount' => -self::CREDIT_COST,
                    'description' => "Débito por publicación de oferta: {$oferta->title} (ID: {$oferta->id})",
                    'related_type' => JobOffer::class,
                    'related_id' => $oferta->id,
                ]);

                CompanyCreditUsageLog::recordUsage(
                    $companyProfile->id,
                    'offer_publication',
                    self::CREDIT_COST,
                    [
                        'related_type' => JobOffer::class,
                        'related_id' => $oferta->id,
                        'description' => "Crédito usado para publicar la oferta: {$oferta->title} (ID: {$oferta->id})",
                        'metadata' => [
                            'offer_title' => $oferta->title,
                            'offer_status' => $oferta->status,
                        ],
                    ]
                );

                $adminLink = route('admin.ofertas.pendientes');
                $contactEmail = $companyProfile->contact_email ?? $companyProfile->email ?? 'info@fuertejob.com';
                $mensajeAdmin = "La empresa <strong>{$companyProfile->company_name}</strong> ha solicitado publicar la oferta <strong>{$oferta->title}</strong>. "
                    . "Revisa y acepta o rechaza la publicación en el panel de administración: <a href=\"{$adminLink}\">Ofertas pendientes</a>.";
                MailsController::enviaremail(
                    'info@fuertejob.com',
                    'FuerteJob Administradores',
                    $contactEmail,
                    "Nueva oferta pendiente: {$oferta->title}",
                    $mensajeAdmin
                );

                $this->notifyMatchingWorkers($oferta);
                $message = "¡Oferta '{$oferta->title}' publicada con éxito! Se ha descontado 1 crédito de tu saldo.";
            }

            DB::commit();
            $oferta->refresh();

            if ($oferta->pending_review) {
                $message = "Oferta '{$oferta->title}' está publicada y pendiente de revisión por FuerteJob. Se ha restado 1 crédito.";
            }

            $this->handlePublicationNotification($oferta, $wasPublishedBefore);

            return redirect()->route('empresa.ofertas.index')->with('success', $message);
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error("Error al actualizar oferta: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al intentar actualizar la oferta.')->withInput();
        }
    }


    public function matchWorkers(JobOffer $oferta)
    {

        // Comprobación de autorización
        if ($oferta->company_profile_id != Auth::user()->companyProfile->id) {
            abort(403, 'Acceso no autorizado.');
        }

        $oferta->loadMissing([
            'skills:id,name',
            'tools:id,name',
        ]);

        $matchData = $this->buildMatchData($oferta);
        $matchedWorkers = $matchData['matchedWorkers'];

        $companyProfile = Auth::user()->companyProfile;
        $availableCvViews = (int) ($companyProfile?->resourceBalance?->available_cv_views ?? 0);
        $hasUnlockedCvViews = CompanyCvViewLog::where('company_profile_id', $companyProfile?->id ?? 0)
            ->where('job_offer_id', $oferta->id)
            ->exists();

        // --- 4. Devolver la Vista con Resultados ---
        // Pasamos 'jobOffer' => $oferta para mantener compatibilidad con la vista si usa $jobOffer
        return view('company.job_offers.match_results', [
            'jobOffer' => $oferta,
            'matchedWorkers' => $matchedWorkers,
            'weights' => $matchData['weights'],
            'totalWeight' => $matchData['totalWeight'],
            'selectedCandidatesCount' => $oferta->candidates()->count(),
            'availableCvViews' => $availableCvViews,
            'hasUnlockedCvViews' => $hasUnlockedCvViews,
        ]);
    }

    public function unlockMatches(JobOffer $oferta)
    {
        if ($oferta->company_profile_id != Auth::user()->companyProfile->id) {
            abort(403, 'Acceso no autorizado.');
        }

        $companyProfile = Auth::user()->companyProfile;
        if (!$companyProfile) {
            return redirect()->route('empresa.profile.index')
                ->with('warning', 'Debes completar tu perfil de empresa antes de continuar.');
        }

        $alreadyUnlocked = CompanyCvViewLog::where('company_profile_id', $companyProfile->id)
            ->where('job_offer_id', $oferta->id)
            ->exists();

        if ($alreadyUnlocked) {
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'cvUrl' => route('empresa.trabajadores.show', ['workerProfile' => Auth::user()->workerProfile ?? 0, 'jobOffer' => $oferta])]);
            }
            return redirect()->route('empresa.ofertas.match', $oferta);
        }

        $balance = $this->resolveResourceBalance($companyProfile);
        if ((int) $balance->available_cv_views <= 0) {
            return redirect()->route('empresa.candidatos.seleccionados.index', $oferta)
                ->with('warning', 'No tienes créditos disponibles para ver CV coincidentes.');
        }

        $matchData = $this->buildMatchData($oferta);
        $matchedWorkers = $matchData['matchedWorkers'];

        DB::transaction(function () use ($balance, $companyProfile, $oferta, $matchedWorkers) {
            $balance->available_cv_views = max(0, (int) $balance->available_cv_views - 1);
            $balance->used_cv_views += 1;
            $balance->save();

            foreach ($matchedWorkers as $worker) {
                CompanyCvViewLog::firstOrCreate(
                    [
                        'company_profile_id' => $companyProfile->id,
                        'job_offer_id' => $oferta->id,
                        'worker_profile_id' => $worker->id,
                    ],
                    [
                        'match_score' => (float) ($worker->match_score ?? 0),
                        'unlocked_at' => now(),
                    ]
                );
            }
            $workerIds = $matchedWorkers->pluck('id')->values()->all();
            CompanyCreditUsageLog::recordUsage(
                $companyProfile->id,
                'cv_view_unlock',
                1,
                [
                    'related_type' => JobOffer::class,
                    'related_id' => $oferta->id,
                    'description' => 'Crédito usado para desbloquear coincidencias de CV',
                    'metadata' => [
                        'job_offer_id' => $oferta->id,
                        'worker_ids' => $workerIds,
                        'worker_count' => count($workerIds),
                    ],
                ]
            );
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'cvUrl' => null]);
        }

        return redirect()->route('empresa.ofertas.match', $oferta);
    }

    public function unlockWorkerMatch(JobOffer $oferta, WorkerProfile $worker)
    {
        if ($oferta->company_profile_id != Auth::user()->companyProfile->id) {
            return response()->json(['error' => 'Acceso no autorizado.'], 403);
        }

        $companyProfile = Auth::user()->companyProfile;
        if (!$companyProfile) {
            return response()->json(['error' => 'Completa el perfil de empresa primero.'], 403);
        }

        $existingLog = CompanyCvViewLog::where('company_profile_id', $companyProfile->id)
            ->where('job_offer_id', $oferta->id)
            ->where('worker_profile_id', $worker->id)
            ->first();

        $profileUrl = route('empresa.trabajadores.show', ['workerProfile' => $worker, 'jobOffer' => $oferta]);

        if ($existingLog) {
            return response()->json(['success' => true, 'cvUrl' => $profileUrl]);
        }

        $balance = $this->resolveResourceBalance($companyProfile);
        if ((int)$balance->available_cv_views <= 0) {
            return response()->json(['error' => 'No tienes créditos disponibles.'], 402);
        }

        DB::transaction(function () use ($balance, $companyProfile, $oferta, $worker) {
            $balance->available_cv_views = max(0, (int) $balance->available_cv_views - 1);
            $balance->used_cv_views += 1;
            $balance->save();

            CompanyCvViewLog::firstOrCreate(
                [
                    'company_profile_id' => $companyProfile->id,
                    'job_offer_id' => $oferta->id,
                    'worker_profile_id' => $worker->id,
                ],
                [
                    'match_score' => 0,
                    'unlocked_at' => now(),
                ]
            );

            CompanyCreditUsageLog::recordUsage(
                $companyProfile->id,
                'cv_view_unlock',
                1,
                [
                    'related_type' => JobOffer::class,
                    'related_id' => $oferta->id,
                    'description' => 'Crédito usado para desbloquear CV individual',
                    'metadata' => [
                        'job_offer_id' => $oferta->id,
                        'worker_profile_id' => $worker->id,
                    ],
                ]
            );
        });

        return response()->json(['success' => true, 'cvUrl' => $profileUrl]);
    }

    private function buildMatchData(JobOffer $oferta): array
    {
        $companyProfileId = optional(Auth::user()?->companyProfile)->id ?? null;
        return $this->matcher->match($oferta, $companyProfileId);
    }

    private function handlePublicationNotification(JobOffer $offer, bool $wasPublishedBefore): void
    {
        if (!$wasPublishedBefore && $offer->status === 'Publicado' && $offer->is_published) {
            $this->publicationNotifier->notify($offer);
        }
    }


    /**
     * SIMULACIÓN: Función para encontrar y notificar a los candidatos alineados.
     */
    protected function notifyMatchingWorkers(JobOffer $offer)
    {
        $matchCount = rand(5, 50);
        Log::info("Notificando a {$matchCount} candidatos alineados con la oferta: {$offer->title}.");
    }

    public function selectCandidate(JobOffer $oferta, WorkerProfile $worker)
    {
        // 1. Autorización
        if ($oferta->company_profile_id != Auth::user()->companyProfile->id) {
            return response()->json(['error' => 'Acceso no autorizado a la oferta.'], 403);
        }

        try {
            DB::beginTransaction();

            // Verificar si ya está seleccionado
            $isCandidate = $oferta->candidates()->where('worker_profile_id', $worker->id)->exists();

            if ($isCandidate) {
                // Deseleccionar (detach)
                $oferta->candidates()->detach($worker->id);
                $message = "Candidato **{$worker->user->name}** deseleccionado.";
                $status = 'deselected';
            } else {
                // Seleccionar (attach)
                // Usamos attach simple o syncWithoutDetaching, dependiendo de la necesidad de campos extra
                // Usaremos syncWithoutDetaching para mantener la simplicidad y evitar duplicados
                $oferta->candidates()->syncWithoutDetaching([$worker->id]);
                $message = "Candidato **{$worker->user->name}** seleccionado con éxito.";
                $status = 'selected';
            }

            Log::info("Acción de candidato ({$status}): WorkerID: {$worker->id} para OfferID: {$oferta->id}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status,
                'worker_id' => $worker->id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al seleccionar/deseleccionar candidato: " . $e->getMessage());
            return response()->json(['error' => 'Error en la operación del candidato.'], 500);
        }
    }

    private function resolveResourceBalance($companyProfile): CompanyResourceBalance
    {
        $balance = CompanyResourceBalance::firstOrCreate(
            ['company_profile_id' => $companyProfile->id],
            ['available_cv_views' => 0, 'available_user_seats' => 0]
        );

        $legacyCredits = (int) ($companyProfile->current_credit_balance ?? 0);
        if ($legacyCredits > 0 && (int) $balance->total_offer_credits === 0) {
            $balance->total_offer_credits = $legacyCredits;
            $balance->available_offer_credits = $legacyCredits;
            $balance->save();
        }

        return $balance;
    }
}
