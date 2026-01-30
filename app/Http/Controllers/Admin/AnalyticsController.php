<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Island;
use App\Models\JobOffer;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $islandId = $request->filled('island_id') ? (int) $request->input('island_id') : null;
        $selectedIsland = $islandId ? Island::find($islandId) : null;
        $searchTerm = trim((string) $request->input('query', '')) ?: null;
        $islands = Island::orderBy('name')->get(['id', 'name']);

        $payload = $this->gatherAnalyticsData($startDate, $endDate, $selectedIsland, $searchTerm);
        return view('admin.analytics.index', array_merge($payload, [
            'islands' => $islands,
            'selectedIsland' => $selectedIsland,
            'searchTerm' => $searchTerm,
        ]));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $islandId = $request->filled('island_id') ? (int) $request->input('island_id') : null;
        $selectedIsland = $islandId ? Island::find($islandId) : null;
        $searchTerm = trim((string) $request->input('query', '')) ?: null;

        $format = $request->input('format', 'pdf');
        $payload = $this->gatherAnalyticsData($startDate, $endDate, $selectedIsland, $searchTerm);

        if ($format === 'excel') {
            $csv = $this->buildAnalyticsCsv($payload);
            $filename = "analytics-{$startDate->format('Ymd')}-{$endDate->format('Ymd')}.csv";
            return Response::streamDownload(function () use ($csv) {
                echo $csv;
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);
        }

        return view('admin.analytics.export', array_merge($payload, [
            'selectedIsland' => $selectedIsland,
            'searchTerm' => $searchTerm,
        ]));
    }

    public function jobOfferDailyViews(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays(7)->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $jobOfferId = $request->has('job_offer_id') ? (int) $request->input('job_offer_id') : null;

        $islandId = $request->filled('island_id') ? (int) $request->input('island_id') : null;
        $selectedIsland = $islandId ? Island::find($islandId) : null;
        $searchTerm = trim((string) $request->input('query', '')) ?: null;
        $islands = Island::orderBy('name')->get(['id', 'name']);

        $availableOffers = JobOffer::where('is_published', true)
            ->whereIn('status', ['Publicado', 'Publicada'])
            ->when($selectedIsland, fn($query) => $query->where('island_id', $selectedIsland->id))
            ->when($searchTerm, fn($query) => $query->where('title', 'like', "%{$searchTerm}%"))
            ->orderBy('title')
            ->get(['id', 'title']);

        $dailyViews = $this->analyticsService->getJobOfferDailyViews(
            $startDate,
            $endDate,
            $jobOfferId,
            $selectedIsland,
            $searchTerm
        );
        $selectedOffer = $availableOffers->firstWhere('id', $jobOfferId);

        return view('admin.analytics.job_offer_daily', compact(
            'startDate',
            'endDate',
            'dailyViews',
            'availableOffers',
            'selectedOffer',
            'jobOfferId',
            'islandId',
            'selectedIsland',
            'islands',
            'searchTerm'
        ));
    }
    protected function gatherAnalyticsData(Carbon $startDate, Carbon $endDate, ?Island $island = null, ?string $term = null): array
    {
        $trafficData = $this->analyticsService->getTrafficSeries($startDate, $endDate, $island, $term);
        $registrations = $this->analyticsService->getRegistrationStats($startDate, $endDate);

        $daily30 = $this->analyticsService->getTrafficSeriesLastDays(30, $island, $term);
        $monthly12 = $this->analyticsService->getTrafficSeriesLastMonths(12, $island, $term);
        $yearly5 = $this->analyticsService->getTrafficSeriesLastYears(5, $island, $term);
        $timeMetrics = $this->analyticsService->getTimeMetrics();
        $jobOfferPerformance = $this->analyticsService->getJobOfferPerformance($startDate, $endDate, $island, $term);
        $visitOriginCounts = $this->analyticsService->getVisitOriginCounts($startDate, $endDate);
        $jobTimeComparisons = $this->analyticsService->getJobTimeComparisons();
        $municipalities = $this->analyticsService->getMunicipalityStats($startDate, $endDate);
        $timeDistribution = $this->analyticsService->getVisitTimeDistribution($startDate, $endDate);
        $visitVsApplications = $this->analyticsService->getVisitVsApplicationStats($startDate, $endDate);
        $shareActivity = $this->analyticsService->getShareActivity($startDate, $endDate);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'chartLabels' => $trafficData->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d/m/Y')),
            'chartViews' => $trafficData->pluck('views'),
            'chartVisitors' => $trafficData->pluck('visitors'),
            'summary' => [
                'total_views' => $trafficData->sum('views'),
                'total_visitors' => $trafficData->sum('visitors'),
                'avg_daily_views' => $trafficData->count() > 0 ? round($trafficData->avg('views'), 2) : 0,
                'new_workers' => $registrations['new_workers'],
                'new_companies' => $registrations['new_companies'],
            ],
            'geoStats' => $this->analyticsService->getGeographicStats(),
            'timeMetrics' => $timeMetrics,
            'topRoutes' => $this->analyticsService->getTopRoutes($startDate, $endDate, $island, $term),
            'topPages' => $this->analyticsService->getTopPages($startDate, $endDate, $island, $term),
            'userTypeStats' => $this->analyticsService->getUserTypeStats($startDate, $endDate),
            'daily30Labels' => $daily30->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->values(),
            'daily30Views' => $daily30->pluck('views'),
            'monthly12Labels' => $monthly12->pluck('date')->map(fn($d) => Carbon::parse($d)->format('m/Y'))->values(),
            'monthly12Views' => $monthly12->pluck('views'),
            'yearly5Labels' => $yearly5->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y'))->values(),
            'yearly5Views' => $yearly5->pluck('views'),
            'jobOfferPerformance' => $jobOfferPerformance,
            'workerProfileViews' => $this->analyticsService->getWorkerProfileViews($startDate, $endDate, $island, $term),
            'companyViews' => $this->analyticsService->getCompanyViews($startDate, $endDate, $island, $term),
            'visitOriginCounts' => $visitOriginCounts,
            'jobTimeComparisons' => $jobTimeComparisons,
            'municipalities' => $municipalities,
            'timeDistribution' => $timeDistribution,
            'visitVsApplications' => $visitVsApplications,
            'shareActivity' => $shareActivity,
            'analyticsModels' => [
                'basic' => [
                    'visitors_by_offer' => $jobOfferPerformance->take(3),
                    'avg_completion_days' => $timeMetrics['avg_days_to_close'] ?? 0,
                ],
                'medium' => [
                    'origin_counts' => $visitOriginCounts,
                    'time_comparisons' => $jobTimeComparisons,
                    'municipalities' => $municipalities,
                ],
                'advanced' => [
                    'day_hour_distribution' => $timeDistribution,
                    'visit_vs_applications' => $visitVsApplications,
                    'share_activity' => $shareActivity,
                ],
            ],
        ];
    }

    protected function buildAnalyticsCsv(array $payload): string
    {
        $lines = [];
        $lines[] = "Portal Analytics ({$payload['startDate']->format('Y-m-d')} to {$payload['endDate']->format('Y-m-d')})";
        $lines[] = '';
        $lines[] = 'Summary';
        $lines[] = 'Metric,Value';
        foreach ($payload['summary'] as $label => $value) {
            $lines[] = ucfirst(str_replace('_', ' ', $label)) . ',' . $value;
        }
        $lines[] = '';
        $lines[] = 'Top Job Offers';
        $lines[] = 'ID,Title,Company,Location,Views,Visitors';
        foreach ($payload['jobOfferPerformance'] as $offer) {
            $lines[] = "{$offer->id}," .
                "\"{$offer->title}\"," .
                "\"{$offer->company}\"," .
                "\"{$offer->location}\"," .
                "{$offer->views}," .
                "{$offer->visitors}";
        }
        $lines[] = '';
        $lines[] = 'Top Worker Profiles';
        $lines[] = 'ID,Name,Views,Visitors';
        foreach ($payload['workerProfileViews'] as $profile) {
            $lines[] = "{$profile->id},\"{$profile->name}\",{$profile->views},{$profile->visitors}";
        }
        $lines[] = '';
        $lines[] = 'Top Companies';
        $lines[] = 'ID,Company,Views,Visitors';
        foreach ($payload['companyViews'] as $company) {
            $lines[] = "{$company->id},\"{$company->company_name}\",{$company->views},{$company->visitors}";
        }
        return implode("\n", $lines);
    }
}
