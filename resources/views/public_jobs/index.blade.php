@extends('plantilla')

@section('title', 'Ofertas Públicas | FuerteJob')

@php
    $islandSocialNetworks = $islandSocialNetworks ?? collect();
    $islandLabel = $islandLabel ?? null;
@endphp

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .public-jobs-hero {
            position: relative;
            overflow: hidden;
            background: #1e6493;
            color: #fff;
        }

        .public-jobs-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('{{ asset('assets/images/background.jpg') }}') center/cover no-repeat;
            opacity: 0.25;
            mix-blend-mode: soft-light;
        }

        .public-jobs-hero .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-stat-card {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 1.5rem;
            backdrop-filter: blur(8px);
        }

        .filter-panel {
            border-radius: 1.5rem;
            border: 1px solid rgba(13, 110, 253, 0.08);
            box-shadow: 0 1.5rem 2.5rem rgba(15, 23, 42, 0.08);
        }

        .job-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.25rem;
            box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .job-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 1.5rem 3rem rgba(15, 23, 42, 0.12);
        }

        .tag-badge {
            border-radius: 50rem;
            padding: 0.4rem 0.9rem;
            background: #f8f9fb;
            font-size: 0.85rem;
        }

        .btn-gradient {
            background: #1e6493;
            border: none;
            color: #fff;
            box-shadow: 0 0.75rem 1.5rem rgba(13, 110, 253, 0.25);
        }

        .btn-gradient:hover {
            background: #984f4f;
            color: #fff;
            transform: translateY(-1px);
        }

        .alert-candidate {
            border-radius: 1rem;
            border: 1px solid rgba(7, 34, 255, 0.4);
            background: rgb(40, 59, 208);
        }

        .job-side-panel {
            min-width: 260px;
            border-radius: 1rem;
            border: 1px solid rgba(13, 110, 253, 0.08);
            background: #f8f9fb;
            padding: 1rem 1.5rem;
        }

        @media (max-width: 991.98px) {
            .job-side-panel {
                min-width: auto;
                width: 100%;
            }
        }

        .shake {
            animation: shake 0.5s;
        }

        .sector-autocomplete-wrapper {
            position: relative;
        }

        .sector-tags-wrapper {
            min-height: 46px;
            border: 1px dashed rgba(13, 110, 253, 0.3);
            border-radius: 0.75rem;
            padding: 0.5rem;
            background: #f8f9fb;
        }

        .sector-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #1e6493;
            color: #fff;
            border-radius: 50rem;
            padding: 0.3rem 0.9rem;
            font-size: 0.85rem;
        }

        .sector-pill .btn-close {
            filter: invert(1);
            width: 0.6rem;
            height: 0.6rem;
            opacity: 0.8;
        }

        .sector-pill .btn-close:hover {
            opacity: 1;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translateX(-2px);
            }

            20%,
            80% {
                transform: translateX(4px);
            }

            30%,
            50%,
            70% {
                transform: translateX(-6px);
            }

            40%,
            60% {
                transform: translateX(6px);
            }
        }
    </style>
@endsection

