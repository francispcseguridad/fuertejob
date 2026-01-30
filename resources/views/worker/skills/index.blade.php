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
                            Gestión de herramientas
                        </h1>
                        <p class="text-muted mb-0">Demuestra el manejo de herramientas necesarias para el trabajo</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createskillModal">
                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir
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
                        <li class="breadcrumb-item active" aria-current="page">herramientas</li>
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
                                    <input type="text" id="searchskill" class="form-control"
                                        placeholder="Buscar herramienta...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100" type="button" id="btnClear">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- skills Grid --}}
                <div class="row g-3" id="skillsList">
                    @forelse ($skills as $skill)
                        <div class="col-md-6 col-lg-4 skill-item" data-name="{{ strtolower($skill->name) }}">
                            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all"
                                style="overflow: visible;">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="bi bi-translate text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-bold text-truncate" style="max-width: 150px;"
                                                title="{{ ucfirst($skill->name) }}">
                                                {{ ucfirst($skill->name) }}
                                            </h5>
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
                                                    data-bs-target="#editskillModal" data-id="{{ $skill->id }}"
                                                    data-name="{{ $skill->name }}">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i> Editar
                                                </button>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button class="dropdown-item delete-btn text-danger" type="button"
                                                    data-bs-toggle="modal" data-bs-target="#deleteskillModal"
                                                    data-id="{{ $skill->id }}" data-name="{{ $skill->name }}">
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
                                    <h5 class="text-muted mb-3">No hay herramientas registrados</h5>
                                    <p class="text-muted mb-4">Añade los herramientas que dominas para mejorar tu perfil.
                                    </p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#createskillModal">
                                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir Primer herramienta
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
    <div class="modal fade @error('name') show @enderror" id="createskillModal" tabindex="-1"
        aria-labelledby="createskillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createskillModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Añadir nueva herramienta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('worker.habilidades.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="create_name" class="form-label fw-bold">Nombre del herramienta</label>
                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                id="create_name" name="name" value="{{ old('name') }}" required
                                placeholder="Ej: Office, Photoshop o herramientas de trabajo físico">
                            @error('name')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar herramienta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. MODAL DE EDICIÓN --}}
    <div class="modal fade" id="editskillModal" tabindex="-1" aria-labelledby="editskillModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editskillModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar herramienta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editskillForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-bold">Nombre del herramienta</label>
                            <input type="text" class="form-control form-control-lg" id="edit_name" name="name"
                                required placeholder="Nombre del herramienta">
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
    <div class="modal fade" id="deleteskillModal" tabindex="-1" aria-labelledby="deleteskillModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteskillModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="mb-1">¿Estás seguro de que quieres eliminar el herramienta?</p>
                    <h5 id="deleteskillName" class="fw-bold text-danger mb-3"></h5>
                    <p class="small text-muted mb-0">Esta acción lo desasocia de tu perfil.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteskillForm" method="POST" style="display: inline;">
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
                // URL base de la ruta de habilidades
                const baseUrl = "{{ url('/') }}/candidatos/habilidades";
                const csrfToken = "{{ csrf_token() }}";

                // ------------------------------------------
                // LÓGICA DE BÚSQUEDA Y FILTRADO
                // ------------------------------------------
                const searchInput = document.getElementById('searchskill');
                const btnClear = document.getElementById('btnClear');
                const skillItems = document.querySelectorAll('.skill-item');
                const noResults = document.getElementById('noResults');

                function filterskills() {
                    const searchTerm = searchInput.value.toLowerCase();
                    let visibleCount = 0;

                    skillItems.forEach(item => {
                        const name = item.dataset.name;

                        const matchesSearch = name.includes(searchTerm);

                        if (matchesSearch) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && skillItems.length > 0) {
                        noResults.style.display = '';
                    } else {
                        noResults.style.display = 'none';
                    }
                }

                function clearFilters() {
                    searchInput.value = '';
                    filterskills();
                    searchInput.focus();
                }

                if (searchInput) {
                    searchInput.addEventListener('keyup', filterskills);
                    btnClear.addEventListener('click', clearFilters);
                }

                // ------------------------------------------
                // AJAX: Crear herramienta
                // ------------------------------------------
                $('#createskillModal form').on('submit', function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const submitBtn = form.find('button[type="submit"]');
                    const originalText = submitBtn.html();

                    submitBtn.prop('disabled', true).html(
                        '<i class="bi bi-hourglass-split me-2"></i>Guardando...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            // Cerrar modal
                            bootstrap.Modal.getInstance(document.getElementById('createskillModal'))
                                .hide();

                            // Limpiar formulario
                            form[0].reset();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Herramienta añadida con éxito');

                            // Recargar página para mostrar la nueva herramienta
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al guardar la herramienta';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                } else if (xhr.responseJSON.errors) {
                                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(
                                        ', ');
                                }
                            }

                            showAlert('danger', errorMsg);
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                });

                // ------------------------------------------
                // Lógica para el MODAL DE EDICIÓN
                // ------------------------------------------
                $('.edit-btn').on('click', function() {
                    const skillId = $(this).data('id');
                    const skillName = $(this).data('name');
                    const updateUrl = baseUrl + '/' + skillId;

                    $('#editskillForm').attr('action', updateUrl);
                    $('#edit_name').val(skillName);
                    $('#editskillModalLabel').html(
                        '<i class="bi bi-pencil-square me-2"></i>Editar herramienta: ' +
                        skillName);
                });

                // ------------------------------------------
                // AJAX: Editar herramienta
                // ------------------------------------------
                $('#editskillForm').on('submit', function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const submitBtn = form.find('button[type="submit"]');
                    const originalText = submitBtn.html();

                    submitBtn.prop('disabled', true).html(
                        '<i class="bi bi-hourglass-split me-2"></i>Guardando...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            // Cerrar modal
                            bootstrap.Modal.getInstance(document.getElementById('editskillModal'))
                                .hide();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Herramienta actualizada con éxito');

                            // Recargar página para mostrar los cambios
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al actualizar la herramienta';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                } else if (xhr.responseJSON.errors) {
                                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(
                                        ', ');
                                }
                            }

                            showAlert('danger', errorMsg);
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                });

                // ------------------------------------------
                // Lógica para el MODAL DE ELIMINACIÓN
                // ------------------------------------------
                $('.delete-btn').on('click', function() {
                    const skillId = $(this).data('id');
                    const deleteUrl = baseUrl + '/' + skillId;
                    const skillName = $(this).data('name');

                    $('#deleteskillForm').attr('action', deleteUrl);
                    $('#deleteskillName').text(skillName);
                });

                // ------------------------------------------
                // AJAX: Eliminar herramienta
                // ------------------------------------------
                $('#deleteskillForm').on('submit', function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const submitBtn = form.find('button[type="submit"]');
                    const originalText = submitBtn.html();

                    submitBtn.prop('disabled', true).html(
                        '<i class="bi bi-hourglass-split me-2"></i>Eliminando...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            // Cerrar modal
                            bootstrap.Modal.getInstance(document.getElementById('deleteskillModal'))
                                .hide();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Herramienta eliminada con éxito');

                            // Recargar página para actualizar la lista
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al eliminar la herramienta';

                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            }

                            showAlert('danger', errorMsg);
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    });
                });

                // ------------------------------------------
                // Función auxiliar para mostrar alertas
                // ------------------------------------------
                function showAlert(type, message) {
                    const alertHtml = `
                        <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                    // Insertar alerta después del breadcrumb
                    $('nav[aria-label="breadcrumb"]').after(alertHtml);

                    // Auto-cerrar después de 5 segundos
                    setTimeout(() => {
                        $('.alert').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }

                // ------------------------------------------
                // Manejo de Errores de Validación
                // ------------------------------------------
                @if ($errors->any() && old('name') && !isset($skill))
                    const modal = new bootstrap.Modal(document.getElementById('createskillModal'));
                    modal.show();
                @endif
            });
        </script>
    @endsection
@endsection
