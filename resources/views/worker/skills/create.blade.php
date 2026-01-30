@extends('layouts.app')

@section('content')
    {{-- Inclusión de Bootstrap (requerida para estilos) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <div class="container my-5">
        <header class="mb-4 pb-3 border-bottom">
            <h1 class="display-5 fw-bold text-dark">Añadir Nueva Habilidad</h1>
            <p class="lead text-muted">Introduce el nombre de la nueva habilidad que deseas registrar.</p>
        </header>

        {{-- Contenedor del Formulario (Bootstrap Card) --}}
        <div class="card shadow-lg mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-tools me-2"></i> Detalles de la Habilidad</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('worker.habilidades.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nombre de la Habilidad</label>
                        {{-- Añadimos la clase is-invalid si hay error --}}
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Ej: Liderazgo, Gestión de Proyectos, Comunicación efectiva">

                        @error('name')
                            {{-- Feedback visual de error de Bootstrap --}}
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3">
                        {{-- Botón primario Guardar --}}
                        <button type="submit" class="btn btn-success btn-lg shadow-sm">
                            <i class="bi bi-save me-2"></i> Guardar Habilidad
                        </button>

                        {{-- Botón secundario Cancelar, ahora en azul/gris como pediste --}}
                        <a href="{{ route('worker.habilidades.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left-circle me-1"></i> Cancelar y Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
