@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="display-4 text-primary mb-3">
                                <i class="bi bi-envelope-check-fill"></i>
                            </div>
                            <h1 class="h3 fw-bold mb-2">Verifica tu Email</h1>
                            <p class="text-muted">Paso final para activar tu cuenta.</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success d-flex gap-3" role="alert">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                                <div>{{ session('status') }}</div>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex gap-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                <div>Necesitas verificar tu correo electrónico para acceder a todas las funciones. Revisa tu bandeja de entrada.</div>
                            </div>
                        @endif

                        <p class="text-muted small mb-4">Si no encuentras el correo, revisa la carpeta de <strong>spam</strong> o solicita un nuevo enlace a continuación.</p>

                        <form method="POST" action="{{ route('verification.resend') }}" class="mb-4">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg w-100">Reenviar Email de Verificación</button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none">Volver a la página de inicio de sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
