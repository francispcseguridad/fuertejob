@extends('layouts.app')

@section('content')
    {{-- Inclusión de Bootstrap (requerida para estilos) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center rounded-top">
                        <h2 class="h4 mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Editar Habilidad: {{ $skill->name }}
                        </h2>
                    </div>
                    <div class="card-body p-4">

                        {{-- Formulario de Edición --}}
                        {{-- 
                            1. Usa la ruta 'update' para enviar la petición.
                            2. Pasa el modelo $skill (o su ID) a la ruta para identificar qué recurso actualizar.
                            3. Usa el método POST y luego el @method('PUT') para simular una petición PUT/PATCH.
                        --}}
                        <form action="{{ route('worker.habilidades.update', $skill->id) }}" method="POST">
                            @csrf
                            @method('PUT') {{-- O PATCH, ambos son válidos para la actualización --}}

                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Nombre de la Habilidad</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $skill->name) }}"
                                    {{-- Usa old() para rellenar en caso de error, y $skill->name como valor predeterminado --}} required
                                    placeholder="Ej: Desarrollo Web, Liderazgo, Contabilidad">

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                    <i class="bi bi-floppy-fill me-2"></i> Guardar Cambios
                                </button>
                                <a href="{{ route('worker.habilidades.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left-circle me-2"></i> Cancelar y Volver
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script para añadir íconos de Bootstrap --}}
    <script>
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css';
        document.head.appendChild(link);
    </script>
@endsection
