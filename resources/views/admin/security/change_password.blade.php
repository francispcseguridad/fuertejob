@extends('layouts.app')

@section('title', 'Seguridad – Cambiar Contraseña')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white">
                        <h4 class="mb-0 fw-bold">Seguridad</h4>
                        <p class="text-muted small mb-0">Actualiza tu contraseña de administrador.</p>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success mb-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.security.password.update') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contraseña actual</label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="admin_security_current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        placeholder="********">
                                    <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                        data-target="admin_security_current_password"
                                        aria-label="Mostrar contraseña actual">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nueva contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="admin_security_password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Mín. 8 caracteres">
                                    <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                        data-target="admin_security_password" aria-label="Mostrar nueva contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirmar contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation"
                                        id="admin_security_password_confirmation" class="form-control"
                                        placeholder="Repite la nueva contraseña">
                                    <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                        data-target="admin_security_password_confirmation"
                                        aria-label="Mostrar confirmación">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                @include('components.password_requirements')
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-bold">
                                    Guardar nueva contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
