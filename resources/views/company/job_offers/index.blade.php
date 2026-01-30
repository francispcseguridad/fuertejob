@extends('layouts.app')
@section('title', 'Listado de Ofertas')
@section('content')
    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">
                    <i class="bi bi-briefcase-fill me-2 text-primary"></i>Gestión de Ofertas
                </h1>
                <p class="text-muted lead">Administra tus vacantes publicadas ({{ $offers->count() }})</p>
            </div>
            <div>
                <span class="badge bg-light text-dark border p-2 rounded-pill me-2">
                    <i class="bi bi-calendar-event me-1"></i> {{ date('d/m/Y') }}
                </span>
                <a href="{{ route('empresa.ofertas.create') }}" class="btn btn-primary btn-modern shadow">
                    <i class="bi bi-plus-lg me-2"></i>Nueva Oferta
                </a>
            </div>
        </div>

        {{-- Mensajes de Estado --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Quick Stats Row --}}
        @php
            $publicadas = $offers->where('status', 'Publicado')->count();
            $borradores = $offers->where('status', 'Borrador')->count();
            $finalizadas = $offers->where('status', 'Finalizada')->count();
        @endphp
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-success h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-success fw-bold mb-1">Publicadas</h6>
                            <h2 class="mb-0 fw-bold">{{ $publicadas }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-check-circle fs-1 text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-warning h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-warning fw-bold mb-1">Borradores</h6>
                            <h2 class="mb-0 fw-bold">{{ $borradores }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-pencil-square fs-1 text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-danger h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-danger fw-bold mb-1">Finalizadas</h6>
                            <h2 class="mb-0 fw-bold">{{ $finalizadas }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-x-circle fs-1 text-danger opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros de Búsqueda y Estado --}}
        <div class="card shadow-sm filter-card mb-5">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">
                    <i class="bi bi-funnel-fill me-2 text-primary"></i>Filtros de Búsqueda
                </h5>
                <form id="filter-form" action="{{ route('empresa.ofertas.index') }}" method="GET">
                    <input type="hidden" name="sort_by" id="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_dir" id="sort_dir" value="{{ $sortDir }}">

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="search" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-search me-1"></i>Buscar por Título o Ubicación
                            </label>
                            <input type="text" name="search" id="search" value="{{ $search ?? '' }}"
                                class="form-control form-control-lg rounded-pill" placeholder="Ej: Desarrollador, Madrid">
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-filter me-1"></i>Estado
                            </label>
                            <select id="status" name="status" class="form-select form-select-lg rounded-pill">
                                <option value="">Todos</option>
                                <option value="Publicada" {{ ($statusFilter ?? '') === 'Publicada' ? 'selected' : '' }}>
                                    Publicada
                                </option>
                                <option value="Borrador" {{ ($statusFilter ?? '') === 'Borrador' ? 'selected' : '' }}>
                                    Borrador
                                </option>
                                <option value="Finalizada" {{ ($statusFilter ?? '') === 'Finalizada' ? 'selected' : '' }}>
                                    Finalizada
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-modern flex-grow-1">
                                <i class="bi bi-search me-2"></i>Aplicar
                            </button>
                            @if (!empty($search) || !empty($statusFilter))
                                <a href="{{ route('empresa.ofertas.index') }}"
                                    class="btn btn-outline-secondary btn-modern">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Contenido Principal --}}
        @if ($offers->isEmpty())
            <div class="empty-state text-center shadow">
                <div class="icon-circle bg-white shadow-sm mx-auto mb-4">
                    <i class="bi bi-inbox text-muted"></i>
                </div>
                @if (!empty($search) || !empty($statusFilter))
                    <h4 class="fw-bold text-dark mb-2">No se encontraron ofertas</h4>
                    <p class="text-muted mb-4">No hay ofertas que coincidan con los filtros aplicados.</p>
                    <a href="{{ route('empresa.ofertas.index') }}" class="btn btn-primary btn-modern">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Mostrar todas
                    </a>
                @else
                    <h4 class="fw-bold text-dark mb-2">Aún no tienes ofertas publicadas</h4>
                    <p class="text-muted mb-4">Comienza a encontrar talento publicando tu primera vacante.</p>
                    <a href="{{ route('empresa.ofertas.create') }}" class="btn btn-primary btn-modern">
                        <i class="bi bi-plus-lg me-2"></i>Publicar Primera Oferta
                    </a>
                @endif
            </div>
        @else
            <div class="card shadow-sm table-modern">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="px-4 py-3 cursor-pointer" onclick="sort('title')">
                                    <div class="d-flex align-items-center">
                                        TÍTULO
                                        @if ($sortBy === 'title')
                                            <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-2"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3">UBICACIÓN / CONTRATO</th>
                                <th scope="col" class="px-4 py-3">ESTADO</th>
                                <th scope="col" class="px-4 py-3 cursor-pointer" onclick="sort('created_at')">
                                    <div class="d-flex align-items-center">
                                        FECHA
                                        @if ($sortBy === 'created_at')
                                            <i class="bi bi-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-2"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-4 py-3 text-end">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offers as $offer)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark">{{ $offer->title }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            {{ $offer->salary_range ?? 'No especificado' }}
                                        </small>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-dark">
                                            <i class="bi bi-geo-alt-fill me-1 text-primary"></i>{{ $offer->location }}
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-file-text me-1"></i>{{ $offer->contract_type }}
                                        </small>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badgeClass = match ($offer->status) {
                                                'Publicada' => 'bg-success',
                                                'Borrador' => 'bg-warning',
                                                'Finalizada' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} badge-modern">
                                            {{ $offer->status }}
                                        </span>
                                        @if ($offer->is_premium)
                                            <i class="bi bi-star-fill text-warning ms-1" title="Oferta Premium"></i>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-dark">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $offer->created_at->format('d/m/Y') }}
                                        </div>
                                        @if ($offer->expires_at)
                                            <small class="text-danger">
                                                <i class="bi bi-clock-history me-1"></i>
                                                Expira: {{ $offer->expires_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('empresa.ofertas.edit', $offer) }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="bi bi-pencil-fill me-1"></i>Gestionar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script>
        /**
         * Función para cambiar el campo de ordenación y la dirección.
         * @param {string} field - El campo a ordenar ('title' o 'created_at').
         */
        function sort(field) {
            const currentSortBy = document.getElementById('sort_by');
            const currentSortDir = document.getElementById('sort_dir');

            // Si se hace clic en el mismo campo, invertir la dirección
            if (currentSortBy.value === field) {
                currentSortDir.value = currentSortDir.value === 'asc' ? 'desc' : 'asc';
            } else {
                // Si se hace clic en un campo diferente, establecer el nuevo campo y la dirección por defecto (descendente)
                currentSortBy.value = field;
                currentSortDir.value = 'desc';
            }

            // Enviar el formulario para aplicar la ordenación
            document.getElementById('filter-form').submit();
        }
    </script>
@endsection
