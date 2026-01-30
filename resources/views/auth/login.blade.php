@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/logofuertejob.png') }}" alt="FuerteJob Logo" class="img-fluid mb-3"
                                style="max-height: 80px;">
                            <h2 class="fw-bold text-dark">Bienvenido de nuevo</h2>
                            <p class="text-muted">Ingresa a tu cuenta para continuar</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold text-secondary">Correo
                                    Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i
                                            class="bi bi-envelope"></i></span>
                                    <input id="email" type="email"
                                        class="form-control border-start-0 ps-0 py-2 @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                        placeholder="nombre@ejemplo.com">
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-secondary">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i
                                            class="bi bi-lock"></i></span>
                                    <input id="password" type="password"
                                        class="form-control border-start-0 ps-0 py-2 @error('password') is-invalid @enderror"
                                        name="password" required autocomplete="current-password" placeholder="••••••••">
                                    <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                        data-target="password" aria-label="Mostrar contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @include('components.password_requirements')
                                @error('password')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted small" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="text-decoration-none small text-primary fw-semibold"
                                        href="{{ route('password.request') }}">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit"
                                    class="btn btn-primary d-block w-100 py-2 rounded-pill fw-bold shadow-sm btn-modern"
                                    style="background-color:#1c476b;">
                                    Iniciar Sesión <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>

                            <div class="text-center mt-4">
                                <p class="text-muted small mb-0">¿Aún no tienes cuenta?</p>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <a href="{{ route('worker.register.form') }}"
                                        class="text-decoration-none fw-semibold text-primary">Regístrate como Candidato</a>
                                    <span class="text-muted">|</span>
                                    <a href="{{ route('company.register.create') }}"
                                        class="text-decoration-none fw-semibold text-primary">Soy Empresa</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
