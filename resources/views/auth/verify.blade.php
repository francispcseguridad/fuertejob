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
                            <h2 class="fw-bold text-dark">Restablece tu Contraseña</h2>
                            <p class="text-muted">Ingresa el correo asociado a tu cuenta y te enviaremos un enlace para
                                crear una nueva contraseña.</p>
                        </div>

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold text-secondary">Correo
                                    Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input id="email" type="email" name="email"
                                        class="form-control border-start-0 ps-0 py-2 @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" required autofocus placeholder="nombre@ejemplo.com">
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block mt-1" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit"
                                    class="btn btn-primary d-block w-100 py-2 rounded-pill fw-bold shadow-sm btn-modern">
                                    Enviar enlace de restablecimiento <i class="bi bi-send-fill ms-2"></i>
                                </button>
                            </div>

                            <div class="text-center mt-4 pt-4 border-top border-light">
                                <p class="text-muted small mb-0">
                                    Si ya recuerdas tu contraseña, vuelve a
                                    <a href="{{ route('login') }}"
                                        class="text-decoration-none fw-semibold text-primary">Iniciar Sesión</a>.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
