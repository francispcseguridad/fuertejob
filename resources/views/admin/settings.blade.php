@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                            <span class="me-3 text-primary fs-3"><i class="bi bi-gear-fill"></i></span>
                            <div>
                                <h1 class="h4 fw-bold mb-0">Configuración del Sistema</h1>
                                <small class="text-muted">Ajustes globales, permisos y parámetros de la plataforma.</small>
                            </div>
                        </div>

                        <form method="POST">
                            @csrf
                            <div class="mb-4">
                                <h2 class="h5 fw-semibold text-secondary">Ajustes Generales</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="site_name" class="form-label">Nombre de la Plataforma</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" value="TalentoDigital">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="admin_email" class="form-label">Correo de Contacto Administrativo</label>
                                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@plataforma.com">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h2 class="h5 fw-semibold text-secondary">Parámetros de Solicitantes</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="max_experiences" class="form-label">Máximo de Experiencias Laborales</label>
                                        <input type="number" min="1" class="form-control" id="max_experiences" name="max_experiences" value="5">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cv_upload_limit" class="form-label">Límite de Tamaño de CV (MB)</label>
                                        <input type="number" min="1" class="form-control" id="cv_upload_limit" name="cv_upload_limit" value="5">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
