<?php

namespace App\Services;

use App\Models\AnalyticsLog;
use App\Models\JobOffer;
use App\Models\CandidateSelection;
use App\Models\User;
use App\Models\Island;
use App\Models\Application;
use App\Models\JobViewLog;
use App\Models\WorkerProfile;
use App\Models\CompanyProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class AnalyticsService
{
    /**
     * Obtiene el rendimiento general para el Admin.
     */
    public function getAdminDashboardStats()
    {
        return [
            'total_offers' => JobOffer::count(),
            'active_offers' => JobOffer::where('status', 'active')->count(),
            'filled_vacancies' => JobOffer::where('status', 'filled')->count(),
            'avg_cv_per_offer' => JobOffer::avg('applications_count') ?? 0,
            'cv_last_24h' => Application::where('created_at', '>=', now()->subDay())->count(),
        ];
    }

    /**
     * Calcula métricas de tiempo (Publicación -> Primer CV / Cierre).
     */
    public function getTimeMetrics()
    {
        $closedOffers = JobOffer::whereNotNull('closed_at')
            ->whereNotNull('published_at')
            ->get();

        $avgTimeToFirstCv = JobOffer::whereNotNull('first_cv_received_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, published_at, first_cv_received_at)) as avg_hours'))
            ->first()->avg_hours;

        $avgTimeToClose = $closedOffers->avg(function ($offer) {
            return $offer->published_at->diffInDays($offer->closed_at);
        });

        return [
            'avg_hours_to_first_cv' => round($avgTimeToFirstCv, 2),
            'avg_days_to_close' => round($avgTimeToClose, 2),
        ];
    }

    /**
     * Análisis geográfico por Isla.
     */
    public function getGeographicStats()
    {
        // Contar ofertas por island_id y por island (texto) para no perder datos antiguos
        $offersByName = JobOffer::select('island', DB::raw('COUNT(*) as total'))
            ->whereNotNull('island')
            ->groupBy('island')
            ->pluck('total', 'island');

        $offersById = JobOffer::select('island_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('island_id')
            ->groupBy('island_id')
            ->pluck('total', 'island_id');

        return Island::withCount(['candidates'])
            ->get()
            ->map(function ($island) use ($offersByName, $offersById) {
                $offersCount = ($offersById[$island->id] ?? 0) + ($offersByName[$island->name] ?? 0);

                $totalCandidates = User::whereNotNull('island_id')->count();
                $localPercentage = $totalCandidates > 0
                    ? ($island->candidates_count / $totalCandidates) * 100
                    : 0;

                return [
                    'island' => $island->name,
                    'offers_count' => $offersCount,
                    'candidates_count' => $island->candidates_count,
                    'local_market_share' => round($localPercentage, 2) . '%',
                ];
            });
    }

    /**
     * Datos para una empresa específica (Preparado para el sistema de bonos).
     */
    public function getCompanyStats($companyId, $planLevel = 'basic')
    {
        $query = JobOffer::where('company_id', $companyId);

        $stats = [
            'my_offers_count' => $query->count(),
            'total_applications' => $query->sum('applications_count'),
        ];

        // Si el bono es premium, añadimos más detalles
        if ($planLevel === 'premium') {
            $stats['detailed_views'] = $query->with('viewsLog')->get();
            $stats['conversion_rate'] = $stats['my_offers_count'] > 0
                ? ($stats['total_applications'] / $query->sum('views_count')) * 100
                : 0;
        }

        return $stats;
    }
    /**
     * Registra una petición HTTP para análisis de tráfico del portal.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null $routeName
     * @param array $routeParamsRaw
     * @return void
     */
    public function logRequest($request, ?string $routeName = null, array $routeParamsRaw = [])
    {
        // Ignorar assets comunes o rutas internas para mantener limpio el log
        if ($request->is([
            'favicon.ico',
            'robots.txt',
            '_debugbar/*',
            'debugbar/*',
            'storage/*',
            'img/*',
            'images/*',
            'css/*',
            'js/*',
            'build/*',
            'vendor/*',
        ])) {
            return;
        }

        // Garantiza un identificador aunque el navegador bloquee cookies/sesión (navegación privada).
        $sessionIdFromSession = $request->hasSession() ? $request->session()->getId() : null;
        $sessionIdFromCookie = $request->cookie('analytics_vid');

        $sessionId = $sessionIdFromSession ?: $sessionIdFromCookie;
        if (empty($sessionId)) {
            $sessionId = 'anon-' . Str::uuid()->toString();
        }

        // Persistir identificador alternativo en cookie por si el navegador bloquea la cookie de sesión.
        if (empty($sessionIdFromCookie)) {
            Cookie::queue('analytics_vid', $sessionId, 60 * 24 * 30); // 30 días
        }

        // Truncamiento preventivo para evitar fallos de base de datos (Data too long)
        $userAgent = $request->userAgent();
        if (strlen($userAgent) > 255) $userAgent = substr($userAgent, 0, 252) . '...';

        $url = $request->fullUrl();
        if (strlen($url) > 255) $url = substr($url, 0, 252) . '...';

        $referer = $request->headers->get('referer');
        if (strlen($referer) > 255) $referer = substr($referer, 0, 252) . '...';

        // Si no recibimos info de ruta (o está incompleta), la resolvemos aquí
        if ($routeName === null || empty($routeParamsRaw)) {
            $route = $request->route() ?? app('router')->current();
            $routeName = $routeName
                ?: app('router')->currentRouteName()
                ?: $route?->getName()
                ?: $route?->uri()
                ?: $request->path()
                ?: 'unknown';
            $routeParamsRaw = empty($routeParamsRaw)
                ? ($route?->parameters() ?? $route?->originalParameters() ?? [])
                : $routeParamsRaw;
        }

        [$routeParams, $relatedType, $relatedId] = $this->normalizeRouteData($routeParamsRaw);


        \App\Models\AnalyticsLog::create([
            'user_id' => auth()->id(), // Puede ser null
            'session_id' => $sessionId,
            'url' => $url,
            'route_name' => $routeName,
            'route_params' => !empty($routeParams) ? $routeParams : null,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'referer' => $referer,
        ]);
    }

    /**
     * Registra una vista única de una oferta (por sesión) y aumenta el contador agregado.
     */
    public function logJobOfferView(JobOffer $jobOffer, Request $request): void
    {
        $sessionId = $this->resolveSessionId($request);

        $existing = JobViewLog::where('job_offer_id', $jobOffer->id)
            ->where('session_id', $sessionId)
            ->first();

        if ($existing) {
            return; // Ya contamos la vista en esta sesión
        }

        JobViewLog::create([
            'job_offer_id' => $jobOffer->id,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'viewed_at' => now(),
        ]);

        $jobOffer->increment('views_count');
    }

    /**
     * Registra un click en "inscribirse" (aunque luego falle o no complete).
     */
    public function logJobOfferApplyClick(JobOffer $jobOffer): void
    {
        $jobOffer->increment('apply_clicks_count');
    }

    /**
     * Obtiene estadísticas de tráfico (logs).
     */
    public function getTrafficStats()
    {
        return [
            'total_views' => \App\Models\AnalyticsLog::count(),
            'views_today' => \App\Models\AnalyticsLog::whereDate('created_at', today())->count(),
            'unique_visitors_today' => \App\Models\AnalyticsLog::whereDate('created_at', today())->distinct('session_id')->count(),
        ];
    }
    /**
     * Obtiene estadísticas de registros en el periodo.
     */
    public function getRegistrationStats($startDate, $endDate)
    {
        return [
            'new_workers' => User::where('rol', 'trabajador')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'new_companies' => User::where('rol', 'empresa')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];
    }

    /**
     * Obtiene datos de tráfico para gráficos (Series temporales).
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getTrafficSeries($startDate, $endDate, ?Island $island = null, ?string $term = null)
    {
        $query = $this->baseAnalyticsLogQuery($island, $term);

        return $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as views'),
            DB::raw('count(distinct session_id) as visitors')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Serie diaria últimos N días (por defecto 30).
     */
    public function getTrafficSeriesLastDays(int $days = 30, ?Island $island = null, ?string $term = null)
    {
        $start = now()->subDays($days)->startOfDay();
        $end = now()->endOfDay();

        return $this->getTrafficSeries($start, $end, $island, $term);
    }

    /**
     * Serie mensual últimos N meses (por defecto 12).
     */
    public function getTrafficSeriesLastMonths(int $months = 12, ?Island $island = null, ?string $term = null)
    {
        $start = now()->subMonths($months - 1)->startOfMonth();
        $end = now()->endOfMonth();

        $query = $this->baseAnalyticsLogQuery($island, $term);

        return $query->select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m-01') as date"),
            DB::raw('count(*) as views'),
            DB::raw('count(distinct session_id) as visitors')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Serie anual últimos N años (por defecto 5).
     */
    public function getTrafficSeriesLastYears(int $years = 5, ?Island $island = null, ?string $term = null)
    {
        $start = now()->subYears($years - 1)->startOfYear();
        $end = now()->endOfYear();

        $query = $this->baseAnalyticsLogQuery($island, $term);

        return $query->select(
            DB::raw("DATE_FORMAT(created_at, '%Y-01-01') as date"),
            DB::raw('count(*) as views'),
            DB::raw('count(distinct session_id) as visitors')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Obtiene estadísticas de uso por tipo de usuario (Worker, Company, Admin, Anonymous).
     */
    public function getUserTypeStats($startDate, $endDate)
    {
        $stats = \App\Models\AnalyticsLog::leftJoin('users', 'analytics_logs.user_id', '=', 'users.id')
            ->select(
                DB::raw('COALESCE(users.rol, "anonymous") as role'),
                DB::raw('count(*) as views'),
                DB::raw('count(distinct session_id) as unique_sessions')
            )
            ->whereBetween('analytics_logs.created_at', [$startDate, $endDate])
            ->groupBy('role')
            ->get();

        return $stats;
    }

    /**
     * Obtiene las páginas más visitadas.
     */
    public function getTopPages($startDate, $endDate, ?Island $island = null, ?string $term = null, $limit = 10)
    {
        $query = $this->baseAnalyticsLogQuery($island, $term);

        return $query->select('url', DB::raw('count(*) as views'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('url')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtiene las rutas más visitadas (usa route_name y fallback a URL).
     */
    public function getTopRoutes($startDate, $endDate, ?Island $island = null, ?string $term = null, $limit = 15)
    {
        $query = $this->baseAnalyticsLogQuery($island, $term);

        return $query->select(
            DB::raw('COALESCE(route_name, url) as label'),
            DB::raw('count(*) as views'),
            DB::raw('count(distinct session_id) as visitors')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('label')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    public function getJobOfferPerformance($startDate, $endDate, ?Island $island = null, ?string $term = null, $limit = 10)
    {
        return AnalyticsLog::select(
            'job_offers.id',
            'job_offers.title',
            'job_offers.location',
            DB::raw('COALESCE(company_profiles.company_name, "Sin empresa") as company'),
            DB::raw('COUNT(*) as views'),
            DB::raw('COUNT(DISTINCT session_id) as visitors')
        )
            ->join('job_offers', 'job_offers.id', '=', 'analytics_logs.related_id')
            ->leftJoin('company_profiles', 'company_profiles.id', '=', 'job_offers.company_profile_id')
            ->where('analytics_logs.related_type', JobOffer::class)
            ->whereBetween('analytics_logs.created_at', [$startDate, $endDate])
            ->when($island, fn($q) => $q->where('job_offers.island_id', $island->id))
            ->when($term, fn($q) => $this->applySearchToQuery($q, $term, ['job_offers.title', 'company_profiles.company_name']))
            ->groupBy('job_offers.id', 'job_offers.title', 'job_offers.location', 'company_profiles.company_name')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }
    public function getJobOfferDailyViews($startDate, $endDate, ?int $jobOfferId = null, ?Island $island = null, ?string $term = null)
    {
        return AnalyticsLog::select(
            DB::raw('DATE(analytics_logs.created_at) as date'),
            'job_offers.id',
            'job_offers.title',
            'job_offers.location',
            DB::raw('COALESCE(company_profiles.company_name, "Sin empresa") as company'),
            DB::raw('COUNT(*) as views'),
            DB::raw('COUNT(DISTINCT session_id) as visitors')
        )
            ->join('job_offers', 'job_offers.id', '=', 'analytics_logs.related_id')
            ->leftJoin('company_profiles', 'company_profiles.id', '=', 'job_offers.company_profile_id')
            ->where('analytics_logs.related_type', JobOffer::class)
            ->when($jobOfferId, fn($query) => $query->where('job_offers.id', $jobOfferId))
            ->when($island, fn($query) => $query->where('job_offers.island_id', $island->id))
            ->when($term, fn($query) => $this->applySearchToQuery($query, $term, ['job_offers.title', 'company_profiles.company_name']))
            ->whereBetween('analytics_logs.created_at', [$startDate, $endDate])
            ->groupBy('date', 'job_offers.id', 'job_offers.title', 'job_offers.location', 'company_profiles.company_name')
            ->orderByDesc('date')
            ->orderByDesc('views')
            ->get();
    }
    public function getWorkerProfileViews($startDate, $endDate, ?Island $island = null, ?string $term = null, $limit = 10)
    {
        return AnalyticsLog::select(
            'worker_profiles.id',
            DB::raw("CONCAT(worker_profiles.first_name, ' ', worker_profiles.last_name) as name"),
            DB::raw('COUNT(*) as views'),
            DB::raw('COUNT(DISTINCT session_id) as visitors')
        )
            ->join('worker_profiles', 'worker_profiles.id', '=', 'analytics_logs.related_id')
            ->where('analytics_logs.related_type', WorkerProfile::class)
            ->whereBetween('analytics_logs.created_at', [$startDate, $endDate])
            ->when($island, fn($query) => $this->applyWorkerIslandFilter($query, $island))
            ->when($term, fn($query) => $this->applySearchToQuery($query, $term, ['worker_profiles.first_name', 'worker_profiles.last_name']))
            ->groupBy('worker_profiles.id', 'worker_profiles.first_name', 'worker_profiles.last_name')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    public function getCompanyViews($startDate, $endDate, ?Island $island = null, ?string $term = null, $limit = 10)
    {
        return AnalyticsLog::select(
            'company_profiles.id',
            'company_profiles.company_name',
            DB::raw('COUNT(*) as views'),
            DB::raw('COUNT(DISTINCT session_id) as visitors')
        )
            ->join('company_profiles', 'company_profiles.id', '=', 'analytics_logs.related_id')
            ->where('analytics_logs.related_type', CompanyProfile::class)
            ->when($island, fn($query) => $this->applyCompanyIslandFilter($query, $island))
            ->when($term, fn($query) => $this->applySearchToQuery($query, $term, ['company_profiles.company_name']))
            ->whereBetween('analytics_logs.created_at', [$startDate, $endDate])
            ->groupBy('company_profiles.id', 'company_profiles.company_name')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();
    }

    public function getJobTimeComparisons($limit = 5)
    {
        return JobOffer::whereNotNull('published_at')
            ->whereNotNull('first_cv_received_at')
            ->whereNotNull('closed_at')
            ->orderByDesc('closed_at')
            ->limit($limit)
            ->get(['id', 'title', 'published_at', 'first_cv_received_at', 'closed_at'])
            ->map(function (JobOffer $offer) {
                $firstDiff = $offer->published_at->diffInDays($offer->first_cv_received_at);
                $closeDiff = $offer->published_at->diffInDays($offer->closed_at);
                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'first_candidate_days' => $firstDiff,
                    'completion_days' => $closeDiff,
                ];
            });
    }

    public function getVisitOriginCounts($startDate, $endDate)
    {
        $logs = AnalyticsLog::select('referer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('route_name', 'worker.jobs.show')
            ->get();

        $grouped = $logs->groupBy(function ($log) {
            return $this->detectVisitChannel($log->referer);
        });

        return $grouped->map(function ($items, $channel) {
            return [
                'channel' => $channel,
                'views' => $items->count(),
            ];
        })->values();
    }

    protected function detectVisitChannel(?string $referer): string
    {
        if (!$referer) {
            return 'Directo';
        }

        $normalized = Str::lower($referer);

        if (Str::contains($normalized, ['/empleos', '/ofertas', '/candidatos/ofertas'])) {
            return 'Buscador de ofertas';
        }

        if (Str::contains($normalized, ['google.com', 'bing.com', 'duckduckgo.com'])) {
            return 'Canal externo';
        }

        return 'Directo / Externo';
    }

    protected function baseAnalyticsLogQuery(?Island $island, ?string $term)
    {
        $query = AnalyticsLog::query();
        $this->applyIslandFilterToLogQuery($query, $island);
        $this->applySearchToLogQuery($query, $term, ['url', 'route_name']);
        return $query;
    }

    protected function applyIslandFilterToLogQuery($query, ?Island $island)
    {
        if (!$island) {
            return $query;
        }

        $islandId = $island->id;
        $lowerIsland = Str::lower($island->name);

        return $query->where(function ($outer) use ($islandId, $lowerIsland) {
            $outer->where(function ($q) use ($islandId) {
                $q->where('analytics_logs.related_type', JobOffer::class)
                    ->whereExists(function ($exists) use ($islandId) {
                        $exists->select(DB::raw(1))
                            ->from('job_offers')
                            ->whereColumn('job_offers.id', 'analytics_logs.related_id')
                            ->where('job_offers.island_id', $islandId);
                    });
            })
                ->orWhere(function ($q) use ($islandId, $lowerIsland) {
                    $q->where('analytics_logs.related_type', WorkerProfile::class)
                        ->whereExists(function ($exists) use ($islandId, $lowerIsland) {
                            $exists->select(DB::raw(1))
                                ->from('worker_profiles')
                                ->whereColumn('worker_profiles.id', 'analytics_logs.related_id')
                                ->where(function ($inner) use ($islandId, $lowerIsland) {
                                    $inner->where('worker_profiles.island_id', $islandId)
                                        ->orWhereRaw('LOWER(worker_profiles.island) = ?', [$lowerIsland]);
                                });
                        });
                })
                ->orWhere(function ($q) use ($islandId) {
                    $q->where('analytics_logs.related_type', CompanyProfile::class)
                        ->whereExists(function ($exists) use ($islandId) {
                            $exists->select(DB::raw(1))
                                ->from('job_offers')
                                ->whereColumn('job_offers.company_profile_id', 'analytics_logs.related_id')
                                ->where('job_offers.island_id', $islandId);
                        });
                });
        });
    }

    public function getMunicipalityStats($startDate, $endDate)
    {
        $inscribed = CandidateSelection::select(
            DB::raw('COALESCE(worker_profiles.city, "Sin municipio") as city'),
            DB::raw('COUNT(*) as total')
        )
            ->join('worker_profiles', 'worker_profiles.id', '=', 'candidate_selections.worker_profile_id')
            ->whereBetween('candidate_selections.created_at', [$startDate, $endDate])
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $viewers = AnalyticsLog::select(
            DB::raw('COALESCE(worker_profiles.city, "Sin municipio") as city'),
            DB::raw('COUNT(*) as total')
        )
            ->leftJoin('worker_profiles', 'worker_profiles.user_id', '=', 'analytics_logs.user_id')
            ->whereBetween('analytics_logs.created_at', [$startDate, $endDate])
            ->where('analytics_logs.route_name', 'worker.jobs.show')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'inscritos' => $inscribed,
            'visualizadores' => $viewers,
        ];
    }

    public function getVisitTimeDistribution($startDate, $endDate)
    {
        $byDay = AnalyticsLog::select(
            DB::raw('DAYNAME(created_at) as day'),
            DB::raw('DAYOFWEEK(created_at) as day_index'),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('route_name', 'worker.jobs.show')
            ->groupBy('day_index', 'day')
            ->orderBy('day_index')
            ->get()
            ->map(fn($row) => ['day' => $row->day, 'count' => $row->total]);

        $byHour = AnalyticsLog::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('route_name', 'worker.jobs.show')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($row) => ['hour' => $row->hour, 'count' => $row->total]);

        return [
            'by_day' => $byDay,
            'by_hour' => $byHour,
        ];
    }

    public function getVisitVsApplicationStats($startDate, $endDate)
    {
        $views = AnalyticsLog::whereBetween('created_at', [$startDate, $endDate])
            ->where('route_name', 'worker.jobs.show')
            ->count();

        $uniqueVisitors = AnalyticsLog::whereBetween('created_at', [$startDate, $endDate])
            ->where('route_name', 'worker.jobs.show')
            ->distinct('session_id')
            ->count('session_id');

        $applications = CandidateSelection::whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            'views' => $views,
            'unique_visitors' => $uniqueVisitors,
            'applications' => $applications,
            'conversion_rate' => $views > 0 ? round(($applications / $views) * 100, 2) : 0,
        ];
    }

    public function getShareActivity($startDate, $endDate)
    {
        $shareSources = [
            'facebook.com/sharer',
            'twitter.com/intent',
            'linkedin.com/sharing',
            'api.whatsapp.com',
        ];

        $query = AnalyticsLog::whereBetween('created_at', [$startDate, $endDate])
            ->where('route_name', 'worker.jobs.show')
            ->where(function ($q) use ($shareSources) {
                foreach ($shareSources as $source) {
                    $q->orWhere('referer', 'like', "%{$source}%");
                }
            });

        return [
            'count' => $query->count(),
            'sources' => $shareSources,
        ];
    }

    protected function applySearchToLogQuery($query, ?string $term, array $columns)
    {
        if (!$term) {
            return $query;
        }

        $like = '%' . Str::lower(trim($term)) . '%';
        return $query->where(function ($inner) use ($columns, $like) {
            foreach ($columns as $column) {
                $inner->orWhere(DB::raw("LOWER({$column})"), 'like', $like);
            }
        });
    }

    protected function applySearchToQuery($query, ?string $term, array $columns)
    {
        if (!$term) {
            return $query;
        }

        $like = '%' . Str::lower(trim($term)) . '%';
        return $query->where(function ($inner) use ($columns, $like) {
            foreach ($columns as $column) {
                $inner->orWhere(DB::raw("LOWER({$column})"), 'like', $like);
            }
        });
    }

    protected function applyWorkerIslandFilter($query, Island $island)
    {
        $lowerIsland = Str::lower($island->name);
        return $query->where(function ($inner) use ($island, $lowerIsland) {
            $inner->where('worker_profiles.island_id', $island->id)
                ->orWhereRaw('LOWER(worker_profiles.island) = ?', [$lowerIsland]);
        });
    }

    protected function applyCompanyIslandFilter($query, Island $island)
    {
        $islandId = $island->id;
        return $query->whereExists(function ($exists) use ($islandId) {
            $exists->select(DB::raw(1))
                ->from('job_offers')
                ->whereColumn('job_offers.company_profile_id', 'analytics_logs.related_id')
                ->where('job_offers.island_id', $islandId);
        });
    }

    /**
     * Genera/recupera un identificador de sesión usable para tracking anónimo.
     */
    protected function resolveSessionId(Request $request): string
    {
        $sessionIdFromSession = $request->hasSession() ? $request->session()->getId() : null;
        $sessionIdFromCookie = $request->cookie('analytics_vid');
        $sessionId = $sessionIdFromSession ?: $sessionIdFromCookie;

        if (empty($sessionId)) {
            $sessionId = 'anon-' . Str::uuid()->toString();
        }

        if (empty($sessionIdFromCookie)) {
            Cookie::queue('analytics_vid', $sessionId, 60 * 24 * 30);
        }

        return $sessionId;
    }

    /**
     * Normaliza parámetros de ruta y detecta el modelo asociado.
     *
     * @param array $routeParamsRaw
     * @return array [array $routeParams, ?string $relatedType, ?int $relatedId]
     */
    protected function normalizeRouteData(array $routeParamsRaw): array
    {
        $routeParams = [];
        $relatedType = null;
        $relatedId = null;

        foreach ($routeParamsRaw as $key => $value) {
            if ($value instanceof Model) {
                $routeParams[$key] = $value->getKey();
                if (!$relatedType && !$relatedId) {
                    $relatedType = get_class($value);
                    $relatedId = $value->getKey();
                }
            } else {
                $routeParams[$key] = $value;
            }
        }

        return [$routeParams, $relatedType, $relatedId];
    }
}
