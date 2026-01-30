@extends('layouts.app')
@section('title', 'Gestión de Empresas')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">Empresas registradas</h1>
                <p class="text-muted lead mb-0">Administra cuentas corporativas y ajusta sus datos clave.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Volver al panel
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-primary fw-bold mb-1">Total de empresas</h6>
                        <h2 class="fw-bold mb-0">{{ $companies->total() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <form method="get" class="row g-3 align-items-end mb-4">
            <div class="col-sm-6 col-md-4">
                <label class="form-label small text-muted text-uppercase">Empresa</label>
                <input type="text" name="company_name" value="{{ $filters['company_name'] ?? '' }}"
                    class="form-control form-control-sm" placeholder="Nombre de empresa">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small text-muted text-uppercase">Activo</label>
                <select name="activo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="1" {{ isset($filters['activo']) && $filters['activo'] === '1' ? 'selected' : '' }}>
                        Activo</option>
                    <option value="2" {{ isset($filters['activo']) && $filters['activo'] === '2' ? 'selected' : '' }}>
                        Inactivo</option>
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 justify-content-between">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.empresas.index') }}"
                        class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.empresas.index', array_merge(request()->except(['export', 'page']), ['export' => 'csv'])) }}"
                        class="btn btn-outline-success btn-sm rounded-pill px-3">
                        <i class="bi bi-download me-1"></i> CSV
                    </a>
                    <a href="{{ route('admin.empresas.index', array_merge(request()->except(['export', 'page']), ['export' => 'pdf'])) }}"
                        class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> PDF
                    </a>
                </div>
            </div>
        </form>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-2 text-secondary text-uppercase small fw-bold">Empresa</th>
                                <th class="px-3 py-2 text-secondary text-uppercase small fw-bold d-none d-md-table-cell">NIF
                                </th>
                                <th class="px-3 py-2 text-secondary text-uppercase small fw-bold d-none d-xxl-table-cell">
                                    Dirección</th>
                                <th class="px-3 py-2 text-secondary text-uppercase small fw-bold d-none d-lg-table-cell">
                                    Teléfono</th>
                                <th class="px-3 py-2 text-secondary text-uppercase small fw-bold d-none d-lg-table-cell">
                                    Email Contacto</th>
                                <th class="px-3 py-2 text-center text-secondary text-uppercase small fw-bold">Activo</th>
                                <th class="px-3 py-2 text-end text-secondary text-uppercase small fw-bold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companies as $company)
                                @php
                                    $profile = optional($company->companyProfile);
                                @endphp
                                <tr>
                                    <td class="px-3">
                                        <div class="fw-semibold text-dark text-break">
                                            {{ $profile->company_name ?? $company->name }}
                                        </div>
                                        <small class="text-muted text-break">{{ $company->name }}</small>
                                    </td>
                                    <td class="px-3 text-muted text-break d-none d-md-table-cell">
                                        <span style="font-size: 0.8rem;">{{ $profile->vat_id ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-3 text-muted text-break d-none d-xxl-table-cell">
                                        <span style="font-size: 0.85rem;">{{ $profile->fiscal_address ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-3 text-muted text-break d-none d-lg-table-cell">
                                        {{ $profile->contact_phone ?? 'N/A' }}</td>
                                    <td class="px-3 text-muted text-break d-none d-lg-table-cell">
                                        {{ $profile->contact_email ?? 'N/A' }}</td>
                                    <td class="px-3 text-center">
                                        @if ((int) $profile->activo === 1)
                                            <div class="rounded-circle bg-success mx-auto shadow-sm"
                                                style="width: 12px; height: 12px;" title="Activo"></div>
                                        @else
                                            <div class="rounded-circle bg-danger mx-auto shadow-sm"
                                                style="width: 12px; height: 12px;" title="Inactivo"></div>
                                        @endif
                                    </td>
                                    <td class="px-3 text-end">
                                        <a href="{{ route('admin.empresas.edit', $company->id) }}"
                                            class="btn btn-sm btn-outline-primary p-2" title="Editar">
                                            <i class="bi bi-pencil-square" style="font-size: 1.1rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-buildings me-2"></i>No hay empresas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($companies->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $companies->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
