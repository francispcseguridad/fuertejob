@extends('layouts.app')

@section('content')
    {{-- Inclusión de Bootstrap y jQuery (Necesario para modales y manipulación de DOM) --}}
    {{-- jQuery UI para Autocomplete --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    {{-- Estilos personalizados para que el autocomplete aparezca sobre el modal --}}
    <style>
        /* El modal de Bootstrap tiene z-index: 1055, así que el autocomplete debe ser mayor */
        .ui-autocomplete {
            z-index: 1060 !important;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Mejorar la apariencia del autocomplete */
        .ui-menu-item {
            font-size: 0.95rem;
        }

        .ui-menu-item-wrapper {
            padding: 8px 12px;
        }

        .ui-state-active {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }
    </style>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="bi bi-translate text-primary me-2"></i>
                            Gestión de Idiomas
                        </h1>
                        <p class="text-muted mb-0">Demuestra tu capacidad de comunicación global</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createLanguageModal">
                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir Idioma
                    </button>
                </div>

                {{-- Navigation Breadcrumb --}}
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('worker.dashboard') }}">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Idiomas</li>
                    </ol>
                </nav>

                {{-- Mensaje de éxito/notificación --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Search and Filter Section --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" id="searchLanguage" class="form-control"
                                        placeholder="Buscar idioma...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="filterLevel" class="form-select">
                                    <option value="">Todos los niveles</option>
                                    <option value="Básico">Básico</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                    <option value="Nativo">Nativo</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100" type="button" id="btnClear">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Languages Grid --}}
                <div class="row g-3" id="languagesList">
                    @forelse ($languages as $language)
                        <div class="col-md-6 col-lg-4 language-item" data-name="{{ strtolower($language->name) }}"
                            data-level="{{ $language->pivot->level ?? '' }}">
                            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all"
                                style="overflow: visible;">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="bi bi-translate text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-bold text-truncate" style="max-width: 150px;"
                                                title="{{ ucfirst($language->name) }}">
                                                {{ ucfirst($language->name) }}
                                            </h5>
                                            @if ($language->pivot->level)
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $language->pivot->level }}
                                                </span>
                                            @else
                                                <span class="text-muted small">Nivel no especificado</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <button class="dropdown-item edit-btn" type="button" data-bs-toggle="modal"
                                                    data-bs-target="#editLanguageModal" data-id="{{ $language->id }}"
                                                    data-name="{{ $language->name }}"
                                                    data-level="{{ $language->pivot->level ?? '' }}">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i> Editar
                                                </button>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button class="dropdown-item delete-btn text-danger" type="button"
                                                    data-bs-toggle="modal" data-bs-target="#deleteLanguageModal"
                                                    data-id="{{ $language->id }}" data-name="{{ $language->name }}">
                                                    <i class="bi bi-trash-fill me-2"></i> Eliminar
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-translate text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                    <h5 class="text-muted mb-3">No hay idiomas registrados</h5>
                                    <p class="text-muted mb-4">Añade los idiomas que dominas para mejorar tu perfil.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#createLanguageModal">
                                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir Primer Idioma
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- No Results Message (Hidden by default) --}}
                <div id="noResults" class="card shadow-sm border-0 bg-light mt-3" style="display: none;">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                        <h5 class="text-muted mb-2">No se encontraron resultados</h5>
                        <p class="text-muted">Intenta con otros términos de búsqueda o filtros</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- MODALES: Creación, Edición y Eliminación --}}
    {{-- ================================================= --}}

    {{-- 1. MODAL DE CREACIÓN --}}
    <div class="modal fade @error('name') show @enderror" id="createLanguageModal" tabindex="-1"
        aria-labelledby="createLanguageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createLanguageModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Añadir Nuevo Idioma
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('worker.idiomas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-bold">Nombre del Idioma</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                id="create_name" name="name" value="{{ old('name') }}" required
                                placeholder="Ej: Español, Inglés, Francés">
                            @error('name')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_level" class="form-label fw-bold">Nivel de Dominio</label>
                            <select class="form-select form-select-lg @error('level') is-invalid @enderror"
                                id="create_level" name="level">
                                <option value="">Seleccionar nivel...</option>
                                <option value="Básico" {{ old('level') == 'Básico' ? 'selected' : '' }}>Básico</option>
                                <option value="Intermedio" {{ old('level') == 'Intermedio' ? 'selected' : '' }}>Intermedio
                                </option>
                                <option value="Avanzado" {{ old('level') == 'Avanzado' ? 'selected' : '' }}>Avanzado
                                </option>
                                <option value="Nativo" {{ old('level') == 'Nativo' ? 'selected' : '' }}>Nativo</option>
                            </select>
                            @error('level')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Idioma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. MODAL DE EDICIÓN --}}
    <div class="modal fade" id="editLanguageModal" tabindex="-1" aria-labelledby="editLanguageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editLanguageModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Idioma
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editLanguageForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-bold">Nombre del Idioma</label>
                            <input type="text" class="form-control form-control-lg" id="edit_name" name="name"
                                required placeholder="Nombre del Idioma">
                        </div>

                        <div class="mb-3">
                            <label for="edit_level" class="form-label fw-bold">Nivel de Dominio</label>
                            <select class="form-select form-select-lg" id="edit_level" name="level">
                                <option value="">Seleccionar nivel...</option>
                                <option value="Básico">Básico</option>
                                <option value="Intermedio">Intermedio</option>
                                <option value="Avanzado">Avanzado</option>
                                <option value="Nativo">Nativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. MODAL DE ELIMINACIÓN --}}
    <div class="modal fade" id="deleteLanguageModal" tabindex="-1" aria-labelledby="deleteLanguageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteLanguageModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="mb-1">¿Estás seguro de que quieres eliminar el idioma?</p>
                    <h5 id="deleteLanguageName" class="fw-bold text-danger mb-3"></h5>
                    <p class="small text-muted mb-0">Esta acción lo desasocia de tu perfil.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteLanguageForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- LÓGICA JAVASCRIPT --}}
    {{-- ================================================= --}}
    @section('scripts')
        <script>
            $(document).ready(function() {
                // URL base de la ruta de idiomas
                const baseUrl = "{{ url('/') }}/candidatos/idiomas";

                // ------------------------------------------
                // LÓGICA DE BÚSQUEDA Y FILTRADO
                // ------------------------------------------
                const searchInput = document.getElementById('searchLanguage');
                const filterLevel = document.getElementById('filterLevel');
                const btnClear = document.getElementById('btnClear');
                const languageItems = document.querySelectorAll('.language-item');
                const noResults = document.getElementById('noResults');

                function filterLanguages() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const levelFilter = filterLevel.value;
                    let visibleCount = 0;

                    languageItems.forEach(item => {
                        const name = item.dataset.name;
                        const level = item.dataset.level;

                        const matchesSearch = name.includes(searchTerm);
                        const matchesLevel = !levelFilter || level === levelFilter;

                        if (matchesSearch && matchesLevel) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && languageItems.length > 0) {
                        noResults.style.display = '';
                    } else {
                        noResults.style.display = 'none';
                    }
                }

                function clearFilters() {
                    searchInput.value = '';
                    filterLevel.value = '';
                    filterLanguages();
                    searchInput.focus();
                }

                if (searchInput) {
                    searchInput.addEventListener('keyup', filterLanguages);
                    filterLevel.addEventListener('change', filterLanguages);
                    btnClear.addEventListener('click', clearFilters);
                }

                // ------------------------------------------
                // Lógica para el MODAL DE EDICIÓN
                // ------------------------------------------
                $('.edit-btn').on('click', function() {
                    const languageId = $(this).data('id');
                    const languageName = $(this).data('name');
                    const languageLevel = $(this).data('level');
                    const updateUrl = baseUrl + '/' + languageId;

                    $('#editLanguageForm').attr('action', updateUrl);
                    $('#edit_name').val(languageName);
                    $('#edit_level').val(languageLevel);
                    $('#editLanguageModalLabel').html(
                        '<i class="bi bi-pencil-square me-2"></i>Editar Idioma: ' +
                        languageName);
                });

                // ------------------------------------------
                // AJAX para actualizar idioma
                // ------------------------------------------
                $('#editLanguageForm').on('submit', function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const url = form.attr('action');
                    const formData = form.serialize();

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            // Cerrar el modal
                            $('#editLanguageModal').modal('hide');

                            // Mostrar mensaje de éxito
                            const alertHtml = `
                                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>${response.success}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                            $('.container.py-4 .row .col-lg-11').prepend(alertHtml);

                            // Recargar la página para mostrar los cambios
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            let errorMessage = 'Error al actualizar el idioma.';

                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMessage = Object.values(errors).flat().join('<br>');
                            }

                            // Mostrar error en el modal
                            const errorHtml = `
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>${errorMessage}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;

                            // Insertar el error al inicio del modal-body
                            $('#editLanguageModal .modal-body').prepend(errorHtml);

                            // Remover el error después de 5 segundos
                            setTimeout(function() {
                                $('#editLanguageModal .alert').fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    });
                });

                // ------------------------------------------
                // Lógica para el MODAL DE ELIMINACIÓN
                // ------------------------------------------
                $('.delete-btn').on('click', function() {
                    const languageId = $(this).data('id');
                    const deleteUrl = baseUrl + '/' + languageId;
                    const languageName = $(this).data('name');

                    $('#deleteLanguageForm').attr('action', deleteUrl);
                    $('#deleteLanguageName').text(languageName);
                });

                // ------------------------------------------
                // Manejo de Errores de Validación
                // ------------------------------------------
                @if ($errors->any() && old('name') && !isset($language))
                    const modal = new bootstrap.Modal(document.getElementById('createLanguageModal'));
                    modal.show();
                @endif
            });
        </script>
    @endsection
@endsection
