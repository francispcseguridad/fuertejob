@extends('layouts.app')

@section('title', 'Candidatos para ' . $jobOffer->title)

@section('content')
    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">
                    <i class="bi bi-person-workspace me-2 text-primary"></i>Candidatos Seleccionados
                </h1>
                <p class="text-muted lead">
                    Oferta: <strong class="text-dark">{{ $jobOffer->title }}</strong>
                    ({{ $selectedCandidates->total() ?? 0 }} candidatos)
                </p>
                {{-- Botón de regreso, útil si vienes de la lista de ofertas --}}
                <a href="{{ route('empresa.ofertas.edit', $jobOffer) }}" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la oferta
                </a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('empresa.ofertas.match', $jobOffer) }}" class="btn btn-primary btn-modern"
                    id="match-cv-button" data-match-url="{{ route('empresa.ofertas.match', $jobOffer) }}"
                    data-unlock-url="{{ route('empresa.ofertas.match.unlock', $jobOffer) }}"
                    data-available="{{ $availableCvViews ?? 0 }}" data-has-unlocked="{{ $hasUnlockedCvViews ? '1' : '0' }}">
                    <i class="bi bi-search-heart me-1"></i>Buscar más candidatos
                </a>
                <span class="badge bg-light text-dark border p-2 rounded-pill">
                    <i class="bi bi-calendar-event me-1"></i> {{ date('d/m/Y') }}
                </span>
            </div>
        </div>

        {{-- Mensajes de Estado --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filtros de Búsqueda y Estado --}}
        <div class="card shadow-sm filter-card mb-5">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="bi bi-funnel-fill me-2 text-primary"></i>Filtros
                </h5>
                {{-- Usamos la ruta con el parámetro jobOffer --}}
                <form id="filter-form" action="{{ route('empresa.candidatos.seleccionados.index', $jobOffer) }}"
                    method="GET">
                    <input type="hidden" name="sort_by" id="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_dir" id="sort_dir" value="{{ $sortDir }}">

                    <div class="row g-3">
                        {{-- 1. Buscador General --}}
                        <div class="col-md-5">
                            <label for="search" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-search me-1"></i>Título o Nombre
                            </label>
                            <input type="text" name="search" id="search" value="{{ $search ?? '' }}"
                                class="form-control form-control-lg rounded-pill"
                                placeholder="Ej: Desarrollador, Juan Pérez">
                        </div>

                        {{-- 2. Filtro de Estado de Selección --}}
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-list-check me-1"></i>Estado de la Selección
                            </label>
                            <select id="status" name="status" class="form-select form-select-lg rounded-pill">
                                <option value="">Todos los Estados</option>
                                @foreach ($allStatuses as $status)
                                    <option value="{{ $status }}"
                                        {{ ($statusFilter ?? '') === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Botones de Acción --}}
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-modern flex-grow-1">
                                <i class="bi bi-search me-2"></i>Aplicar
                            </button>
                            @if (!empty($search) || !empty($statusFilter))
                                {{-- Enlace para resetear filtros --}}
                                <a href="{{ route('empresa.candidatos.seleccionados.index', $jobOffer) }}"
                                    class="btn btn-outline-secondary btn-modern" title="Restablecer filtros">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Contenido Principal: Listado en Formato Tarjeta --}}
        @if ($selectedCandidates->isEmpty())
            <div class="empty-state text-center shadow">
                <div class="icon-circle bg-white shadow-sm mx-auto mb-4">
                    <i class="bi bi-people-fill text-muted"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">No se encontraron candidatos seleccionados</h4>
                <p class="text-muted mb-4">No hay candidatos para esta oferta que coincidan con los filtros aplicados.</p>
                <a href="{{ route('empresa.trabajadores.index') }}" class="btn btn-primary btn-modern">
                    Seleccionar más perfiles
                </a>
            </div>
        @else
            <div class="row g-4 mb-4">
                @foreach ($selectedCandidates as $selection)
                    {{-- Accedemos al perfil del trabajador a través de la relación --}}
                    @php
                        $worker = $selection->workerProfile;
                        // Determinación de iniciales para el placeholder
                        $initials = 'JP';
                        if ($worker && $worker->user && $worker->user->name) {
                            $parts = explode(' ', $worker->user->name);
                            $initials = strtoupper(
                                substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''),
                            );
                        }

                        // Función para determinar la clase del badge según el estado
                        $statusClass = match ($selection->current_status) {
                            'Contratado' => 'success',
                            'Rechazado' => 'danger',
                            'Oferta Enviada' => 'info',
                            'En Entrevista' => 'primary',
                            'Prueba Técnica' => 'secondary',
                            'En Espera' => 'warning',
                            default => 'primary', // Seleccionado
                        };
                        $cvUnlocked = $worker ? in_array($worker->id, $cvUnlockedWorkerIds ?? []) : false;
                    @endphp

                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card shadow-sm h-100 worker-card border-0 rounded-4 overflow-hidden">
                            <div class="card-body p-4 text-center">
                                {{-- Foto de Perfil --}}
                                <img src="{{ optional(optional($worker)->user)->profile_picture ?? 'https://placehold.co/100x100/A0BFFF/FFFFFF?text=' . $initials }}"
                                    alt="Foto de {{ optional(optional($worker)->user)->name ?? 'Usuario Anónimo' }}"
                                    onerror="this.onerror=null;this.src='https://placehold.co/100x100/A0BFFF/FFFFFF?text={{ $initials }}';"
                                    class="rounded-circle mb-3 border border-4 border-white shadow-sm"
                                    style="width: 100px; height: 100px; object-fit: cover;">

                                {{-- Nombre y Profesión --}}
                                <h5 class="card-title fw-bold text-dark mb-0 worker-name-truncate"
                                    title="{{ optional(optional($worker)->user)->name ?? 'Usuario Anónimo' }}">
                                    {{ optional(optional($worker)->user)->name ?? 'Usuario Anónimo' }}</h5>
                                <p class="text-primary fw-semibold mb-2 profession-truncate"
                                    title="{{ optional($worker)->profession_title ?? 'Sin Título' }}">
                                    {{ optional($worker)->profession_title ?? 'Sin Título' }}
                                </p>

                                {{-- Estado de la Selección --}}
                                <div class="mb-3">
                                    <span class="d-block small text-muted mb-1">Estado actual:</span>
                                    <span class="badge bg-{{ $statusClass }} p-2 rounded-pill fw-bold shadow-sm">
                                        <i class="bi bi-circle-fill me-1 small"></i> {{ $selection->current_status }}
                                    </span>
                                </div>


                                {{-- Detalles Clave del Proceso --}}
                                <div class="text-start mb-3 border-top pt-3">
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-tag-fill me-2 text-primary"></i>
                                        Prioridad: <span class="fw-semibold">{{ $selection->priority ?? 'N/A' }}</span>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-calendar-check me-2 text-primary"></i>
                                        F. Selección: <span class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($selection->selection_date)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-cash-stack me-2 text-primary"></i>
                                        Salario Esperado: <span
                                            class="fw-semibold">{{ optional($worker)->min_expected_salary ? '$' . number_format($worker->min_expected_salary, 0, ',', '.') : 'N/A' }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('empresa.candidatos.seleccionados.edit', ['jobOffer' => $jobOffer, 'candidateSelection' => $selection]) }}"
                                    class="btn btn-sm btn-outline-primary w-100 mt-2 rounded-pill shadow-sm btn-modern">
                                    Gestionar Proceso de Selección
                                </a>

                                {{-- Botón de Acción --}}
                                @if ($worker)
                                    <a href="{{ route('empresa.trabajadores.show', ['workerProfile' => $worker, 'jobOffer' => $jobOffer]) }}"
                                        class="btn btn-sm btn-outline-primary w-100 mt-2 rounded-pill shadow-sm btn-modern cv-view-link"
                                        data-job-offer-id="{{ $jobOffer->id }}"
                                        data-unlock-url="{{ route('empresa.trabajadores.unlock', ['workerProfile' => $worker, 'jobOffer' => $jobOffer]) }}"
                                        data-cv-unlocked="{{ $cvUnlocked ? '1' : '0' }}">
                                        Ver Perfil
                                    </a>
                                @else
                                    <button
                                        class="btn btn-sm btn-outline-secondary w-100 mt-2 rounded-pill shadow-sm btn-modern"
                                        disabled>
                                        Perfil No Disponible
                                    </button>
                                @endif
                                {{-- Aquí podrías añadir un botón para editar el estado de selección si tuvieras la ruta --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-center">
                {{ $selectedCandidates->links() }}
            </div>
        @endif
    </div>

    <style>
        /* Estilos CSS (sin cambios) */
        .worker-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .worker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1) !important;
        }

        .empty-state {
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 1rem;
            border: 1px dashed #ced4da;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            line-height: 80px;
            font-size: 3rem;
            border-radius: 50%;
        }

        .btn-modern {
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
        }

        .worker-name-truncate,
        .profession-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <script>
        /**
         * Función para cambiar el campo de ordenación y la dirección.
         */
        function sort(field) {
            const currentSortBy = document.getElementById('sort_by');
            const currentSortDir = document.getElementById('sort_dir');

            if (currentSortBy.value === field) {
                currentSortDir.value = currentSortDir.value === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortBy.value = field;
                currentSortDir.value = 'desc';
            }

            document.getElementById('filter-form').submit();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const purchaseCreditUrl = null;

            const profileLinks = document.querySelectorAll('.cv-view-link');

            async function handleNoCredits(payload = {}) {
                const targetUrl = payload?.purchase_url || purchaseCreditUrl;
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Sin créditos de CV',
                    text: payload?.message ?? 'No tienes créditos disponibles para esta acción.',
                    showCancelButton: Boolean(targetUrl),
                    confirmButtonText: targetUrl ? 'Comprar créditos' : 'Aceptar',
                    cancelButtonText: 'Cancelar',
                });

                if (result.isConfirmed && targetUrl) {
                    window.location.href = targetUrl;
                }
            }

            profileLinks.forEach(link => {
                link.addEventListener('click', async function(event) {
                    if (link.dataset.cvUnlocked === '1') {
                        return;
                    }

                    event.preventDefault();

                    const confirmation = await Swal.fire({
                        icon: 'question',
                        title: '¿Estás seguro?',
                        text: 'Ver este perfil descontará un crédito de CV.',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar',
                    });

                    if (!confirmation.isConfirmed) {
                        return;
                    }

                    if (!csrfToken) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo verificar la sesión. Por favor intenta nuevamente.',
                        });
                        return;
                    }

                    const unlockUrl = link.dataset.unlockUrl;
                    const jobOfferId = link.dataset.jobOfferId;

                    if (!unlockUrl || !jobOfferId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo determinar la ruta para desbloquear el CV.',
                        });
                        return;
                    }

                    try {
                        const response = await fetch(unlockUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                job_offer_id: jobOfferId
                            }),
                        });

                        let payload = null;
                        try {
                            payload = await response.json();
                        } catch (_) {
                            payload = null;
                        }

                        if (response.status === 402 || payload?.status === 'no-credits') {
                            await handleNoCredits(payload);
                            return;
                        }

                        if (!response.ok || payload?.status === 'error') {
                            throw new Error(payload?.message ||
                                'No se pudo desbloquear el perfil.');
                        }

                        link.dataset.cvUnlocked = '1';
                        window.location.href = link.href;
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message ||
                                'Ocurrió un error al desbloquear el perfil.',
                        });
                    }
                });
            });
        });
    </script>
@endsection
