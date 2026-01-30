@extends('layout.app')

@section('title', 'Editar Banner')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 fw-bold text-primary">Editar Banner: {{ $homeHero->title }}</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.home_heroes.update', $homeHero->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title" class="form-label fw-medium">Título Principal</label>
                                <input type="text" name="title" id="title" class="form-control"
                                    value="{{ old('title', $homeHero->title) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="subtitle" class="form-label fw-medium">Subtítulo</label>
                                <input type="text" name="subtitle" id="subtitle" class="form-control"
                                    value="{{ old('subtitle', $homeHero->subtitle) }}">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="button1_text" class="form-label fw-medium">Texto Botón 1</label>
                                    <input type="text" name="button1_text" id="button1_text" class="form-control"
                                        value="{{ old('button1_text', $homeHero->button1_text) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="button1_url" class="form-label fw-medium">URL Botón 1</label>
                                    <input type="text" name="button1_url" id="button1_url" class="form-control"
                                        value="{{ old('button1_url', $homeHero->button1_url) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="button2_text" class="form-label fw-medium">Texto Botón 2</label>
                                    <input type="text" name="button2_text" id="button2_text" class="form-control"
                                        value="{{ old('button2_text', $homeHero->button2_text) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="button2_url" class="form-label fw-medium">URL Botón 2</label>
                                    <input type="text" name="button2_url" id="button2_url" class="form-control"
                                        value="{{ old('button2_url', $homeHero->button2_url) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="background_image" class="form-label fw-medium">URL Imagen Fondo</label>
                                <input type="text" name="background_image" id="background_image" class="form-control"
                                    value="{{ old('background_image', $homeHero->background_image) }}">
                            </div>

                            <div class="mb-4 form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                    value="1" {{ $homeHero->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Activo</label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.home_heroes.index') }}" class="btn btn-light border">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-repeat me-1"></i> Actualizar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
