@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-primary mb-3">
                    <i class="fas fa-search me-2"></i> Buscar Ofertas de Empleo
                </h2>
                <p class="text-muted">Explora y filtra las mejores oportunidades laborales disponibles para ti.</p>
            </div>
        </div>

        <div class="row">
            {{-- SIDEBAR DE FILTROS --}}
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 mb-4 sticky-top" style="top: 2rem; z-index: 10;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-secondary"></i> Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('worker.jobs.index') }}" method="GET">

                            {{-- Búsqueda por palabra clave --}}
                            <div class="mb-3">
                                <label for="search" class="form-label small fw-bold text-uppercase text-muted">Palabra
                                    Clave</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 bg-light" id="search"
                                        name="search" value="{{ request('search') }}"
                                        placeholder="Ej. Desarrollador, Ventas...">
                                </div>
                            </div>

                            {{-- Ubicación --}}
                            <div class="mb-3">
                                <label for="location"
                                    class="form-label small fw-bold text-uppercase text-muted">Ubicación</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="fas fa-map-marker-alt text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 bg-light" id="location"
                                        name="location" value="{{ request('location') }}" placeholder="Ciudad o Región">
                                </div>
                            </div>

                            {{-- Modalidad --}}
                            <div class="mb-3">
                                <label for="modality"
                                    class="form-label small fw-bold text-uppercase text-muted">Modalidad</label>
                                <select class="form-select bg-light" id="modality" name="modality">
                                    <option value="">Cualquiera</option>
                                    <option value="presencial" {{ request('modality') == 'presencial' ? 'selected' : '' }}>
                                        Presencial</option>
                                    <option value="remoto" {{ request('modality') == 'remoto' ? 'selected' : '' }}>Remoto
                                    </option>
                                    <option value="hibrido" {{ request('modality') == 'hibrido' ? 'selected' : '' }}>Híbrido
                                    </option>
                                </select>
                            </div>

                            {{-- Tipo de Contrato --}}
                            <div class="mb-4">
                                <label for="contract_type"
                                    class="form-label small fw-bold text-uppercase text-muted">Contrato</label>
                                <select class="form-select bg-light" id="contract_type" name="contract_type">
                                    <option value="">Cualquiera</option>
                                    <option value="indefinido"
                                        {{ request('contract_type') == 'indefinido' ? 'selected' : '' }}>Indefinido</option>
                                    <option value="temporal" {{ request('contract_type') == 'temporal' ? 'selected' : '' }}>
                                        Temporal</option>
                                    <option value="freelance"
                                        {{ request('contract_type') == 'freelance' ? 'selected' : '' }}>Freelance/Autónomo
                                    </option>
                                    <option value="practicas"
                                        {{ request('contract_type') == 'practicas' ? 'selected' : '' }}>Prácticas</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold text-white shadow-sm">
                                    Aplicar Filtros
                                </button>
                                @if (request()->anyFilled(['search', 'modality', 'location', 'contract_type']))
                                    <a href="{{ route('worker.jobs.index') }}" class="btn btn-outline-secondary btn-sm">
                                        Limpiar Filtros
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- LISTADO DE RESULTADOS --}}
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">
                        Mostrando <strong>{{ $jobOffers->firstItem() ?? 0 }} - {{ $jobOffers->lastItem() ?? 0 }}</strong>
                        de <strong>{{ $jobOffers->total() }}</strong> ofertas
                    </span>
                </div>

                @forelse($jobOffers as $offer)
                    @php
                        $showCompany = $offer->company_visible ?? true;
                        $companyName = $showCompany
                            ? $offer->companyProfile->company_name ?? 'Empresa'
                            : 'Empresa Confidencial';
                        $companyLogo = $showCompany && $offer->companyProfile ? $offer->companyProfile->logo_url : null;
                    @endphp
                    <div class="card shadow-sm border-0 rounded-3 mb-3 hover-shadow transition-all">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                {{-- Logo de la empresa (Placeholder o real) --}}
                                <div class="col-auto d-none d-sm-block">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border"
                                        style="width: 64px; height: 64px;">
                                        @if ($companyLogo)
                                            <img src="{{ $companyLogo }}" alt="Logo {{ $companyName }}"
                                                class="rounded-circle img-fluid"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <i class="fas fa-building text-secondary fa-lg"></i>
                                        @endif
                                    </div>
                                </div>

                                {{-- Detalles de la oferta --}}
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="fw-bold mb-1 text-dark">
                                                <a href="{{ route('worker.jobs.show', $offer->id) }}"
                                                    class="text-decoration-none text-dark stretched-link-custom">
                                                    {{ $offer->title }}
                                                </a>
                                            </h5>
                                            <p class="mb-2 text-muted small">
                                                <i class="fas fa-building me-1"></i>
                                                {{ $companyName }}
                                            </p>
                                        </div>

                                        {{-- Badge de Estado (Inscrito o Fecha) --}}
                                        <div class="text-end">
                                            @if (in_array($offer->id, $appliedJobOfferIds))
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                    <i class="fas fa-check me-1"></i> Inscrito
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border rounded-pill fw-normal">
                                                    {{ $offer->created_at->locale('es')->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Metadatos (Tags) --}}
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <span class="badge bg-white text-dark border fw-normal">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $offer->location }}
                                        </span>
                                        <span class="badge bg-white text-dark border fw-normal">
                                            @if ($offer->modality === 'remoto')
                                                <i class="fas fa-wifi text-primary me-1"></i> Remoto
                                            @elseif($offer->modality === 'hibrido')
                                                <i class="fas fa-home text-info me-1"></i> Híbrido
                                            @else
                                                <i class="fas fa-building text-secondary me-1"></i> Presencial
                                            @endif
                                        </span>
                                        @if ($offer->salary_range)
                                            <span class="badge bg-white text-dark border fw-normal">
                                                <i class="fas fa-money-bill-wave text-success me-1"></i>
                                                {{ $offer->salary_range }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Botón de Acción --}}
                                <div class="col-md-auto mt-3 mt-md-0 text-md-end text-center">
                                    @if (in_array($offer->id, $appliedJobOfferIds))
                                        <button type="button"
                                            class="btn btn-outline-danger w-100 w-md-auto btn-cancel-subscription"
                                            data-offer-id="{{ $offer->id }}">
                                            Anular
                                        </button>
                                    @else
                                        <a href="{{ route('worker.jobs.show', $offer->id) }}"
                                            class="btn btn-primary w-100 w-md-auto px-4 fw-bold shadow-sm">
                                            Ver Oferta
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-search fa-4x text-muted opacity-25"></i>
                        </div>
                        <h5 class="fw-bold text-secondary">No se encontraron ofertas</h5>
                        <p class="text-muted">Intenta ajustar los filtros de búsqueda para encontrar más resultados.</p>
                    </div>
                @endforelse

                {{-- Paginación --}}
                <div class="mt-4 d-flex justify-content-center">
                    {{ $jobOffers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1) !important;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-cancel-subscription');
                if (!btn) return;

                const offerId = btn.dataset.offerId;

                Swal.fire({
                    title: '¿Anular inscripción?',
                    text: 'Se eliminará tu candidatura en esta oferta.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch("{{ route('worker.jobs.cancel') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                job_offer_id: offerId,
                            })
                        })
                        .then(response => response.json().then(data => ({
                            ok: response.ok,
                            status: response.status,
                            body: data
                        })))
                        .then(({
                            ok,
                            body
                        }) => {
                            if (ok && body.success) {
                                Swal.fire('Inscripción anulada', body.message, 'success')
                                    .then(() => window.location.reload());
                            } else {
                                Swal.fire('No se pudo anular', body.message ||
                                    'Intenta nuevamente.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Cancel Error:', error);
                            Swal.fire('Error de conexión', 'No se pudo completar la anulación.',
                                'error');
                        });
                });
            });
        });
    </script>
@endsection