@section('content')
    <section class="public-jobs-hero py-5 py-lg-6">
        <div class="container hero-content">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <p class="text-uppercase small fw-semibold mb-2 opacity-75">Acceso Público</p>
                    <h1 class="display-4 fw-bold mb-3">Descubre oportunidades reales en Canarias</h1>
                    <p class="lead mb-4 pe-lg-5">
                        Todas las ofertas provienen de empresas verificadas dentro de FuerteJob. Filtra por provincia, isla
                        o modalidad y aplica desde tu panel privado.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('worker.register.form') }}" class="btn btn-light btn-lg rounded-pill px-4">
                            <i class="bi bi-person-plus-fill me-2"></i>Quiero ser candidato
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ya tengo cuenta
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-stat-card p-4 p-lg-5 text-white">
                        <div class="d-flex gap-4">
                            <div>
                                <p class="text-uppercase small opacity-75 mb-1">Ofertas Disponibles</p>
                                <h3 class="display-5 fw-bold mb-0">{{ number_format($jobOffers->total()) }}</h3>
                                <small class="opacity-75">Actualizado en tiempo real</small>
                            </div>
                            <div>
                                <p class="text-uppercase small opacity-75 mb-1">Modalidades</p>
                                <h6 class="mb-0">Presencial · Remoto · Híbrido</h6>
                                <small class="opacity-75">Filtra para ver lo que necesitas</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        @unless ($canViewOffers)
            <div id="candidateAlert" class="alert alert-candidate shadow-sm mb-4 d-flex align-items-start gap-3">

                <div>
                    <h5 class="fw-bold mb-1">Acceso limitado a los detalles</h5>
                    <p class="mb-0">
                        Para abrir una oferta necesitas una cuenta de candidato confirmada. Regístrate gratis o inicia sesión
                        para continuar.
                    </p>
                </div>
            </div>
        @endunless

        <div class="row g-4">
            <div class="col-xl-3 col-lg-4">
                <div class="card filter-panel shadow-sm p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="text-uppercase small text-muted fw-semibold mb-1">Filtra por</p>
                            <h4 class="mb-0">Preferencias</h4>
                        </div>
                        <i class="bi bi-sliders2 text-primary fs-4"></i>
                    </div>
                    <form action="{{ route('public.jobs.index') }}" method="GET" class="d-grid gap-3">
                        <div>
                            <label class="form-label small text-muted text-uppercase fw-semibold">Palabra clave</label>
                            <input type="text" class="form-control rounded-3" name="search"
                                value="{{ $filters['search'] }}" placeholder="Ej. Recepcionista">
                        </div>
                        <div>
                            <label class="form-label small text-muted text-uppercase fw-semibold">Provincia</label>
                            <input type="text" class="form-control rounded-3" name="province"
                                value="{{ $filters['province'] }}" placeholder="Ej. Las Palmas">
                        </div>
                        <div>
                            <label class="form-label small text-muted text-uppercase fw-semibold">Isla</label>
                            <input type="text" class="form-control rounded-3" name="island"
                                value="{{ $filters['island'] }}" placeholder="Ej. Fuerteventura">
                        </div>
                        <div>
                            <label class="form-label small text-muted text-uppercase fw-semibold">Sectores</label>
                            <div class="sector-autocomplete-wrapper">
                                <input type="text" class="form-control rounded-3" id="public_sector_search"
                                    placeholder="Escribe para buscar..." autocomplete="off">
                                <small class="text-muted mt-1 d-block">Selecciona uno o varios sectores del catálogo
                                    oficial.</small>
                            </div>
                            <div id="public-sector-feedback" class="small mt-2"></div>
                            <div id="public-selected-sectors" class="sector-tags-wrapper d-flex flex-wrap gap-2 mt-2">
                                @forelse ($selectedSectors as $sector)
                                    <span class="sector-pill" data-sector-id="{{ $sector['id'] }}">
                                        <i class="bi bi-briefcase"></i> {{ $sector['label'] }}
                                        <button type="button" class="btn-close btn-close-white remove-sector-pill"
                                            data-sector-id="{{ $sector['id'] }}" aria-label="Eliminar sector"></button>
                                    </span>
                                @empty
                                    <p class="text-muted mb-0" id="public-no-sectors-placeholder">Aún no has agregado
                                        sectores.</p>
                                @endforelse
                            </div>
                            <div id="public-sector-hidden-inputs">
                                @foreach ($selectedSectors as $sector)
                                    <input type="hidden" name="sectors[]" value="{{ $sector['id'] }}"
                                        data-sector-id="{{ $sector['id'] }}">
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="form-label small text-muted text-uppercase fw-semibold">Modalidad</label>
                            <select class="form-select rounded-3" name="modality">
                                <option value="">Cualquiera</option>
                                <option value="presencial" @selected($filters['modality'] === 'presencial')>Presencial</option>
                                <option value="remoto" @selected($filters['modality'] === 'remoto')>Remoto</option>
                                <option value="hibrido" @selected($filters['modality'] === 'hibrido')>Híbrido</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small text-muted text-uppercase fw-semibold">Contrato</label>
                            <select class="form-select rounded-3" name="contract_type">
                                <option value="">Cualquiera</option>
                                @foreach (['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'] as $type)
                                    <option value="{{ $type }}" @selected($filters['contract_type'] === $type)>{{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-gradient rounded-pill fw-semibold">
                            <i class="bi bi-search me-1"></i> Aplicar filtros
                        </button>
                        @if (collect($filters)->filter(fn($value) => filled($value))->isNotEmpty())
                            <a href="{{ route('public.jobs.index') }}" class="btn btn-outline-secondary rounded-pill">
                                Reiniciar filtros
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                    <div>
                        <p class="text-muted mb-0">Resultados en tiempo real</p><br>
                        <h5 class="mb-0 fw-bold">{{ $jobOffers->total() }} ofertas publicadas activas</h5>
                        @if ($selectedSectors->isNotEmpty())
                            <small class="text-muted d-block mt-1">Filtrando por sectores:
                                <span
                                    class="fw-semibold text-dark">{{ $selectedSectors->pluck('label')->implode(', ') }}</span></small>
                        @endif
                    </div>
                    <span class="badge bg-light text-dark py-2 px-3 rounded-pill">

                    </span>
                </div>

                @forelse ($jobOffers as $offer)
                    @php
                        $offerPayload = [
                            'title' => $offer->title,
                            'location' => $offer->location,
                            'province' => $offer->province,
                            'island' => $offer->island,
                            'salary_range' => $offer->salary_range,
                            'modality' => $offer->modality,
                            'contract_type' => $offer->contract_type,
                            'description' => $offer->description,
                            'requirements' => $offer->requirements,
                            'benefits' => $offer->benefits,
                            'published_at' => optional($offer->created_at)->format('d/m/Y'),
                        ];
                    @endphp
                    <article class="card job-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row gap-4 align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"
                                            style="background: #1e6493 !important;">Publicada</span>
                                        <small
                                            class="text-muted">{{ optional($offer->created_at)->locale('es')->diffForHumans() }}</small>
                                    </div>
                                    <h3 class="h4 fw-bold text-dark mb-2">{{ $offer->title }}</h3>
                                    <p class="text-muted mb-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($offer->description), 200) }}
                                    </p>
                                    <button type="button"
                                        class="btn btn-gradient rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2 mb-3"
                                        data-offer='@json($offerPayload)'>
                                        <i class="bi bi-eye-fill"></i> Ver oferta
                                    </button>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if ($offer->location)
                                            <span class="tag-badge">
                                                <i class="bi bi-geo-alt me-1 text-primary"></i>{{ $offer->location }}
                                            </span>
                                        @endif
                                        @if ($offer->province)
                                            <span class="tag-badge">
                                                <i class="bi bi-pin-map me-1 text-danger"></i>{{ $offer->province }}
                                            </span>
                                        @endif
                                        @if ($offer->island)
                                            <span class="tag-badge">
                                                <i
                                                    class="bi bi-brightness-alt-high me-1 text-warning"></i>{{ $offer->island }}
                                            </span>
                                        @endif
                                        <span class="tag-badge text-capitalize">
                                            <i
                                                class="bi bi-briefcase me-1 text-success"></i>{{ $offer->modality ?? 'N/D' }}
                                        </span>
                                        @if ($offer->contract_type)
                                            <span class="tag-badge">
                                                <i
                                                    class="bi bi-file-earmark-text me-1 text-info"></i>{{ $offer->contract_type }}
                                            </span>
                                        @endif
                                        @if ($offer->salary_range)
                                            <span class="tag-badge">
                                                <i
                                                    class="bi bi-currency-euro me-1 text-success"></i>{{ $offer->salary_range }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="job-side-panel text-md-end ms-md-4 mt-3 mt-md-0">
                                    <p class="small text-muted text-uppercase fw-semibold mb-2">Provincia / Isla</p>
                                    <p class="fw-semibold mb-0">{{ $offer->province ?? '—' }}</p>
                                    <small class="text-muted d-block mb-3">{{ $offer->island ?? '—' }}</small>

                                    <p class="small text-muted text-uppercase fw-semibold mb-2">Contrato</p>
                                    <p class="fw-semibold mb-0">{{ $offer->contract_type ?? 'No especificado' }}</p>
                                    <small
                                        class="text-muted d-block mb-3 text-capitalize">{{ $offer->modality ?? 'N/D' }}</small>

                                    <p class="small text-muted text-uppercase fw-semibold mb-2">Rango Salarial</p>
                                    <p class="fw-semibold mb-0">{{ $offer->salary_range ?? 'No disponible' }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card shadow-sm border-0 rounded-4 p-5 text-center">
                        <i class="bi bi-search text-muted display-3 d-block mb-3"></i>
                        <h5 class="fw-bold text-muted mb-2">No encontramos ofertas con esos filtros.</h5>
                        <p class="text-muted mb-4">Prueba con otros términos o reinicia los filtros para ver todo el
                            catálogo
                            disponible.</p>
                        <a href="{{ route('public.jobs.index') }}" class="btn btn-gradient rounded-pill px-4">
                            Reiniciar búsqueda
                        </a>
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $jobOffers->links('pagination::bootstrap-5') }}
                </div>
                @if ($islandSocialNetworks->isNotEmpty())
                    <div class="mt-5">
                        <h4 class="mb-3 fw-bold">Síguenos en las redes de
                            {{ $islandLabel ?? ucfirst(strtolower($filters['island'] ?? '')) }}</h4>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($islandSocialNetworks as $network)
                                <a class="btn btn-outline-primary rounded-pill d-flex align-items-center gap-2"
                                    href="{{ $network->url }}" target="_blank" rel="noopener noreferrer">
                                    <i class="{{ $network->icon_class }}"></i>
                                    <span>{{ $network->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="publicJobModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <div>
                        <p class="text-uppercase small fw-semibold text-primary mb-1">Oferta publicada</p>
                        <h5 class="modal-title fw-bold" id="modalJobTitle">Título de la oferta</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <p class="text-muted small mb-1">Ubicación</p>
                                <p class="fw-semibold mb-0" id="modalJobLocation">-</p>
                                <small class="text-muted" id="modalJobIsland"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <p class="text-muted small mb-1">Modalidad</p>
                                <p class="fw-semibold text-capitalize mb-0" id="modalJobModality">-</p>
                                <small class="text-muted" id="modalJobContract"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 h-100">
                                <p class="text-muted small mb-1">Publicado el</p>
                                <p class="fw-semibold mb-0" id="modalJobPublished">-</p>
                                <small class="text-muted" id="modalJobSalary"></small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase small">Descripción</h6>
                        <p class="mb-0" id="modalJobDescription">-</p>
                    </div>
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase small">Requisitos</h6>
                        <p class="mb-0" id="modalJobRequirements">-</p>
                    </div>
                    <div>
                        <h6 class="fw-bold text-muted text-uppercase small">Beneficios</h6>
                        <p class="mb-0" id="modalJobBenefits">-</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('worker.register.form') }}" class="btn btn-gradient rounded-pill px-4">
                        Acceder como candidato
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('public_sector_search');
            const selectedContainer = document.getElementById('public-selected-sectors');
            const feedbackContainer = document.getElementById('public-sector-feedback');
            const hiddenInputsContainer = document.getElementById('public-sector-hidden-inputs');
            const sectorSearchUrl = "{{ route('api.sectores.search') }}";
            const initialSectors = @json($selectedSectors->values());
            const selectedSectors = new Map();

            initialSectors.forEach(sector => {
                selectedSectors.set(String(sector.id), sector.label);
            });

            function showFeedback(message = '', type = 'info') {
                if (!feedbackContainer) {
                    return;
                }

                feedbackContainer.textContent = message;
                feedbackContainer.className = message ? `small text-${type}` : 'small mt-2';
            }

            function ensurePlaceholder() {
                const placeholder = document.getElementById('public-no-sectors-placeholder');
                if (selectedSectors.size === 0) {
                    if (!placeholder) {
                        const empty = document.createElement('p');
                        empty.id = 'public-no-sectors-placeholder';
                        empty.className = 'text-muted mb-0';
                        empty.textContent = 'Aún no has agregado sectores.';
                        selectedContainer.appendChild(empty);
                    }
                } else if (placeholder) {
                    placeholder.remove();
                }
            }

            function addHiddenInput(id) {
                if (!hiddenInputsContainer) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sectors[]';
                input.value = id;
                input.dataset.sectorId = id;
                hiddenInputsContainer.appendChild(input);
            }

            function removeHiddenInput(id) {
                const input = hiddenInputsContainer?.querySelector(`input[data-sector-id=\"${id}\"]`);
                if (input) {
                    input.remove();
                }
            }

            function createPill(id, label) {
                const pill = document.createElement('span');
                pill.className = 'sector-pill';
                pill.dataset.sectorId = id;
                pill.innerHTML = `<i class=\"bi bi-briefcase\"></i> ${label}`;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn-close btn-close-white remove-sector-pill';
                removeBtn.dataset.sectorId = id;
                removeBtn.setAttribute('aria-label', 'Eliminar sector');

                pill.appendChild(removeBtn);
                selectedContainer.appendChild(pill);
            }

            function removePill(id) {
                const pill = selectedContainer?.querySelector(`.sector-pill[data-sector-id=\"${id}\"]`);
                if (pill) {
                    pill.remove();
                }
            }

            function addSector(id, label) {
                const key = String(id);
                if (selectedSectors.has(key)) {
                    showFeedback('Ese sector ya está agregado.', 'warning');
                    return;
                }

                showFeedback('');
                selectedSectors.set(key, label);
                addHiddenInput(key);
                createPill(key, label);
                ensurePlaceholder();

                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
            }

            function removeSector(id) {
                const key = String(id);
                if (!selectedSectors.has(key)) {
                    return;
                }

                selectedSectors.delete(key);
                removeHiddenInput(key);
                removePill(key);
                ensurePlaceholder();
            }

            ensurePlaceholder();

            selectedContainer?.addEventListener('click', function(event) {
                const button = event.target.closest('.remove-sector-pill');
                if (!button) {
                    return;
                }

                event.preventDefault();
                removeSector(button.dataset.sectorId);
            });

            if (searchInput && sectorSearchUrl) {
                $('#public_sector_search').autocomplete({
                    minLength: 2,
                    source: function(request, response) {
                        $.ajax({
                                url: sectorSearchUrl,
                                dataType: 'json',
                                data: {
                                    term: request.term
                                }
                            })
                            .done(data => response(data))
                            .fail(() => showFeedback(
                                'No se pudieron obtener sectores. Inténtalo de nuevo.', 'danger'));
                    },
                    select: function(event, ui) {
                        event.preventDefault();
                        addSector(ui.item.id, ui.item.label);
                    },
                    focus: function(event) {
                        event.preventDefault();
                    }
                });
            } else if (searchInput) {
                searchInput.disabled = true;
                searchInput.placeholder = 'Autocompletado no disponible';
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canView = @json($canViewOffers);
            const modalElement = document.getElementById('publicJobModal');
            const jobModal = new bootstrap.Modal(modalElement);

            const fields = {
                title: document.getElementById('modalJobTitle'),
                location: document.getElementById('modalJobLocation'),
                island: document.getElementById('modalJobIsland'),
                modality: document.getElementById('modalJobModality'),
                contract: document.getElementById('modalJobContract'),
                published: document.getElementById('modalJobPublished'),
                salary: document.getElementById('modalJobSalary'),
                description: document.getElementById('modalJobDescription'),
                requirements: document.getElementById('modalJobRequirements'),
                benefits: document.getElementById('modalJobBenefits'),
            };

            function fillField(el, value, fallback = '-') {
                if (el) {
                    const normalized = (value ?? '').toString().trim();
                    el.textContent = normalized !== '' ? normalized : fallback;
                }
            }

            document.querySelectorAll('[data-offer]').forEach(button => {
                button.addEventListener('click', function() {
                    if (!canView) {
                        const alertBox = document.getElementById('candidateAlert');
                        if (alertBox) {
                            alertBox.classList.add('shake');
                            setTimeout(() => alertBox.classList.remove('shake'), 600);
                            alertBox.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                        alert(
                            'Debes iniciar sesión y verificarte como candidato para ver la oferta completa.'
                        );
                        return;
                    }

                    const offer = JSON.parse(this.getAttribute('data-offer'));
                    fillField(fields.title, offer.title);
                    fillField(fields.location, offer.location);
                    fillField(fields.island, [offer.province, offer.island].filter(Boolean).join(
                        ' · '));
                    fillField(fields.modality, offer.modality);
                    fillField(fields.contract, offer.contract_type);
                    fillField(fields.published, offer.published_at);
                    fillField(fields.salary, offer.salary_range ? `Salario: ${offer.salary_range}` :
                        '');
                    fillField(fields.description, offer.description);
                    fillField(fields.requirements, offer.requirements);
                    fillField(fields.benefits, offer.benefits ?? 'No especificados');

                    jobModal.show();
                });
            });
        });
    </script>
@endsection
