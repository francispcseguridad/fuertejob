@extends('layouts.app')

@section('title', 'Usuarios Corporativos')

@section('content')
    <div class="container py-4">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1">Usuarios Corporativos</h1>
                <p class="text-muted mb-0">Administra los asientos que tienes disponibles para crear usuarios vinculados a tu
                    compañía.</p>
            </div>
            <div>
                <span class="badge bg-light border border-2 text-dark fs-6">
                    Asientos disponibles: {{ $resourceBalance->available_user_seats ?? 0 }}
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Balance de asientos</h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <small class="text-muted">Total</small>
                                <div class="fw-bold fs-4">{{ $resourceBalance->total_user_seats ?? 0 }}</div>
                            </div>
                            <div>
                                <small class="text-muted">Usados</small>
                                <div class="fw-bold fs-4">{{ $resourceBalance->used_user_seats ?? 0 }}</div>
                            </div>
                            <div>
                                <small class="text-muted">Disponibles</small>
                                <div class="fw-bold fs-4 text-success">{{ $resourceBalance->available_user_seats ?? 0 }}
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Cada usuario creado consume 1 asiento. Compra bonos para recargar
                            los asientos disponibles.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Crear nuevo usuario</h5>
                        <form method="POST" action="{{ route('empresa.usuarios.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label small text-uppercase text-muted">Nombre
                                    completo</label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" maxlength="255" required>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label small text-uppercase text-muted">Correo
                                    electrónico</label>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" maxlength="255" required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password"
                                        class="form-label small text-uppercase text-muted">Contraseña</label>
                                    <div class="input-group">
                                        <input type="password"
                                            class="form-control form-control-lg @error('password') is-invalid @enderror"
                                            id="password" name="password" minlength="8" required>
                                        <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                            data-target="password" aria-label="Mostrar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation"
                                        class="form-label small text-uppercase text-muted">Confirmar contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password_confirmation"
                                            name="password_confirmation" minlength="8" required>
                                        <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                            data-target="password_confirmation"
                                            aria-label="Mostrar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @include('components.password_requirements')
                            <div class="mt-3">
                                <small class="text-muted">Se enviará un correo de verificación al usuario creado.</small>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-person-plus me-2"></i>Crear usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0">
                <h5 class="fw-bold mb-0 text-dark">Usuarios creados</h5>
                <p class="text-muted small mb-0">Se refleja la última actividad de asientos para tu empresa.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Creado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($memberships as $membership)
                                <tr>
                                    <td>{{ optional($membership->user)->name ?? 'Usuario eliminado' }}</td>
                                    <td>{{ optional($membership->user)->email ?? '—' }}</td>
                                    <td>{{ $membership->created_at ? $membership->created_at->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill px-3 py-1 fs-6 {{ optional($membership->user)->email_verified_at ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}">
                                            {{ optional($membership->user)->email_verified_at ? 'Activo' : 'Pendiente' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No has creado usuarios corporativos todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $memberships->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
