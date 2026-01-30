@extends('layouts.app')
@section('title', 'Buscador de Candidatos: ' . $jobOffer->title)

@section('content')
    <style>
        :root {
            --sidebar-width: 320px;
            --primary-soft: rgba(13, 110, 253, 0.08);
        }

        .search-layout {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        /* Barra Lateral de Filtros */
        .filter-sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            position: sticky;
            top: 2rem;
            background: #fff;
            border-radius: 20px;
            border: 1px solid #eef2f7;
            padding: 1.5rem;
        }

        .filter-group {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .filter-group:last-child {
            border-bottom: none;
        }

        .filter-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 1rem;
            display: block;
        }

        /* Grid de Resultados */
        .results-column {
            flex-grow: 1;
        }

        /* Card de Candidato Estilizada */
        /* Card de Candidato Estilizada Premium */
        .candidate-card-wrapper {
            transition: all 0.3s ease;
        }

        .candidate-card {
            border: 1px solid #f1f5f9;
            border-radius: 20px;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 1.25rem;
            gap: 1.5rem;
            width: 100%;
        }

        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.12);
            border-color: #0d6efd40;
        }

        .candidate-card.selected {
            background-color: #f0f7ff;
            border-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
        }

        /* Score-Hexagon/Badge Premium */
        .score-visual {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            position: relative;
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #eef2f7;
        }

        .score-visual.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }

        .score-visual.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
        }

        .score-visual.secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
            border: none;
        }

        .score-number {
            font-size: 1.25rem;
            font-weight: 850;
            line-height: 1;
        }

        .score-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.9;
        }

        /* Content Area */
        .candidate-info-main {
            flex: 1;
            min-width: 0;
        }

        .candidate-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .candidate-meta-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 0.85rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Stats & Tags */
        .candidate-details {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 0 1.5rem;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }

        .stat-diamond {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.02em;
        }

        .skill-tag {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.8rem;
            background: #f1f5f9;
            color: #475569;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .skill-tag:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .skill-tag.unlocked {
            background: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        /* Actions */
        .candidate-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 150px;
        }

        .btn-premium-view {
            background: #1e293b;
            color: white;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
        }

        .btn-premium-view:hover {
            background: #0f172a;
            color: white;
            transform: scale(1.02);
        }

        .btn-premium-action {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }

        .btn-select {
            background: #0d6efd;
            color: white;
        }

        .btn-select:hover {
            background: #0b5ed7;
            transform: scale(1.02);
        }

        .btn-deselect {
            background: #fee2e2;
            color: #ef4444;
        }

        .btn-deselect:hover {
            background: #fecaca;
        }

        .btn-unlock {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-unlock:hover {
            background: #e2e8f0;
        }

        @media (max-width: 1200px) {
            .candidate-details {
                gap: 1rem;
                padding: 0 1rem;
            }
        }

        @media (max-width: 992px) {
            .candidate-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .candidate-details {
                border: none;
                padding: 0;
                width: 100%;
                justify-content: space-between;
                border-top: 1px solid #f1f5f9;
                padding-top: 1rem;
            }

            .candidate-actions {
                width: 100%;
                flex-direction: row;
            }

            .candidate-actions>* {
                flex: 1;
            }
        }
    </style>

    <div class="container-fluid px-lg-5 py-4">

        <!-- Header Superior -->
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item small"><a href="{{ route('empresa.ofertas.index') }}">Ofertas</a></li>
                        <li class="breadcrumb-item small active">Buscador de Talento</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-dark mb-1">Candidatos para: <span
                        class="text-primary">{{ $jobOffer->title }}</span></h1>
                <p class="text-muted mb-0">Hemos encontrado <span class="fw-bold"
                        id="totalCount">{{ $matchedWorkers->count() }}</span> perfiles compatibles.</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('empresa.candidatos.seleccionados.index', $jobOffer) }}"
                    class="btn btn-dark rounded-pill px-4 shadow-sm me-2">
                    <i class="bi bi-people-fill me-2"></i>Seleccionados (<span
                        id="selectedCountSpan">{{ $selectedCandidatesCount ?? 0 }}</span>)
                </a>
                <a href="{{ route('empresa.ofertas.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>

        <div class="search-layout">

            <!-- Sidebar de Filtros -->
            <aside class="filter-sidebar shadow-sm">
                @php
                    $canonicalIslands = [
                        'Tenerife',
                        'Gran Canaria',
                        'Lanzarote',
                        'Fuerteventura',
                        'La Palma',
                        'La Gomera',
                        'El Hierro',
                    ];
                    $matchedIslands = $matchedWorkers
                        ->flatMap(fn($worker) => collect([$worker->island, $worker->current_location]))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                    $islandOptions = collect(array_merge($canonicalIslands, $matchedIslands))
                        ->filter()
                        ->unique()
                        ->values();

                    $offerSkillSuggestions = $jobOffer->skills?->pluck('name')->filter()->values() ?? collect();
                    $offerToolSuggestions = $jobOffer->tools?->pluck('name')->filter()->values() ?? collect();
                    $offerLanguageSuggestions = collect(json_decode($jobOffer->required_languages ?? '[]', true))
                        ->filter()
                        ->values();

                    $matchedSkillSuggestions = $matchedWorkers
                        ->flatMap(fn($worker) => $worker->skills?->pluck('name') ?? collect())
                        ->filter()
                        ->unique()
                        ->values();
                    $matchedToolSuggestions = $matchedWorkers
                        ->flatMap(fn($worker) => $worker->tools?->pluck('name') ?? collect())
                        ->filter()
                        ->unique()
                        ->values();
                    $matchedLanguageSuggestions = $matchedWorkers
                        ->flatMap(fn($worker) => $worker->languages?->pluck('name') ?? collect())
                        ->filter()
                        ->unique()
                        ->values();

                    $languageTokenAliases = [
                        'english' => 'ingles',
                        'spanish' => 'espanol',
                        'german' => 'aleman',
                        'french' => 'frances',
                        'portuguese' => 'portugues',
                        'italian' => 'italiano',
                        'chinese' => 'chino',
                        'japanese' => 'japones',
                    ];
                    $languageCanonicalLabels = [
                        'espanol' => 'Español',
                        'ingles' => 'Inglés',
                        'aleman' => 'Alemán',
                        'frances' => 'Francés',
                        'portugues' => 'Portugués',
                        'italiano' => 'Italiano',
                        'chino' => 'Chino',
                        'japones' => 'Japonés',
                    ];

                    $normalizeLanguageLabel = function ($value) {
                        $label = trim((string) $value);
                        if ($label === '') {
                            return '';
                        }
                        $label = preg_replace('/\\s*\\([^)]*\\)\\s*/', ' ', $label);
                        $label = preg_replace('/\\s+/', ' ', $label);
                        return trim((string) $label);
                    };
                    $languageToken = function ($value) use ($normalizeLanguageLabel, $languageTokenAliases) {
                        $label = $normalizeLanguageLabel($value);
                        if ($label === '') {
                            return '';
                        }
                        $ascii = \Illuminate\Support\Str::ascii($label);
                        $ascii = preg_replace('/[^A-Za-z]+/', ' ', (string) $ascii);
                        $ascii = preg_replace('/\\s+/', ' ', trim((string) $ascii));
                        $firstWord = strtok($ascii, ' ') ?: '';
                        $token = mb_strtolower($firstWord, 'UTF-8');
                        return $languageTokenAliases[$token] ?? $token;
                    };

                    $languageLabelByToken = [];
                    $languageTokensPerWorker = $matchedWorkers->map(function ($worker) use (
                        $languageToken,
                        $normalizeLanguageLabel,
                        $languageCanonicalLabels,
                        &$languageLabelByToken,
                    ) {
                        $names = $worker->languages?->pluck('name') ?? collect();
                        return collect($names)
                            ->map(function ($name) use (
                                $languageToken,
                                $normalizeLanguageLabel,
                                $languageCanonicalLabels,
                                &$languageLabelByToken,
                            ) {
                                $label = $normalizeLanguageLabel($name);
                                $token = $languageToken($label);
                                if ($token !== '' && !isset($languageLabelByToken[$token])) {
                                    $languageLabelByToken[$token] = $languageCanonicalLabels[$token] ?? $label;
                                }
                                return $token;
                            })
                            ->filter()
                            ->unique()
                            ->values();
                    });
                    $languageCounts = $languageTokensPerWorker->flatten()->countBy()->sortDesc();
                    $popularLanguages = $languageCounts
                        ->take(5)
                        ->map(
                            fn($count, $token) => [
                                'token' => $token,
                                'label' => $languageLabelByToken[$token] ?? ucfirst($token),
                                'count' => $count,
                            ],
                        )
                        ->values();

                    $normalizeLocalityLabel = function ($value) {
                        $label = trim((string) $value);
                        $label = preg_replace('/\\s+/', ' ', $label);
                        return trim((string) $label);
                    };
                    $localityKey = function ($value) use ($normalizeLocalityLabel) {
                        $label = $normalizeLocalityLabel($value);
                        if ($label === '') {
                            return '';
                        }
                        $ascii = \Illuminate\Support\Str::ascii($label);
                        return preg_replace('/[^a-z0-9]/', '', strtolower((string) $ascii));
                    };

                    $localityLabelByKey = [];
                    $localityKeysPerWorker = $matchedWorkers
                        ->map(function ($worker) use ($normalizeLocalityLabel, $localityKey, &$localityLabelByKey) {
                            $label = $normalizeLocalityLabel($worker->city ?? ($worker->current_location ?? ''));
                            $key = $localityKey($label);
                            if ($key !== '' && !isset($localityLabelByKey[$key])) {
                                $localityLabelByKey[$key] = $label;
                            }
                            return $key;
                        })
                        ->filter()
                        ->values();
                    $localityCounts = $localityKeysPerWorker->countBy()->sortDesc();
                    $popularLocalities = $localityCounts
                        ->take(5)
                        ->map(
                            fn($count, $key) => [
                                'key' => $key,
                                'label' => $localityLabelByKey[$key] ?? $key,
                                'count' => $count,
                            ],
                        )
                        ->values();
                    $localitySuggestions = collect($localityLabelByKey)
                        ->values()
                        ->filter()
                        ->unique()
                        ->sort()
                        ->take(200)
                        ->values();

                    $educationLevels = collect([
                        [
                            'token' => 'doctorado',
                            'label' => 'Doctorado',
                            'rank' => 6,
                            'patterns' => ['doctorado', 'phd'],
                        ],
                        [
                            'token' => 'master',
                            'label' => 'Máster',
                            'rank' => 5,
                            'patterns' => ['master', 'máster', 'msc', 'mba', 'posgrado', 'postgrado'],
                        ],
                        [
                            'token' => 'grado',
                            'label' => 'Grado universitario',
                            'rank' => 4,
                            'patterns' => ['grado', 'licenciatura', 'diplomatura', 'ingenier', 'universit'],
                        ],
                        [
                            'token' => 'ciclo_formativo',
                            'label' => 'Ciclo formativo / Grado superior',
                            'rank' => 3,
                            'patterns' => [
                                'grado superior',
                                'ciclo formativo',
                                'fp',
                                'formacion profesional',
                                'formación profesional',
                                'tecnico superior',
                                'técnico superior',
                            ],
                        ],
                        [
                            'token' => 'bachillerato',
                            'label' => 'Bachillerato',
                            'rank' => 2,
                            'patterns' => ['bachiller'],
                        ],
                        [
                            'token' => 'eso',
                            'label' => 'ESO',
                            'rank' => 1,
                            'patterns' => ['eso', 'secundaria', 'educacion secundaria', 'educación secundaria'],
                        ],
                    ]);
                    $educationByToken = $educationLevels->keyBy('token');
                    $educationLevelFromWorker = function ($worker) use ($educationLevels, $educationByToken) {
                        $degrees = $worker->educations?->pluck('degree') ?? collect();
                        $combined = trim($degrees->filter()->implode(' | '));
                        if ($combined === '') {
                            return ['token' => 'no_especificado', 'label' => 'No especificado', 'rank' => 0];
                        }

                        $haystack = mb_strtolower((string) \Illuminate\Support\Str::ascii($combined), 'UTF-8');
                        $best = ['token' => 'otros', 'label' => 'Otros', 'rank' => 0];
                        foreach ($educationLevels as $level) {
                            foreach ($level['patterns'] as $pattern) {
                                $needle = mb_strtolower((string) \Illuminate\Support\Str::ascii($pattern), 'UTF-8');
                                if ($needle !== '' && str_contains($haystack, $needle)) {
                                    if ($level['rank'] > $best['rank']) {
                                        $best = [
                                            'token' => $level['token'],
                                            'label' => $level['label'],
                                            'rank' => $level['rank'],
                                        ];
                                    }
                                    break;
                                }
                            }
                        }

                        return $best;
                    };
                    $educationLevelPerWorker = $matchedWorkers
                        ->map(fn($worker) => $educationLevelFromWorker($worker)['token'])
                        ->filter()
                        ->values();
                    $educationCounts = $educationLevelPerWorker->countBy()->sortDesc();
                    $popularEducationLevels = $educationCounts
                        ->take(5)
                        ->map(
                            fn($count, $token) => [
                                'token' => $token,
                                'label' =>
                                    $educationByToken->get($token)['label'] ??
                                    (null ?? ($token === 'otros' ? 'Otros' : 'No especificado')),
                                'count' => $count,
                            ],
                        )
                        ->values();
                    $educationSuggestions = $educationLevels
                        ->pluck('label')
                        ->merge(collect(['Otros', 'No especificado']))
                        ->unique()
                        ->values();

                    $skillSuggestions = $offerSkillSuggestions
                        ->merge($matchedSkillSuggestions)
                        ->filter()
                        ->unique()
                        ->values();
                    $toolSuggestions = $offerToolSuggestions
                        ->merge($matchedToolSuggestions)
                        ->filter()
                        ->unique()
                        ->values();
                    $languageSuggestions = $offerLanguageSuggestions
                        ->merge($matchedLanguageSuggestions)
                        ->filter()
                        ->unique()
                        ->values();
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Filtros</h5>
                    <button class="btn btn-link btn-sm text-decoration-none p-0" id="resetFilters">Limpiar todo</button>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Búsqueda Directa</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="searchInput"
                            placeholder="Nombre o palabra clave...">
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Ubicación (Islas)</label>
                    <select class="form-select form-select-sm mb-2" id="islaFilter">
                        <option value="">Todas las islas</option>
                        @foreach ($islandOptions as $island)
                            <option value="{{ mb_strtolower($island, 'UTF-8') }}">{{ $island }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Ubicación (Localidad)</label>
                    <input class="form-control form-control-sm mb-2" id="localityInput" list="localitySuggestions"
                        placeholder="Escribe una localidad...">
                    <datalist id="localitySuggestions">
                        @foreach ($localitySuggestions as $locality)
                            <option value="{{ $locality }}"></option>
                        @endforeach
                    </datalist>
                    @if ($popularLocalities->count())
                        <div class="small text-muted fw-semibold mb-1">Más usadas</div>
                        @foreach ($popularLocalities as $locality)
                            <div class="form-check">
                                <input class="form-check-input popular-locality-checkbox" type="checkbox"
                                    id="popularLocality{{ $loop->index }}" value="{{ $locality['key'] }}">
                                <label class="form-check-label small" for="popularLocality{{ $loop->index }}">
                                    {{ $locality['label'] }} ({{ $locality['count'] }})
                                </label>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="filter-group">
                    <label class="filter-label">Filtros Avanzados</label>

                    <div class="mb-3">
                        <label class="small text-muted fw-semibold mb-1 d-block">Nivel de estudios</label>
                        <input class="form-control form-control-sm" id="educationInput" list="educationSuggestions"
                            placeholder="Ej: Bachillerato...">
                        <datalist id="educationSuggestions">
                            @foreach ($educationSuggestions as $education)
                                <option value="{{ $education }}"></option>
                            @endforeach
                        </datalist>
                        @if ($popularEducationLevels->count())
                            <div class="mt-2">
                                <div class="small text-muted fw-semibold mb-1">Más usados</div>
                                @foreach ($popularEducationLevels as $education)
                                    <div class="form-check">
                                        <input class="form-check-input popular-education-checkbox" type="checkbox"
                                            id="popularEducation{{ $loop->index }}" value="{{ $education['token'] }}">
                                        <label class="form-check-label small" for="popularEducation{{ $loop->index }}">
                                            {{ $education['label'] }} ({{ $education['count'] }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-semibold mb-1 d-block">Idiomas</label>

                        <input class="form-control form-control-sm" id="languagesInput" list="languagesSuggestions"
                            placeholder="Ej: Inglés (Enter para añadir)">
                        <datalist id="languagesSuggestions">
                            @foreach ($languageSuggestions as $language)
                                <option value="{{ $language }}"></option>
                            @endforeach
                        </datalist>
                        <div class="mt-2 d-flex flex-wrap gap-1" id="languagesChips"></div>
                        @if ($popularLanguages->count())
                            <div class="mb-2">
                                <div class="small text-muted fw-semibold mb-1">Más usados</div>
                                @foreach ($popularLanguages as $language)
                                    <div class="form-check">
                                        <input class="form-check-input popular-language-checkbox" type="checkbox"
                                            id="popularLanguage{{ $loop->index }}" value="{{ $language['token'] }}">
                                        <label class="form-check-label small" for="popularLanguage{{ $loop->index }}">
                                            {{ $language['label'] }} ({{ $language['count'] }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-semibold mb-1 d-block">Habilidades</label>
                        <input class="form-control form-control-sm" id="skillsInput" list="skillsSuggestions"
                            placeholder="Ej: PHP (Enter para añadir)">
                        <datalist id="skillsSuggestions">
                            @foreach ($skillSuggestions as $skill)
                                <option value="{{ $skill }}"></option>
                            @endforeach
                        </datalist>
                        <div class="mt-2 d-flex flex-wrap gap-1" id="skillsChips"></div>
                    </div>

                    <div>
                        <label class="small text-muted fw-semibold mb-1 d-block">Herramientas</label>
                        <input class="form-control form-control-sm" id="toolsInput" list="toolsSuggestions"
                            placeholder="Ej: Excel (Enter para añadir)">
                        <datalist id="toolsSuggestions">
                            @foreach ($toolSuggestions as $tool)
                                <option value="{{ $tool }}"></option>
                            @endforeach
                        </datalist>
                        <div class="mt-2 d-flex flex-wrap gap-1" id="toolsChips"></div>
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Estado de Selección</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="selectFilter" id="filterAny"
                            value="any" checked>
                        <label class="form-check-label small" for="filterAny">Todos los perfiles</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="selectFilter" id="filterSelected"
                            value="selected">
                        <label class="form-check-label small" for="filterSelected">Solo seleccionados</label>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="button" class="btn btn-primary rounded-pill" id="applyFiltersBtn">
                        <i class="bi bi-funnel me-2"></i>Filtrar
                    </button>
                </div>
            </aside>

            <!-- Resultados -->
            <main class="results-column">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted mb-0" for="perPageSelect">Por página</label>
                        <select class="form-select form-select-sm w-auto" id="perPageSelect">
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Todos</option>
                        </select>
                    </div>
                    <nav aria-label="Paginación">
                        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                    </nav>
                </div>
                <div class="row g-4" id="candidatesGrid">
                    @forelse ($matchedWorkers as $worker)
                        @php
                            $isSelected = (bool) $worker->is_selected;
                            $scorePercentage = ($worker->match_score / $totalWeight) * 100;
                            $colorClass =
                                $scorePercentage >= 80 ? 'success' : ($scorePercentage >= 50 ? 'warning' : 'secondary');
                            $educationLevel = $educationLevelFromWorker($worker);
                        @endphp

                        <div class="col-12 candidate-card-wrapper"
                            data-name="{{ $worker->cv_unlocked ? strtolower($worker->user->name ?? '') : 'perfil confidencial' }}"
                            @php
$locationParts = collect([
                                    $worker->city,
                                    $worker->current_location,
                                    $worker->island,
                                    $worker->province,
                                    $worker->country,
                                ])->filter();
                                $locationRaw = $locationParts->implode(' ');
                                $locationSearch = mb_strtolower($locationRaw, 'UTF-8');
                                $locationKey = preg_replace(
                                    '/[^a-z0-9]/',
                                    '',
                                    strtolower(\Illuminate\Support\Str::ascii((string) $locationRaw)),
                                );

                                $localityRaw = (string) ($worker->city ?? $worker->current_location ?? '');
                                $localitySearch = mb_strtolower($localityRaw, 'UTF-8');
                                $workerLocalityKey = preg_replace(
                                    '/[^a-z0-9]/',
                                    '',
                                    strtolower(\Illuminate\Support\Str::ascii((string) $localityRaw)),
                                );

                                $workerIslandRaw = (string) ($worker->island ?? $worker->current_location ?? '');
                                $workerIsland = mb_strtolower($workerIslandRaw, 'UTF-8');
                                $workerIslandKey = preg_replace(
                                    '/[^a-z0-9]/',
                                    '',
                                    strtolower(\Illuminate\Support\Str::ascii((string) $workerIslandRaw)),
                                );
                                $lower = fn($value) => mb_strtolower(trim((string) $value), 'UTF-8');

                                $workerSkillNames = $worker->skills?->pluck('name')->filter()->map($lower)->values() ?? collect();
                                $workerToolNames = $worker->tools?->pluck('name')->filter()->map($lower)->values() ?? collect();
                                $workerLanguageNames = $worker->languages?->pluck('name')->filter()->map($lower)->values() ?? collect(); @endphp
                            data-location="{{ $locationSearch }}" data-location-key="{{ $locationKey }}"
                            data-locality="{{ $localitySearch }}" data-locality-key="{{ $workerLocalityKey }}"
                            data-island="{{ $workerIsland }}" data-island-key="{{ $workerIslandKey }}"
                            data-score="{{ $worker->match_score }}" data-skills="{{ $worker->skill_matches }}"
                            data-tools="{{ $worker->tool_matches }}" data-skills-names='@json($workerSkillNames)'
                            data-tools-names='@json($workerToolNames)'
                            data-languages-names='@json($workerLanguageNames)'
                            data-education-level-token="{{ $educationLevel['token'] }}"
                            data-education-level-label="{{ $educationLevel['label'] }}"
                            data-selected="{{ $isSelected ? 'true' : 'false' }}">

                            <div class="candidate-card {{ $isSelected ? 'selected' : '' }}">
                                <!-- Visual Score -->
                                <div class="score-visual {{ $colorClass }}">
                                    <span class="score-number">{{ number_format($scorePercentage, 0) }}%</span>
                                    <span class="score-label">Match</span>
                                </div>

                                <!-- Main Information -->
                                <div class="candidate-info-main">
                                    @php
                                        $displayLanguages = ($worker->languages ?? collect())
                                            ->filter(fn($language) => !empty($language?->name))
                                            ->map(function ($language) {
                                                $name = (string) $language->name;
                                                $level = $language->pivot->level ?? null;

                                                return !empty($level) ? $name . ' (' . $level . ')' : $name;
                                            })
                                            ->values();
                                        $languagesInline = $displayLanguages->implode(', ');
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="candidate-name mb-0">
                                            {{ $worker->cv_unlocked ? $worker->user->name ?? 'Usuario' : 'Candidato #' . $worker->id }}
                                        </h5>
                                        @if ($isSelected)
                                            <span class="badge bg-primary rounded-pill small"
                                                title="Candidato Seleccionado">
                                                <i class="bi bi-star-fill"></i>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="candidate-meta-row mb-2">
                                        <div class="meta-item">
                                            <i class="bi bi-geo-alt text-muted"></i>
                                            {{ $worker->city ?? 'Provincia' }},
                                            {{ $worker->current_location ?? ($worker->island ?? ($worker->province ?? ($worker->country ?? 'Canarias'))) }}
                                        </div>
                                        <div class="meta-item">
                                            <i class="bi bi-briefcase text-muted"></i>
                                            {{ ucfirst($worker->preferred_modality) }}
                                        </div>
                                        <div class="meta-item">
                                            <i class="bi bi-mortarboard text-muted"></i>
                                            {{ $educationLevel['label'] ?? 'No especificado' }}
                                        </div>
                                        <div class="meta-item"
                                            @if ($languagesInline !== '') title="{{ $languagesInline }}" @endif>
                                            <i class="bi bi-translate text-muted"></i>
                                            {{ $languagesInline !== '' ? 'Idiomas: ' . \Illuminate\Support\Str::limit($languagesInline, 45) : 'Idiomas: No especificado' }}
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-1">
                                        @if ($worker->cv_unlocked)
                                            <span class="skill-tag unlocked">
                                                <i class="bi bi-unlock-fill"></i> CV Desbloqueado
                                            </span>
                                        @endif

                                        @if ($displayLanguages->count())
                                            @foreach ($displayLanguages->take(3) as $language)
                                                <span class="skill-tag">
                                                    <i class="bi bi-translate opacity-50"></i> {{ $language }}
                                                </span>
                                            @endforeach
                                            @if ($displayLanguages->count() > 3)
                                                <span class="skill-tag opacity-75">
                                                    +{{ $displayLanguages->count() - 3 }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Match Stats -->
                                <div class="candidate-details d-none d-md-flex">
                                    <div class="stat-diamond">
                                        <span class="stat-value text-primary">{{ $worker->skill_matches }}</span>
                                        <span class="stat-label">Skills</span>
                                    </div>
                                    <div class="stat-diamond">
                                        <span class="stat-value text-info">{{ $worker->tool_matches }}</span>
                                        <span class="stat-label">Herram.</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="candidate-actions">
                                    <a href="{{ route('empresa.trabajadores.show', ['workerProfile' => $worker, 'jobOffer' => $jobOffer]) }}"
                                        class="btn-premium-view">
                                        Ver Perfil <i class="bi bi-arrow-right ms-1"></i>
                                    </a>

                                    @if ($worker->cv_unlocked)
                                        <form method="POST" action="{{ route('empresa.candidatos.toggle_selection') }}"
                                            class="select-candidate-form mb-0">
                                            @csrf
                                            <input type="hidden" name="worker_profile_id" value="{{ $worker->id }}">
                                            <input type="hidden" name="job_offer_id" value="{{ $jobOffer->id }}">
                                            <button type="submit"
                                                class="btn-premium-action w-100 {{ $isSelected ? 'btn-deselect' : 'btn-select' }}">
                                                @if ($isSelected)
                                                    <i class="bi bi-dash-circle me-2"></i> Descartar
                                                @else
                                                    <i class="bi bi-plus-circle me-2"></i> Seleccionar
                                                @endif
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn-premium-action btn-unlock w-100 unlock-cv-btn"
                                            data-unlock-url="{{ route('empresa.trabajadores.unlock', ['workerProfile' => $worker, 'jobOffer' => $jobOffer]) }}"
                                            data-available-views="{{ $availableCvViews ?? 0 }}">
                                            <i class="bi bi-lock-fill me-2"></i> Desbloquear
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <img src="https://illustrations.popsy.co/gray/searching.svg" style="width: 200px;"
                                alt="Buscando">
                            <h4 class="fw-bold mt-4">No hay candidatos aptos</h4>
                            <p class="text-muted">Aún no hay perfiles que encajen con los requisitos de esta oferta.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Empty State para JS -->
                <div id="jsNoResults" class="d-none text-center py-5">
                    <i class="bi bi-funnel-fill display-1 text-muted opacity-25"></i>
                    <h5 class="fw-bold mt-3">Sin resultados para esos filtros</h5>
                    <p class="text-muted">Intenta ampliar el rango de puntuación o cambiar la ubicación.</p>
                </div>
            </main>
        </div>
    </div>

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Selectores de Filtros
                const searchInput = document.getElementById('searchInput');
                const islaFilter = document.getElementById('islaFilter');
                const localityInput = document.getElementById('localityInput');
                const educationInput = document.getElementById('educationInput');
                const languagesInput = document.getElementById('languagesInput');
                const languagesChips = document.getElementById('languagesChips');
                const skillsInput = document.getElementById('skillsInput');
                const skillsChips = document.getElementById('skillsChips');
                const toolsInput = document.getElementById('toolsInput');
                const toolsChips = document.getElementById('toolsChips');
                const selectRadios = document.getElementsByName('selectFilter');
                const resetBtn = document.getElementById('resetFilters');
                const applyFiltersBtn = document.getElementById('applyFiltersBtn');
                const popularLanguageCheckboxes = document.querySelectorAll('.popular-language-checkbox');
                const popularLocalityCheckboxes = document.querySelectorAll('.popular-locality-checkbox');
                const popularEducationCheckboxes = document.querySelectorAll('.popular-education-checkbox');
                const perPageSelect = document.getElementById('perPageSelect');
                const paginationEl = document.getElementById('pagination');

                const cards = document.querySelectorAll('.candidate-card-wrapper');
                const totalCountSpan = document.getElementById('totalCount');
                const jsNoResults = document.getElementById('jsNoResults');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                let currentPage = 1;
                let lastMatchingCards = [];

                const tokenSuggestionLists = {
                    skills: @json($skillSuggestions->values()),
                    tools: @json($toolSuggestions->values()),
                    languages: @json($languageSuggestions->values()),
                };

                const normalizeText = (value) =>
                    (value ?? '')
                    .toString()
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
                const normalizeToken = (value) => normalizeText(value);
                const languageTokenAliases = {
                    english: 'ingles',
                    spanish: 'espanol',
                    german: 'aleman',
                    french: 'frances',
                    portuguese: 'portugues',
                    italian: 'italiano',
                    chinese: 'chino',
                    japanese: 'japones',
                };
                const normalizeLanguageToken = (value) => {
                    const normalized = normalizeText(value)
                        .replace(/\s*\([^)]*\)\s*/g, ' ')
                        .replace(/[^a-z]+/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    const firstWord = (normalized.split(' ')[0] ?? '').trim();
                    return languageTokenAliases[firstWord] ?? firstWord;
                };
                const toKey = (value) => normalizeText(value).replace(/[^a-z0-9]/g, '');
                const islandAliasMap = {
                    grancanaria: ['laspalmas'],
                    lanzarote: ['laspalmas'],
                    fuerteventura: ['laspalmas'],
                    tenerife: ['santacruzdenerife'],
                    lapalma: ['santacruzdenerife'],
                    lagomera: ['santacruzdenerife'],
                    elhierro: ['santacruzdenerife'],
                };
                const buildSuggestionMap = (list) => {
                    const map = new Map();
                    (list ?? []).forEach((value) => map.set(normalizeToken(value), value));
                    return map;
                };

                function setupTokenInput({
                    inputEl,
                    chipsEl,
                    suggestions,
                    onChange
                }) {
                    const selected = new Set();
                    const suggestionMap = buildSuggestionMap(suggestions);

                    const render = () => {
                        if (!chipsEl) return;
                        chipsEl.innerHTML = '';
                        selected.forEach((token) => {
                            const label = suggestionMap.get(token) ?? token;
                            const chip = document.createElement('span');
                            chip.className =
                                'badge rounded-pill text-bg-light border filter-chip d-inline-flex align-items-center gap-2';
                            chip.textContent = label;

                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'btn btn-sm btn-link text-decoration-none p-0 lh-1';
                            removeBtn.setAttribute('aria-label', `Quitar ${label}`);
                            removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                            removeBtn.addEventListener('click', () => {
                                selected.delete(token);
                                render();
                                onChange?.();
                            });

                            chip.appendChild(removeBtn);
                            chipsEl.appendChild(chip);
                        });
                    };

                    const addToken = (rawValue) => {
                        const token = normalizeToken(rawValue);
                        if (!token) return;
                        selected.add(token);
                        render();
                        onChange?.();
                    };

                    const commitCurrentValue = () => {
                        if (!inputEl) return;
                        const value = inputEl.value;
                        if (!value?.trim()) return;
                        addToken(value);
                        inputEl.value = '';
                    };

                    inputEl?.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ',') {
                            e.preventDefault();
                            commitCurrentValue();
                        }
                    });
                    inputEl?.addEventListener('blur', commitCurrentValue);

                    render();

                    return {
                        getValues: () => Array.from(selected.values()),
                        clear: () => {
                            selected.clear();
                            if (inputEl) inputEl.value = '';
                            render();
                        }
                    };
                }

                const cardMetaCache = new WeakMap();
                const safeParseJsonArray = (value) => {
                    if (!value) return [];
                    try {
                        const parsed = JSON.parse(value);
                        return Array.isArray(parsed) ? parsed : [];
                    } catch {
                        return [];
                    }
                };
                const getCardMeta = (card) => {
                    let meta = cardMetaCache.get(card);
                    if (meta) return meta;

                    const skillsNames = safeParseJsonArray(card.dataset.skillsNames).map(normalizeToken);
                    const toolsNames = safeParseJsonArray(card.dataset.toolsNames).map(normalizeToken);
                    const languagesNames = safeParseJsonArray(card.dataset.languagesNames).map(
                        normalizeLanguageToken);

                    meta = {
                        island: normalizeToken(card.dataset.island),
                        localityKey: (card.dataset.localityKey ?? '') || toKey(card.dataset.locality),
                        educationToken: normalizeToken(card.dataset.educationLevelToken),
                        educationLabel: normalizeText(card.dataset.educationLevelLabel).replace(/\s+/g, ' ')
                            .trim(),
                        skillsSet: new Set(skillsNames),
                        toolsSet: new Set(toolsNames),
                        languagesSet: new Set(languagesNames),
                    };
                    cardMetaCache.set(card, meta);
                    return meta;
                };

                const tokenFilters = {
                    languages: setupTokenInput({
                        inputEl: languagesInput,
                        chipsEl: languagesChips,
                        suggestions: tokenSuggestionLists.languages,
                        onChange: () => applyFilters(),
                    }),
                    skills: setupTokenInput({
                        inputEl: skillsInput,
                        chipsEl: skillsChips,
                        suggestions: tokenSuggestionLists.skills,
                        onChange: () => applyFilters(),
                    }),
                    tools: setupTokenInput({
                        inputEl: toolsInput,
                        chipsEl: toolsChips,
                        suggestions: tokenSuggestionLists.tools,
                        onChange: () => applyFilters(),
                    }),
                };

                const setHasPartial = (set, token) => {
                    if (!token) return true;
                    for (const value of set.values()) {
                        if (value.includes(token)) return true;
                    }
                    return false;
                };

                const getPerPage = () => {
                    const value = perPageSelect?.value ?? '25';
                    if (value === 'all') return Infinity;
                    const parsed = parseInt(value, 10);
                    return Number.isFinite(parsed) && parsed > 0 ? parsed : 25;
                };

                const renderPagination = (totalPages) => {
                    if (!paginationEl) return;
                    paginationEl.innerHTML = '';
                    if (totalPages <= 1) return;

                    const createItem = ({
                        label,
                        page,
                        disabled = false,
                        active = false,
                        ariaLabel = null
                    }) => {
                        const li = document.createElement('li');
                        li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
                        const a = document.createElement('a');
                        a.className = 'page-link';
                        a.href = '#';
                        a.textContent = label;
                        if (ariaLabel) a.setAttribute('aria-label', ariaLabel);
                        if (!disabled && !active && typeof page === 'number') {
                            a.addEventListener('click', (e) => {
                                e.preventDefault();
                                currentPage = page;
                                applyPagination();
                            });
                        } else {
                            a.addEventListener('click', (e) => e.preventDefault());
                        }
                        li.appendChild(a);
                        paginationEl.appendChild(li);
                    };

                    createItem({
                        label: '«',
                        page: Math.max(1, currentPage - 1),
                        disabled: currentPage <= 1,
                        ariaLabel: 'Anterior'
                    });

                    const pages = [];
                    const windowSize = 2;
                    const start = Math.max(2, currentPage - windowSize);
                    const end = Math.min(totalPages - 1, currentPage + windowSize);

                    pages.push(1);
                    if (start > 2) pages.push('…');
                    for (let p = start; p <= end; p++) pages.push(p);
                    if (end < totalPages - 1) pages.push('…');
                    if (totalPages > 1) pages.push(totalPages);

                    pages.forEach((p) => {
                        if (p === '…') {
                            createItem({
                                label: '…',
                                page: null,
                                disabled: true
                            });
                            return;
                        }
                        createItem({
                            label: String(p),
                            page: p,
                            active: p === currentPage
                        });
                    });

                    createItem({
                        label: '»',
                        page: Math.min(totalPages, currentPage + 1),
                        disabled: currentPage >= totalPages,
                        ariaLabel: 'Siguiente'
                    });
                };

                function applyPagination() {
                    const perPage = getPerPage();
                    const total = lastMatchingCards.length;
                    const totalPages = perPage === Infinity ? (total > 0 ? 1 : 0) : Math.ceil(total / perPage);

                    renderPagination(totalPages);
                    cards.forEach(card => {
                        card.style.display = 'none';
                    });

                    if (total === 0) return;

                    const safeTotalPages = Math.max(1, totalPages);
                    currentPage = Math.min(Math.max(1, currentPage), safeTotalPages);
                    const startIndex = perPage === Infinity ? 0 : (currentPage - 1) * perPage;
                    const endIndex = perPage === Infinity ? total : Math.min(total, startIndex + perPage);

                    lastMatchingCards.slice(startIndex, endIndex).forEach(card => {
                        card.style.display = 'block';
                    });
                }

                function applyFilters() {
                    currentPage = 1;
                    const query = searchInput.value.toLowerCase();
                    const island = islaFilter.value;
                    const islandKey = toKey(island);
                    const localityQueryKey = toKey(localityInput?.value);
                    const selectedLocalityKeys = Array.from(popularLocalityCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => (cb.value ?? '').toString());
                    const educationQuery = normalizeText(educationInput?.value).replace(/\s+/g, ' ').trim();
                    const educationQueryToken = normalizeToken(educationInput?.value).replace(/\s+/g, '_');
                    const selectedEducationTokens = Array.from(popularEducationCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => normalizeToken(cb.value));
                    const requiredLanguages = tokenFilters.languages.getValues().map(normalizeLanguageToken);
                    const requiredSkills = tokenFilters.skills.getValues();
                    const requiredTools = tokenFilters.tools.getValues();
                    const popularLanguagesSelected = Array.from(popularLanguageCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => normalizeLanguageToken(cb.value));
                    const pendingLanguage = normalizeLanguageToken(languagesInput?.value);
                    const pendingSkill = normalizeToken(skillsInput?.value);
                    const pendingTool = normalizeToken(toolsInput?.value);
                    const languagesToMatch = Array.from(new Set([
                        ...requiredLanguages,
                        ...popularLanguagesSelected,
                        ...(pendingLanguage ? [pendingLanguage] : []),
                    ].map(normalizeLanguageToken))).filter(Boolean);
                    const skillsToMatch = pendingSkill ? [...requiredSkills, pendingSkill] : requiredSkills;
                    const toolsToMatch = pendingTool ? [...requiredTools, pendingTool] : requiredTools;
                    let selectionType = 'any';
                    selectRadios.forEach(r => {
                        if (r.checked) selectionType = r.value;
                    });

                    const matchingCards = [];

                    cards.forEach(card => {
                        const name = card.dataset.name;
                        const location = card.dataset.location;
                        const meta = getCardMeta(card);
                        const isSelected = card.dataset.selected === 'true';

                        let matches = true;

                        if (query && !name.includes(query) && !location.includes(query)) matches = false;
                        if (islandKey) {
                            const islandAliases = islandAliasMap[islandKey] ?? [];
                            const cardIslandKey = (card.dataset.islandKey ?? '') || toKey(meta.island);
                            const locationKey = (card.dataset.locationKey ?? '') || toKey(location);
                            if (cardIslandKey || locationKey) {
                                const islandMatch =
                                    (cardIslandKey && cardIslandKey.includes(islandKey)) ||
                                    (locationKey && locationKey.includes(islandKey)) ||
                                    islandAliases.some((alias) => locationKey.includes(alias));
                                if (!islandMatch) matches = false;
                            }
                        }
                        if (selectedLocalityKeys.length && !selectedLocalityKeys.includes(meta.localityKey))
                            matches = false;
                        if (localityQueryKey) {
                            const locationKey = (card.dataset.locationKey ?? '') || toKey(location);
                            const localityMatch =
                                (meta.localityKey && meta.localityKey.includes(localityQueryKey)) ||
                                (locationKey && locationKey.includes(localityQueryKey));
                            if (!localityMatch) matches = false;
                        }
                        if (selectedEducationTokens.length && !selectedEducationTokens.includes(meta
                                .educationToken))
                            matches = false;
                        if (educationQuery) {
                            const educationMatch =
                                (meta.educationLabel && meta.educationLabel.includes(educationQuery)) ||
                                (educationQueryToken && meta.educationToken && meta.educationToken.includes(
                                    educationQueryToken));
                            if (!educationMatch) matches = false;
                        }
                        if (languagesToMatch.length && !languagesToMatch.every(t => setHasPartial(meta
                                .languagesSet, t)))
                            matches = false;
                        if (skillsToMatch.length && !skillsToMatch.every(t => setHasPartial(meta.skillsSet, t)))
                            matches = false;
                        if (toolsToMatch.length && !toolsToMatch.every(t => setHasPartial(meta.toolsSet, t)))
                            matches = false;
                        if (selectionType === 'selected' && !isSelected) matches = false;

                        if (matches) matchingCards.push(card);
                    });

                    lastMatchingCards = matchingCards;
                    totalCountSpan.textContent = matchingCards.length;
                    jsNoResults.classList.toggle('d-none', matchingCards.length > 0);
                    applyPagination();
                }

                // Eventos
                searchInput.addEventListener('input', applyFilters);
                islaFilter.addEventListener('change', applyFilters);
                localityInput?.addEventListener('input', applyFilters);
                selectRadios.forEach(r => r.addEventListener('change', applyFilters));
                educationInput?.addEventListener('input', applyFilters);
                languagesInput?.addEventListener('input', applyFilters);
                skillsInput?.addEventListener('input', applyFilters);
                toolsInput?.addEventListener('input', applyFilters);
                applyFiltersBtn?.addEventListener('click', applyFilters);
                popularLanguageCheckboxes.forEach(cb => cb.addEventListener('change', applyFilters));
                popularLocalityCheckboxes.forEach(cb => cb.addEventListener('change', applyFilters));
                popularEducationCheckboxes.forEach(cb => cb.addEventListener('change', applyFilters));
                perPageSelect?.addEventListener('change', () => {
                    currentPage = 1;
                    applyPagination();
                });

                resetBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    islaFilter.value = '';
                    if (localityInput) localityInput.value = '';
                    popularLocalityCheckboxes.forEach(cb => (cb.checked = false));
                    if (educationInput) educationInput.value = '';
                    popularEducationCheckboxes.forEach(cb => (cb.checked = false));
                    popularLanguageCheckboxes.forEach(cb => (cb.checked = false));
                    tokenFilters.languages.clear();
                    tokenFilters.skills.clear();
                    tokenFilters.tools.clear();
                    document.getElementById('filterAny').checked = true;
                    currentPage = 1;
                    applyFilters();
                });

                applyFilters();

                // Lógica de Selección AJAX (Simplificada para el ejemplo)
                document.querySelectorAll('.select-candidate-form').forEach(form => {
                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const btn = this.querySelector('button');
                        const isSelecting = btn.classList.contains('btn-primary');

                        if (isSelecting) {
                            const res = await Swal.fire({
                                title: '¿Seleccionar candidato?',
                                text: 'Se utilizará un crédito de contacto si aún no lo has hecho.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, seleccionar'
                            });
                            if (!res.isConfirmed) return;
                        }

                        const formData = new FormData(this);
                        try {
                            const response = await fetch(this.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });
                            const data = await response.json();

                            if (data.status === 'success') {
                                window.location
                                    .reload(); // Recarga simple para actualizar estados de créditos y badges
                            }
                        } catch (err) {
                            Swal.fire('Error', 'No se pudo procesar la selección', 'error');
                        }
                    });
                });

                // Desbloqueo de CV
                document.querySelectorAll('.unlock-cv-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const available = parseInt(this.dataset.availableViews);
                        if (available <= 0) {
                            Swal.fire('Sin créditos',
                                'No tienes créditos de desbloqueo disponibles.', 'warning');
                            return;
                        }

                        const res = await Swal.fire({
                            title: 'Desbloquear perfil',
                            text: `Se consumirá 1 crédito. Disponibles: ${available}`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Desbloquear'
                        });

                        if (res.isConfirmed) {
                            const url = this.dataset.unlockUrl;
                            try {
                                const response = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        job_offer_id: "{{ $jobOffer->id }}"
                                    })
                                });
                                if (response.ok) window.location.reload();
                            } catch (e) {
                                Swal.fire('Error', 'Error al desbloquear', 'error');
                            }
                        }
                    });
                });
            });
        </script>
    @endsection
@endsection
