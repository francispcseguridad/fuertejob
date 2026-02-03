@extends('layouts.app')

@section('content')
    {{-- Inclusión de jQuery (Necesario para modales y manipulación de DOM) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }
    </style>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="bi bi-briefcase-fill text-primary me-2"></i>
                            Gestión de Experiencias Laborales
                        </h1>
                        <p class="text-muted mb-0">Administra tu historial profesional</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createExperienceModal">
                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir Experiencia
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
                        <li class="breadcrumb-item active" aria-current="page">Experiencias Laborales</li>
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
                                    <input type="text" id="searchExperience" class="form-control"
                                        placeholder="Buscar por puesto o empresa...">
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

                {{-- Experiences Grid --}}
                <div class="row g-3" id="experiencesList">
                    @forelse ($experiences as $experience)
                        <div class="col-12 experience-item"
                            data-search="{{ strtolower($experience->job_title . ' ' . $experience->company_name) }}">
                            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                                    <i class="bi bi-briefcase-fill text-primary fs-5"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1 fw-bold">{{ $experience->job_title }}</h5>
                                                    <h6 class="text-muted mb-2">
                                                        <i class="bi bi-building me-1"></i>{{ $experience->company_name }}
                                                    </h6>
                                                    <p class="text-muted mb-2">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        {{ $experience->start_year ?? '—' }} -
                                                        @if ($experience->is_current)
                                                            <span class="badge bg-success">Actualidad</span>
                                                        @else
                                                            {{ $experience->end_year ?? '—' }}
                                                        @endif
                                                    </p>
                                                    @if ($experience->description)
                                                        <p class="text-secondary mb-0">
                                                            {{ Str::limit($experience->description, 200) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-5"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <button class="dropdown-item edit-btn" type="button"
                                                        data-bs-toggle="modal" data-bs-target="#editExperienceModal"
                                                        data-id="{{ $experience->id }}"
                                                        data-job-title="{{ $experience->job_title }}"
                                                        data-company-name="{{ $experience->company_name }}"
                                                        data-start-year="{{ $experience->start_year }}"
                                                        data-end-year="{{ $experience->end_year }}"
                                                        data-is-current="{{ $experience->is_current ? '1' : '0' }}"
                                                        data-description="{{ $experience->description }}">
                                                        <i class="bi bi-pencil-square text-warning me-2"></i> Editar
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item delete-btn text-danger" type="button"
                                                        data-bs-toggle="modal" data-bs-target="#deleteExperienceModal"
                                                        data-id="{{ $experience->id }}"
                                                        data-job-title="{{ $experience->job_title }}">
                                                        <i class="bi bi-trash-fill me-2"></i> Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-briefcase text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                    <h5 class="text-muted mb-3">No hay experiencias registradas</h5>
                                    <p class="text-muted mb-4">Añade tu historial laboral para mejorar tu perfil.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#createExperienceModal">
                                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir Primera Experiencia
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
                        <p class="text-muted">Intenta con otros términos de búsqueda</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- MODALES: Creación, Edición y Eliminación --}}
    {{-- ================================================= --}}

    {{-- 1. MODAL DE CREACIÓN --}}
    <div class="modal fade @error('job_title') show @enderror @error('company_name') show @enderror"
        id="createExperienceModal" tabindex="-1" aria-labelledby="createExperienceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createExperienceModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Añadir Nueva Experiencia
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="createExperienceForm" action="{{ route('worker.experiencias.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_job_title" class="form-label fw-bold">Puesto de Trabajo *</label>
                                <input type="text" class="form-control @error('job_title') is-invalid @enderror"
                                    id="create_job_title" name="job_title" value="{{ old('job_title') }}" required
                                    placeholder="Ej: Desarrollador Full Stack">
                                @error('job_title')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="create_company_name" class="form-label fw-bold">Empresa *</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                    id="create_company_name" name="company_name" value="{{ old('company_name') }}"
                                    required placeholder="Ej: Tech Solutions S.A.">
                                @error('company_name')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="create_start_year" class="form-label fw-bold">Año de Inicio *</label>
                                <input type="number" min="1900" max="2100"
                                    class="form-control @error('start_year') is-invalid @enderror"
                                    id="create_start_year" name="start_year" value="{{ old('start_year') }}" required>
                                @error('start_year')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="create_end_year" class="form-label fw-bold">Año de Fin</label>
                                <input type="number" min="1900" max="2100"
                                    class="form-control @error('end_year') is-invalid @enderror"
                                    id="create_end_year" name="end_year" value="{{ old('end_year') }}">
                                @error('end_year')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="create_is_current"
                                        name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="create_is_current">
                                        Trabajo actualmente aquí
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="create_description" class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="create_description" name="description"
                                    rows="4" placeholder="Describe tus responsabilidades y logros...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Experiencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. MODAL DE EDICIÓN --}}
    <div class="modal fade" id="editExperienceModal" tabindex="-1" aria-labelledby="editExperienceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editExperienceModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Experiencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editExperienceForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_job_title" class="form-label fw-bold">Puesto de Trabajo *</label>
                                <input type="text" class="form-control" id="edit_job_title" name="job_title" required
                                    placeholder="Puesto de trabajo">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_company_name" class="form-label fw-bold">Empresa *</label>
                                <input type="text" class="form-control" id="edit_company_name" name="company_name"
                                    required placeholder="Nombre de la empresa">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_start_year" class="form-label fw-bold">Año de Inicio *</label>
                                <input type="number" min="1900" max="2100" class="form-control" id="edit_start_year"
                                    name="start_year" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_end_year" class="form-label fw-bold">Año de Fin</label>
                                <input type="number" min="1900" max="2100" class="form-control" id="edit_end_year"
                                    name="end_year">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_current"
                                        name="is_current" value="1">
                                    <label class="form-check-label" for="edit_is_current">
                                        Trabajo actualmente aquí
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="edit_description" class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="4"
                                    placeholder="Describe tus responsabilidades y logros..."></textarea>
                            </div>
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
    <div class="modal fade" id="deleteExperienceModal" tabindex="-1" aria-labelledby="deleteExperienceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteExperienceModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="mb-1">¿Estás seguro de que quieres eliminar esta experiencia?</p>
                    <h5 id="deleteExperienceName" class="fw-bold text-danger mb-3"></h5>
                    <p class="small text-muted mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteExperienceForm" method="POST" style="display: inline;">
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
                // URL base de la ruta de experiencias
                const baseUrl = "{{ url('/') }}/candidatos/experiencias";
                const csrfToken = "{{ csrf_token() }}";

                // ------------------------------------------
                // LÓGICA DE BÚSQUEDA Y FILTRADO
                // ------------------------------------------
                const searchInput = document.getElementById('searchExperience');
                const btnClear = document.getElementById('btnClear');
                const experienceItems = document.querySelectorAll('.experience-item');
                const noResults = document.getElementById('noResults');

                function filterExperiences() {
                    const searchTerm = searchInput.value.toLowerCase();
                    let visibleCount = 0;

                    experienceItems.forEach(item => {
                        const searchData = item.dataset.search;
                        const matchesSearch = searchData.includes(searchTerm);

                        if (matchesSearch) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && experienceItems.length > 0) {
                        noResults.style.display = '';
                    } else {
                        noResults.style.display = 'none';
                    }
                }

                function clearFilters() {
                    searchInput.value = '';
                    filterExperiences();
                    searchInput.focus();
                }

                if (searchInput) {
                    searchInput.addEventListener('keyup', filterExperiences);
                    btnClear.addEventListener('click', clearFilters);
                }

                // ------------------------------------------
                // Manejo del checkbox "Trabajo actualmente aquí"
                // ------------------------------------------
                $('#create_is_current').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#create_end_year').val('').prop('disabled', true);
                    } else {
                        $('#create_end_year').prop('disabled', false);
                    }
                });

                $('#edit_is_current').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#edit_end_year').val('').prop('disabled', true);
                    } else {
                        $('#edit_end_year').prop('disabled', false);
                    }
                });

                // ------------------------------------------
                // AJAX: Crear experiencia
                // ------------------------------------------
                $('#createExperienceForm').on('submit', function(e) {
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
                            bootstrap.Modal.getInstance(document.getElementById(
                                'createExperienceModal')).hide();

                            // Limpiar formulario
                            form[0].reset();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Experiencia añadida con éxito');

                            // Recargar página para mostrar la nueva experiencia
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al guardar la experiencia';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                } else if (xhr.responseJSON.errors) {
                                    errorMsg = Object.values(xhr.responseJSON.errors).flat()
                                        .join(', ');
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
                    const experienceId = $(this).data('id');
                    const jobTitle = $(this).data('job-title');
                    const companyName = $(this).data('company-name');
                    const startYear = $(this).data('start-year');
                    const endYear = $(this).data('end-year');
                    const isCurrent = $(this).data('is-current');
                    const description = $(this).data('description');
                    const updateUrl = baseUrl + '/' + experienceId;

                    $('#editExperienceForm').attr('action', updateUrl);
                    $('#edit_job_title').val(jobTitle);
                    $('#edit_company_name').val(companyName);
                    $('#edit_start_year').val(startYear);
                    $('#edit_end_year').val(endYear);
                    $('#edit_description').val(description);

                    if (isCurrent == '1') {
                        $('#edit_is_current').prop('checked', true);
                        $('#edit_end_year').val('').prop('disabled', true);
                    } else {
                        $('#edit_is_current').prop('checked', false);
                        $('#edit_end_year').prop('disabled', false);
                    }

                    $('#editExperienceModalLabel').html(
                        '<i class="bi bi-pencil-square me-2"></i>Editar: ' + jobTitle);
                });

                // ------------------------------------------
                // AJAX: Editar experiencia
                // ------------------------------------------
                $('#editExperienceForm').on('submit', function(e) {
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
                            bootstrap.Modal.getInstance(document.getElementById(
                                    'editExperienceModal'))
                                .hide();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Experiencia actualizada con éxito');

                            // Recargar página para mostrar los cambios
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al actualizar la experiencia';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                } else if (xhr.responseJSON.errors) {
                                    errorMsg = Object.values(xhr.responseJSON.errors).flat()
                                        .join(', ');
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
                    const experienceId = $(this).data('id');
                    const deleteUrl = baseUrl + '/' + experienceId;
                    const jobTitle = $(this).data('job-title');

                    $('#deleteExperienceForm').attr('action', deleteUrl);
                    $('#deleteExperienceName').text(jobTitle);
                });

                // ------------------------------------------
                // AJAX: Eliminar experiencia
                // ------------------------------------------
                $('#deleteExperienceForm').on('submit', function(e) {
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
                            bootstrap.Modal.getInstance(document.getElementById(
                                'deleteExperienceModal')).hide();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Experiencia eliminada con éxito');

                            // Recargar página para actualizar la lista
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al eliminar la experiencia';

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
                @if ($errors->any() && (old('job_title') || old('company_name')))
                    const modal = new bootstrap.Modal(document.getElementById('createExperienceModal'));
                    modal.show();
                @endif
            });
        </script>
    @endsection
@endsection
