@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5 text-center">
                        <div class="display-4 text-success mb-4">
                            <i class="bi bi-envelope-check"></i>
                        </div>
                        <h1 class="h3 fw-bold mb-3">¡Muchas gracias por registrarte!</h1>
                        <p class="lead text-muted">Tu solicitud se ha procesado correctamente.</p>
                        <div class="alert alert-primary mt-4">
                            Se ha enviado un email de confirmación a tu correo electrónico. Revísalo para activar tu cuenta.
                        </div>
                        <p class="text-muted small mb-0">Si no lo ves en la bandeja de entrada, revisa la carpeta de <strong>SPAM</strong> o correo no deseado.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
