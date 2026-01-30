@extends('layouts.app')

@section('title', 'Listado de Perfiles de Trabajadores')

@section('content')
    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Talento en Búsqueda
                </h1>
                <p class="text-muted lead">Explora perfiles de trabajadores listos para nuevas oportunidades
                    ({{ $workers->total() ?? 0 }})</p>
            </div>
            <div>
                <span class="badge bg-light text-dark border p-2 rounded-pill me-2">
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
                    <i class="bi bi-funnel-fill me-2 text-primary"></i>Filtros de Búsqueda
                </h5>
                <form id="filter-form" action="{{ route('empresa.trabajadores.index') }}" method="GET">
                    <input type="hidden" name="sort_by" id="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_dir" id="sort_dir" value="{{ $sortDir }}">

                    <div class="row g-3">
                        {{-- 1. Buscador General --}}
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-search me-1"></i>Título, Nombre o Bio
                            </label>
                            <input type="text" name="search" id="search" value="{{ $search ?? '' }}"
                                class="form-control form-control-lg rounded-pill"
                                placeholder="Ej: Desarrollador, Juan Pérez">
                        </div>

                        {{-- 2. Filtro de Modalidad --}}
                        <div class="col-md-3">
                            <label for="modality" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-geo-alt-fill me-1"></i>Modalidad Preferida
                            </label>
                            <select id="modality" name="modality" class="form-select form-select-lg rounded-pill">
                                <option value="">Todas</option>
                                @foreach ($modalities as $modality)
                                    <option value="{{ $modality }}"
                                        {{ ($modalityFilter ?? '') === $modality ? 'selected' : '' }}>
                                        {{ ucfirst($modality) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Filtro de Tags (Skills, Tools, Languages) --}}
                        <div class="col-md-3">
                            <label for="tags" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-tags-fill me-1"></i>Habilidades/Idiomas/Herramientas
                            </label>
                            <input type="text" name="tags" id="tags" value="{{ $tags ?? '' }}"
                                class="form-control form-control-lg rounded-pill" placeholder="Ej: Python, Inglés, Figma">
                            <div class="form-text mt-1 text-muted small">Separa los términos con comas (e.g., SQL, Java,
                                Francés).</div>
                        </div>

                        {{-- 4. Botones de Acción --}}
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-modern flex-grow-1">
                                <i class="bi bi-search me-2"></i>Aplicar
                            </button>
                            @if (!empty($search) || !empty($modalityFilter) || !empty($tags))
                                <a href="{{ route('empresa.trabajadores.index') }}"
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
        @if ($workers->isEmpty())
            <div class="empty-state text-center shadow">
                <div class="icon-circle bg-white shadow-sm mx-auto mb-4">
                    <i class="bi bi-people-fill text-muted"></i>
                </div>
                @if (!empty($search) || !empty($modalityFilter) || !empty($tags))
                    <h4 class="fw-bold text-dark mb-2">No se encontraron perfiles</h4>
                    <p class="text-muted mb-4">No hay trabajadores que coincidan con los filtros aplicados.</p>
                @else
                    <h4 class="fw-bold text-dark mb-2">Aún no hay perfiles públicos disponibles</h4>
                    <p class="text-muted mb-4">Vuelve más tarde o ajusta tus criterios de búsqueda.</p>
                @endif
            </div>
        @else
            <div class="row g-4 mb-4">
                @foreach ($workers as $worker)
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card shadow-sm h-100 worker-card border-0 rounded-4 overflow-hidden">
                            <div class="card-body p-4 text-center">
                                {{-- Foto de Perfil --}}
                                @php
                                    // Determinar iniciales para el placeholder
                                    $initials = '';
                                    if ($worker->user->name ?? false) {
                                        $parts = explode(' ', $worker->user->name);
                                        $initials = strtoupper(
                                            substr($parts[0], 0, 1) .
                                                (count($parts) > 1 ? substr(end($parts), 0, 1) : ''),
                                        );
                                    } else {
                                        $initials = 'JP';
                                    }
                                @endphp
                                <img src="{{ $worker->user->profile_picture ?? 'https://placehold.co/100x100/A0BFFF/FFFFFF?text=' . $initials }}"
                                    alt="Foto de {{ $worker->user->name }}"
                                    onerror="this.onerror=null;this.src='https://placehold.co/100x100/A0BFFF/FFFFFF?text={{ $initials }}';"
                                    class="rounded-circle mb-3 border border-4 border-white shadow-sm"
                                    style="width: 100px; height: 100px; object-fit: cover;">

                                {{-- Nombre y Profesión --}}
                                <h5 class="card-title fw-bold text-dark mb-0">
                                    {{ $worker->user->name ?? 'Usuario Anónimo' }}</h5>
                                <p class="text-primary fw-semibold mb-2">{{ $worker->profession_title ?? 'Sin Título' }}
                                </p>

                                {{-- Detalles Clave --}}
                                <div class="text-start mb-3">
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                                        {{ $worker->city ?? 'N/A' }}, {{ $worker->country ?? 'N/A' }}
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="bi bi-house-door-fill me-2 text-primary"></i>
                                        Modalidad: <span
                                            class="fw-semibold">{{ ucfirst($worker->preferred_modality ?? 'N/A') }}</span>
                                    </div>
                                </div>

                                {{-- Habilidades Principales --}}
                                @php
                                    $allTags = $worker->skills->merge($worker->tools)->merge($worker->languages);
                                @endphp

                                @if ($allTags->isNotEmpty())
                                    <div class="skills-list mb-3 text-start">
                                        <span class="fw-semibold small text-muted d-block mb-1">Tags Clave:</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($allTags->take(4) as $tag)
                                                <span
                                                    class="badge bg-light text-secondary border border-secondary fw-normal rounded-pill">
                                                    {{ $tag->name ?? $tag->language_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Botón de Acción --}}
                                <a href="{{ route('empresa.ofertas.index') }}"
                                    class="btn btn-sm btn-primary w-100 mt-2 rounded-pill shadow-sm btn-modern">
                                    Ver Perfil Completo <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="d-flex justify-content-center">
                {{ $workers->links() }}
            </div>
        @endif
    </div>

    <style>
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
    </style>

    <script>
        /**
         * Función para cambiar el campo de ordenación y la dirección (similar al index de ofertas).
         * @param {string} field - El campo a ordenar.
         */
        function sort(field) {
            const currentSortBy = document.getElementById('sort_by');
            const currentSortDir = document.getElementById('sort_dir');

            if (currentSortBy.value === field) {
                currentSortDir.value = currentSortDir.value === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortBy.value = field;
                currentSortDir.value = 'desc'; // Por defecto, descendente
            }

            document.getElementById('filter-form').submit();
        }
    </script>
@endsection
