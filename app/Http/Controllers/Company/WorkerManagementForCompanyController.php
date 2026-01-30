<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\WorkerProfile;
use App\Models\Skill; // Mantener por si se necesita para select avanzado, aunque no se usa directamente ahora
use App\Models\Tool;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cv;
use App\Models\CompanyCvViewLog;
use App\Models\CompanyResourceBalance;
use App\Models\JobOffer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Company\CvController;

class WorkerManagementForCompanyController extends Controller
{
    /**
     * Asegura que el usuario esté autenticado.
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }



    /**
     * Muestra una lista de perfiles de trabajadores disponibles para la empresa.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Verificar si la empresa tiene un perfil creado
        if (!Auth::user()->companyProfile) {
            return redirect()->route('company.profile.create')->with('warning', 'Debes completar tu perfil de empresa antes de acceder a los perfiles de trabajadores.');
        }

        // Parámetros de filtrado y ordenación
        $search = $request->get('search');
        $modalityFilter = $request->get('modality');
        $tags = $request->get('tags'); // Nuevo campo para tags unificados (skills, tools, languages)
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDir = $request->get('sort_dir', 'desc');

        // Construcción de la consulta base para perfiles publicados
        $query = WorkerProfile::query()
            ->with(['user', 'skills', 'tools', 'languages']); // Precargar relaciones

        // 1. Filtro de búsqueda general (por nombre, título de profesión o biografía)
        $query->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('profession_title', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($r) use ($search) {
                        $r->where('name', 'like', "%{$search}%");
                    });
            });
        });

        // 2. Filtro por modalidad de trabajo preferida
        $query->when($modalityFilter, function ($query, $modalityFilter) {
            $query->where('preferred_modality', $modalityFilter);
        });

        // 3. Filtro por Habilidades, Herramientas e Idiomas (Tags unificados)
        $query->when($tags, function ($query, $tags) {
            // Convertir la cadena de tags separada por comas en un array, limpiar y filtrar vacíos
            $tagArray = array_map('trim', explode(',', $tags));
            $tagArray = array_filter($tagArray);

            if (!empty($tagArray)) {
                // Aplicar un filtro que coincida con CUALQUIERA de los tags proporcionados
                // en CUALQUIERA de las tres tablas relacionales.
                $query->where(function ($q) use ($tagArray) {
                    // Búsqueda en Habilidades (Skills)
                    $q->orWhereHas('skills', function ($r) use ($tagArray) {
                        // Usamos LIKE para permitir coincidencias parciales si el tag es una palabra clave
                        foreach ($tagArray as $tag) {
                            $r->orWhere('name', 'like', "%{$tag}%");
                        }
                    });

                    // Búsqueda en Herramientas (Tools)
                    $q->orWhereHas('tools', function ($r) use ($tagArray) {
                        foreach ($tagArray as $tag) {
                            $r->orWhere('name', 'like', "%{$tag}%");
                        }
                    });

                    // Búsqueda en Idiomas (Languages)
                    $q->orWhereHas('languages', function ($r) use ($tagArray) {
                        foreach ($tagArray as $tag) {
                            $r->orWhere('name', 'like', "%{$tag}%");
                        }
                    });
                });
            }
        });

        // Aplicar ordenación
        $workers = $query->orderBy($sortBy, $sortDir)->paginate(12);

        // Datos para la vista
        $modalities = ['presencial', 'remoto', 'hibrido'];

        return view('company.worker.index', [
            'workers' => $workers,
            'modalities' => $modalities,
            'search' => $search,
            'modalityFilter' => $modalityFilter,
            'tags' => $tags, // Devolver los tags para mantener el valor en el formulario
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    /**
     * Muestra el perfil detallado de un trabajador junto con su CV.
     *
     * @param \App\Models\WorkerProfile $workerProfile
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, WorkerProfile $workerProfile, JobOffer $jobOffer)
    {
        $companyProfile = Auth::user()->companyProfile;
        if (!$companyProfile || $jobOffer->company_profile_id !== $companyProfile->id) {
            return redirect()->route('empresa.ofertas.index')
                ->with('warning', 'Accede desde una oferta válida para ver este perfil.');
        }
        $companyProfileId = $companyProfile->id ?? null;
        $cvUnlocked = false;
        $availableCvViews = null;
        $purchaseUrl = route('empresa.bonos.catalogo');
        $resourceBalance = null;
        if ($companyProfileId) {
            $cvUnlocked = CompanyCvViewLog::where('company_profile_id', $companyProfileId)
                ->where('worker_profile_id', $workerProfile->id)
                ->exists();
            $resourceBalance = CompanyResourceBalance::firstWhere('company_profile_id', $companyProfileId);
            $availableCvViews = (int) ($resourceBalance->available_cv_views ?? 0);
        }

        // 2. Cargar relaciones necesarias para la vista
        $workerProfile->load([
            'user',
            'skills',
            'tools',
            'languages',
            'educations',
            'experiences',
        ]);

        // 3. Obtener el CV principal del trabajador
        $primaryCv = $cvUnlocked ? Cv::getPrimaryCvByWorkerProfileId($workerProfile->id) : null;

        return view('company.worker.show', [
            'worker' => $workerProfile,
            'primaryCv' => $primaryCv, // Se pasa el objeto Cv a la vista
            'cvUnlocked' => $cvUnlocked,
            'jobOfferId' => $jobOffer->id,
            'availableCvViews' => $availableCvViews,
            'purchaseUrl' => $purchaseUrl,
        ]);
    }
    /**
     * Desbloquea el acceso al CV de un trabajador descontando créditos.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\WorkerProfile $workerProfile
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlockCvView(Request $request, WorkerProfile $workerProfile, JobOffer $jobOffer)
    {
        $companyProfile = Auth::user()->companyProfile;

        if (!$companyProfile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes completar tu perfil de empresa para continuar.'
            ], 403);
        }

        if (!$jobOffer || $jobOffer->company_profile_id !== $companyProfile->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'La oferta de trabajo no es válida.'
            ], 403);
        }

        $alreadyUnlocked = CompanyCvViewLog::where('company_profile_id', $companyProfile->id)
            ->where('worker_profile_id', $workerProfile->id)
            ->where('job_offer_id', $jobOffer->id)
            ->exists();

        if ($alreadyUnlocked) {
            return response()->json([
                'status' => 'already',
                'message' => 'Ya tienes acceso a este CV.'
            ]);
        }

        try {
            DB::beginTransaction();

            $balance = $this->resolveResourceBalance($companyProfile);

            if ((int) $balance->available_cv_views <= 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'no-credits',
                    'message' => 'No tienes créditos disponibles para ver este CV. Compra más saldo en el catálogo de bonos.',
                    'purchase_url' => route('empresa.bonos.catalogo'),
                ], 402);
            }

            $balance->available_cv_views = max(0, (int) $balance->available_cv_views - 1);
            $balance->used_cv_views += 1;
            $balance->save();

            CompanyCvViewLog::create([
                'company_profile_id' => $companyProfile->id,
                'job_offer_id' => $jobOffer->id,
                'worker_profile_id' => $workerProfile->id,
                'match_score' => 0,
                'unlocked_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'unlocked',
                'message' => 'CV desbloqueado correctamente.',
                'available_cv_views' => $balance->available_cv_views,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desbloquear CV: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al intentar desbloquear este CV.'
            ], 500);
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
