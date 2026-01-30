@extends('layouts.app')
@section('title', 'Gestión de Candidatos')

@section('content')
    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">Gestión de Candidatos</h1>
                <p class="text-muted lead">Administración de usuarios registrados con perfil de candidato.</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Stats / Total -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm stat-card border-primary">
                    <div class="card-body">
                        <h6 class="text-uppercase text-primary fw-bold mb-1">Total Registros</h6>
                        <h2 class="mb-0 fw-bold">{{ $workers->total() }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters + Export -->
        <form method="get" class="row g-3 align-items-end mb-4">
            <div class="col-sm-6 col-md-3">
                <label class="form-label small text-uppercase text-muted">Nombre</label>
                <input type="text" name="name" value="{{ $filters['name'] ?? '' }}"
                    class="form-control form-control-sm" placeholder="Buscar por nombre">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small text-uppercase text-muted">Email</label>
                <input type="text" name="email" value="{{ $filters['email'] ?? '' }}"
                    class="form-control form-control-sm" placeholder="Buscar por email">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small text-uppercase text-muted">Ciudad</label>
                <input type="text" name="city" value="{{ $filters['city'] ?? '' }}"
                    class="form-control form-control-sm" placeholder="Ciudad">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label small text-uppercase text-muted">País</label>
                <input type="text" name="country" value="{{ $filters['country'] ?? '' }}"
                    class="form-control form-control-sm" placeholder="País">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.candidatos.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </a>
            </div>
            <div class="col-auto ms-auto d-flex gap-2">
                <a href="{{ route('admin.candidatos.index', array_merge(request()->except(['export', 'page']), ['export' => 'csv'])) }}"
                    class="btn btn-outline-success btn-sm rounded-pill px-3">
                    <i class="bi bi-download me-1"></i> CSV
                </a>
                <a href="{{ route('admin.candidatos.index', array_merge(request()->except(['export', 'page']), ['export' => 'pdf'])) }}"
                    class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> PDF
                </a>
            </div>
        </form>

        <!-- Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Table Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive table-modern">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Nombre</th>
                                <th class="px-4 py-3 text-secondary text-uppercase small fw-bold d-none d-lg-table-cell">
                                    Email</th>
                                <th
                                    class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center d-none d-md-table-cell">
                                    Ciudad</th>
                                <th
                                    class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center d-none d-lg-table-cell">
                                    País</th>
                                <th class="px-4 py-3 text-center text-secondary text-uppercase small fw-bold">Estado</th>
                                <th class="px-4 py-3 text-end text-secondary text-uppercase small fw-bold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($workers as $worker)
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-break">{{ $worker->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 d-none d-lg-table-cell">
                                        <span class="text-muted text-break">{{ $worker->email }}</span>
                                    </td>
                                    @php
                                        $profile = $worker->workerProfile;
                                    @endphp
                                    <td class="px-4 text-center d-none d-md-table-cell">
                                        <span class="text-muted small">{{ $profile->city ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 text-center d-none d-lg-table-cell">
                                        <span class="text-muted small">{{ $profile->country ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 text-center">
                                        @if ($worker->email_verified_at)
                                            <div class="rounded-circle bg-success mx-auto shadow-sm"
                                                style="width: 12px; height: 12px;" title="Verificado"></div>
                                        @else
                                            <div class="rounded-circle bg-warning mx-auto shadow-sm"
                                                style="width: 12px; height: 12px;" title="Pendiente"></div>
                                        @endif
                                    </td>
                                    <td class="px-4 text-end">
                                        <a href="{{ route('admin.candidatos.edit', $worker->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded-circle p-2" title="Editar">
                                            <i class="bi bi-pencil-square" style="font-size: 1.1rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="bi bi-people text-muted opacity-25" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-3">No se encontraron candidatos registrados.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Footer -->
            @if ($workers->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $workers->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
