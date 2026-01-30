@extends('layouts.app')

@section('title', 'Crear Texto Loop')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 fw-bold text-primary">Crear Texto Loop</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.home_loop_texts.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="content" class="form-label fw-medium">Contenido</label>
                                <textarea name="content" id="content" rows="4" class="form-control" required
                                    placeholder="Ej: · Tu Próximo Éxito Comienza Aquí..."></textarea>
                                <div class="form-text">Este texto se repetirá en el banner.</div>
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                    value="1" checked>
                                <label class="form-check-label" for="is_active">Activo</label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.home_loop_texts.index') }}"
                                    class="btn btn-light border">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
