@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/logofuertejob.png') }}" alt="FuerteJob Logo" class="img-fluid mb-3"
                                style="max-height: 80px;">
                            <h2 class="fw-bold text-dark">Verifica tu Correo</h2>
                            <p class="text-muted">Antes de continuar, por favor verifica tu dirección de correo electrónico
                                haciendo clic en el enlace que te acabamos de enviar.</p>
                        </div>

                        <!-- Mensaje de Instrucción -->
                        <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                <div>
                                    Se ha enviado un nuevo enlace de verificación a la dirección de correo proporcionada
                                    durante el registro.
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje de Sesión (Éxito al reenviar) -->
                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                                <div class="d-flex">
                                    <i class="bi bi-check-circle-fill me-2 mt-1"></i>
                                    <div>
                                        Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mt-2">
                            <!-- Botón de Reenvío de Verificación -->
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-primary w-100 py-2 rounded-pill fw-bold btn-modern">
                                        Reenviar Email <i class="bi bi-envelope-at ms-1"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Botón de Logout -->
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-outline-secondary w-100 py-2 rounded-pill fw-bold">
                                        Cerrar Sesión <i class="bi bi-box-arrow-right ms-1"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center mt-4 pt-4 border-top border-light">
                            <p class="text-muted small mb-0">
                                ¿No recibiste el correo? Revisa tu carpeta de <strong>Spam</strong> o solicita uno nuevo.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
