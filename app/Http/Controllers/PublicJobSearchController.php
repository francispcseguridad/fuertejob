<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\Menu;
use App\Models\Sector;
use App\Models\SocialNetwork;
use App\Models\Island;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicJobSearchController extends Controller
{
    /**
     * Listado público de ofertas publicadas con filtros básicos.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'province' => $request->input('province'),
            'island' => $request->input('island'),
            'modality' => $request->input('modality'),
            'contract_type' => $request->input('contract_type'),
            'sector' => $request->input('sector'),
            'sectors' => array_filter((array) $request->input('sectors', []), fn($value) => $value !== null && $value !== ''),
        ];

        $islandSocialNetworks = collect();
        $islandLabel = null;
        $islandParam = trim((string) ($filters['island'] ?? ''));

        if ($islandParam !== '') {
            $normalizedParam = $this->normalizeIslandName($islandParam);
            if ($normalizedParam !== '') {
                $matchingIsland = Island::whereRaw('REPLACE(LOWER(name), " ", "") = ?', [$normalizedParam])
                    ->first();
                if ($matchingIsland) {
                    $islandSocialNetworks = SocialNetwork::where('is_active', true)
                        ->where('island_id', $matchingIsland->id)
                        ->orderBy('order')
                        ->get();
                    $islandLabel = $matchingIsland->name;
                }
            }
        }

        $query = JobOffer::query()
            ->whereIn('status', ['Publicado', 'Publicada'])
            ->where('is_published', true)
            ->with(['companyProfile.sectors.parent'])
            ->orderByDesc('created_at');

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('requirements', 'like', $term);
            });
        }

        if ($filters['province']) {
            $query->where('province', 'like', '%' . $filters['province'] . '%');
        }

        if ($filters['island']) {
            $query->where('island', 'like', '%' . $filters['island'] . '%');
        }

        $validModalities = ['presencial', 'remoto', 'hibrido'];
        if ($filters['modality'] && in_array($filters['modality'], $validModalities, true)) {
            $query->where('modality', $filters['modality']);
        }

        $validContracts = ['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'];
        if ($filters['contract_type'] && in_array($filters['contract_type'], $validContracts, true)) {
            $query->where('contract_type', $filters['contract_type']);
        }

        $selectedSectors = collect();
        $sectorInputs = collect($filters['sectors']);
        if ($filters['sector']) {
            $sectorInputs->push($filters['sector']);
        }

        $sectorInputs = $sectorInputs
            ->filter(fn($value) => $value !== null && $value !== '')
            ->values();

        $sectorIds = $sectorInputs
            ->filter(fn($value) => is_numeric($value))
            ->map(fn($value) => (int) $value)
            ->unique()
            ->values();

        $sectorSlugs = $sectorInputs
            ->reject(fn($value) => is_numeric($value))
            ->map(fn($value) => (string) $value)
            ->unique()
            ->values();

        if ($sectorIds->isNotEmpty() || $sectorSlugs->isNotEmpty()) {
            $selectedSectors = Sector::query()
                ->with('parent')
                ->where('is_active', true)
                ->where(function ($q) use ($sectorIds, $sectorSlugs) {
                    if ($sectorIds->isNotEmpty()) {
                        $q->whereIn('id', $sectorIds->all());
                    }

                    if ($sectorSlugs->isNotEmpty()) {
                        if ($sectorIds->isNotEmpty()) {
                            $q->orWhereIn('slug', $sectorSlugs->all());
                        } else {
                            $q->whereIn('slug', $sectorSlugs->all());
                        }
                    }
                })
                ->get();
        }

        $selectedSectorIds = $selectedSectors->pluck('id')->all();
        if (!empty($selectedSectorIds)) {
            $query->whereHas('companyProfile.sectors', function ($companySectorQuery) use ($selectedSectorIds) {
                $companySectorQuery->whereIn('sectors.id', $selectedSectorIds);
            });
        }

        $filters['sector'] = null;
        $filters['sectors'] = $selectedSectorIds;

        $jobOffers = $query->paginate(9)->withQueryString();

        $selectedSectors = $selectedSectors->map(function (Sector $sector) {
            return [
                'id' => $sector->id,
                'label' => $sector->parent
                    ? $sector->parent->name . ' · ' . $sector->name
                    : $sector->name,
            ];
        });

        $menus = Menu::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->where('location', 'primary')
            ->orderBy('order')
            ->get();

        $footer1 = Menu::whereNull('parent_id')->with('children')->where('is_active', true)->where('location', 'footer_1')->orderBy('order')->get();
        $footer2 = Menu::whereNull('parent_id')->with('children')->where('is_active', true)->where('location', 'footer_2')->orderBy('order')->get();
        $footer3 = Menu::whereNull('parent_id')->with('children')->where('is_active', true)->where('location', 'footer_3')->orderBy('order')->get();
        $socialNetworks = SocialNetwork::where('is_active', true)
            ->where('island_id', 0)
            ->orderBy('order')
            ->get();

        $canViewOffers = Auth::check() && Auth::user()->role === 'trabajador' && Auth::user()->hasVerifiedEmail();

        return view('public_jobs.index', compact(
            'jobOffers',
            'filters',
            'menus',
            'footer1',
            'footer2',
            'footer3',
            'socialNetworks',
            'canViewOffers',
            'selectedSectors',
            'islandSocialNetworks',
            'islandLabel'
        ));
    }

    public function show($id)
    {
        $jobOffer = JobOffer::with(['companyProfile', 'skills', 'tools', 'islandRelation'])->findOrFail($id);

        $menus = Menu::whereNull('parent_id')
            ->with('children')
            ->where('is_active', true)
            ->where('location', 'primary')
            ->orderBy('order')
            ->get();

        $footer1 = Menu::whereNull('parent_id')->with('children')->where('is_active', true)->where('location', 'footer_1')->orderBy('order')->get();
        $footer2 = Menu::whereNull('parent_id')->with('children')->where('is_active', true)->where('location', 'footer_2')->orderBy('order')->get();
        $footer3 = Menu::whereNull('parent_id')->with('children')->where('is_active', true)->where('location', 'footer_3')->orderBy('order')->get();
        $socialNetworks = SocialNetwork::where('is_active', true)
            ->where('island_id', 0)
            ->orderBy('order')
            ->get();

        return view('public_jobs.show', compact(
            'jobOffer',
            'menus',
            'footer1',
            'footer2',
            'footer3',
            'socialNetworks'
        ));
    }

    private function normalizeIslandName(string $name): string
    {
        $normalized = str_replace('+', ' ', $name);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);
        if ($normalized === '') {
            return '';
        }
        return str_replace(' ', '', mb_strtolower($normalized));
    }
}
