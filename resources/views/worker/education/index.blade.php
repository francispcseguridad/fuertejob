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
                            <i class="bi bi-mortarboard-fill text-primary me-2"></i>
                            Gestión de Educación
                        </h1>
                        <p class="text-muted mb-0">Administra tu formación académica y certificaciones</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createEducationModal">
                        <i class="bi bi-plus-circle-fill me-2"></i> Añadir Educación
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
                        <li class="breadcrumb-item active" aria-current="page">Educación</li>
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
                                    <input type="text" id="searchEducation" class="form-control"
                                        placeholder="Buscar por institución, título, campo de estudio...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="filterStatus" class="form-select">
                                    <option value="">Todos los registros</option>
                                    <option value="current">En curso</option>
                                    <option value="completed">Completados</option>
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

                {{-- Education Records --}}
                <div id="educationList">
                    @forelse ($educationRecords as $education)
                        <div class="card shadow-sm mb-3 education-item hover-shadow transition-all"
                            data-institution="{{ strtolower($education->institution) }}"
                            data-degree="{{ strtolower($education->degree) }}"
                            data-field="{{ strtolower($education->field_of_study ?? '') }}"
                            data-status="{{ $education->is_current ? 'current' : 'completed' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                                <i class="bi bi-mortarboard-fill text-primary fs-5"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-bold">{{ $education->degree }}</h5>
                                                <h6 class="text-primary mb-2">
                                                    <i class="bi bi-building me-1"></i>{{ $education->institution }}
                                                </h6>

                                                @if ($education->field_of_study)
                                                    <span class="badge bg-info bg-opacity-10 text-info mb-2">
                                                        <i class="bi bi-book me-1"></i>
                                                        {{ $education->field_of_study }}
                                                    </span>
                                                @endif

                                                <p class="text-muted mb-2">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ $education->start_date }} -
                                                    @if ($education->is_current)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-clock me-1"></i>En curso
                                                        </span>
                                                    @else
                                                        {{ $education->end_date }}
                                                    @endif
                                                </p>

                                                @if ($education->description)
                                                    <p class="text-secondary mb-0">
                                                        {{ Str::limit($education->description, 200) }}
                                                    </p>
                                                @endif
                                            </div>
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
                                                    data-bs-target="#editEducationModal" data-id="{{ $education->id }}"
                                                    data-institution="{{ $education->institution }}"
                                                    data-degree="{{ $education->degree }}"
                                                    data-field="{{ $education->field_of_study }}"
                                                    data-start-date="{{ $education->start_date }}"
                                                    data-end-date="{{ $education->end_date }}"
                                                    data-is-current="{{ $education->is_current ? '1' : '0' }}"
                                                    data-description="{{ $education->description }}">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i> Editar
                                                </button>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button class="dropdown-item delete-btn text-danger" type="button"
                                                    data-bs-toggle="modal" data-bs-target="#deleteEducationModal"
                                                    data-id="{{ $education->id }}"
                                                    data-degree="{{ $education->degree }}">
                                                    <i class="bi bi-trash-fill me-2"></i> Eliminar
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card shadow-sm border-0 bg-light">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-mortarboard text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                <h5 class="text-muted mb-3">No hay registros de educación</h5>
                                <p class="text-muted mb-4">Aún no has registrado ningún estudio o formación.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#createEducationModal">
                                    <i class="bi bi-plus-circle-fill me-2"></i> Añadir tu primera educación
                                </button>
                            </div>
                        </div>
                    @endforelse

                    {{-- No Results Message (Hidden by default) --}}
                    <div id="noResults" class="card shadow-sm border-0 bg-light" style="display: none;">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-search text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                            <h5 class="text-muted mb-2">No se encontraron resultados</h5>
                            <p class="text-muted">Intenta con otros términos de búsqueda o filtros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- MODALES: Creación, Edición y Eliminación --}}
    {{-- ================================================= --}}

    {{-- 1. MODAL DE CREACIÓN --}}
    <div class="modal fade @error('institution') show @enderror @error('degree') show @enderror" id="createEducationModal"
        tabindex="-1" aria-labelledby="createEducationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createEducationModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Añadir Nueva Educación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="createEducationForm" action="{{ route('worker.educacion.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="create_institution" class="form-label fw-bold">Institución *</label>
                                <input type="text" class="form-control @error('institution') is-invalid @enderror"
                                    id="create_institution" name="institution" value="{{ old('institution') }}" required
                                    placeholder="Ej: Universidad Nacional">
                                @error('institution')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="create_degree" class="form-label fw-bold">Título/Grado *</label>
                                <input type="text" class="form-control @error('degree') is-invalid @enderror"
                                    id="create_degree" name="degree" value="{{ old('degree') }}" required
                                    placeholder="Ej: Licenciatura en Ingeniería">
                                @error('degree')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="create_field_of_study" class="form-label fw-bold">Campo de Estudio</label>
                                <input type="text" class="form-control @error('field_of_study') is-invalid @enderror"
                                    id="create_field_of_study" name="field_of_study" value="{{ old('field_of_study') }}"
                                    placeholder="Ej: Ingeniería de Software, Ciencias de la Computación">
                                @error('field_of_study')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="create_start_date" class="form-label fw-bold">Fecha de Inicio *</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                    id="create_start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="create_end_date" class="form-label fw-bold">Fecha de Fin</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                    id="create_end_date" name="end_date" value="{{ old('end_date') }}">
                                @error('end_date')
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
                                        Estoy cursando actualmente
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="create_description" class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="create_description" name="description"
                                    rows="4" placeholder="Describe tus logros, actividades destacadas, proyectos...">{{ old('description') }}</textarea>
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
                        <button type="submit" class="btn btn-primary px-4">Guardar Educación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. MODAL DE EDICIÓN --}}
    <div class="modal fade" id="editEducationModal" tabindex="-1" aria-labelledby="editEducationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editEducationModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Educación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editEducationForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_institution" class="form-label fw-bold">Institución *</label>
                                <input type="text" class="form-control" id="edit_institution" name="institution"
                                    required placeholder="Institución educativa">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_degree" class="form-label fw-bold">Título/Grado *</label>
                                <input type="text" class="form-control" id="edit_degree" name="degree" required
                                    placeholder="Título o grado obtenido">
                            </div>
                            <div class="col-12">
                                <label for="edit_field_of_study" class="form-label fw-bold">Campo de Estudio</label>
                                <input type="text" class="form-control" id="edit_field_of_study"
                                    name="field_of_study" placeholder="Campo de estudio">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_start_date" class="form-label fw-bold">Fecha de Inicio *</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_end_date" class="form-label fw-bold">Fecha de Fin</label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_is_current"
                                        name="is_current" value="1">
                                    <label class="form-check-label" for="edit_is_current">
                                        Estoy cursando actualmente
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="edit_description" class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="4"
                                    placeholder="Describe tus logros, actividades destacadas, proyectos..."></textarea>
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
    <div class="modal fade" id="deleteEducationModal" tabindex="-1" aria-labelledby="deleteEducationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteEducationModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="mb-1">¿Estás seguro de que quieres eliminar este registro de educación?</p>
                    <h5 id="deleteEducationName" class="fw-bold text-danger mb-3"></h5>
                    <p class="small text-muted mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteEducationForm" method="POST" style="display: inline;">
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
                // URL base de la ruta de educación
                const baseUrl = "{{ url('/') }}/candidatos/educacion";
                const csrfToken = "{{ csrf_token() }}";

                // ------------------------------------------
                // LÓGICA DE BÚSQUEDA Y FILTRADO
                // ------------------------------------------
                const searchInput = document.getElementById('searchEducation');
                const filterStatus = document.getElementById('filterStatus');
                const btnClear = document.getElementById('btnClear');
                const educationItems = document.querySelectorAll('.education-item');
                const noResults = document.getElementById('noResults');

                function filterEducation() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const statusFilter = filterStatus.value;
                    let visibleCount = 0;

                    educationItems.forEach(item => {
                        const institution = item.dataset.institution;
                        const degree = item.dataset.degree;
                        const field = item.dataset.field;
                        const status = item.dataset.status;

                        const matchesSearch = institution.includes(searchTerm) ||
                            degree.includes(searchTerm) ||
                            field.includes(searchTerm);

                        const matchesStatus = !statusFilter || status === statusFilter;

                        if (matchesSearch && matchesStatus) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount === 0 && educationItems.length > 0) {
                        noResults.style.display = '';
                    } else {
                        noResults.style.display = 'none';
                    }
                }

                function clearFilters() {
                    searchInput.value = '';
                    filterStatus.value = '';
                    filterEducation();
                    searchInput.focus();
                }

                if (searchInput) {
                    searchInput.addEventListener('keyup', filterEducation);
                    filterStatus.addEventListener('change', filterEducation);
                    btnClear.addEventListener('click', clearFilters);
                }

                // ------------------------------------------
                // Manejo del checkbox "Estoy cursando actualmente"
                // ------------------------------------------
                $('#create_is_current').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#create_end_date').val('').prop('disabled', true);
                    } else {
                        $('#create_end_date').prop('disabled', false);
                    }
                });

                $('#edit_is_current').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#edit_end_date').val('').prop('disabled', true);
                    } else {
                        $('#edit_end_date').prop('disabled', false);
                    }
                });

                // ------------------------------------------
                // AJAX: Crear educación
                // ------------------------------------------
                $('#createEducationForm').on('submit', function(e) {
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
                                'createEducationModal')).hide();

                            // Limpiar formulario
                            form[0].reset();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Educación añadida con éxito');

                            // Recargar página para mostrar la nueva educación
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al guardar la educación';

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
                    const educationId = $(this).data('id');
                    const institution = $(this).data('institution');
                    const degree = $(this).data('degree');
                    const field = $(this).data('field');
                    const startDate = $(this).data('start-date');
                    const endDate = $(this).data('end-date');
                    const isCurrent = $(this).data('is-current');
                    const description = $(this).data('description');
                    const updateUrl = baseUrl + '/' + educationId;

                    $('#editEducationForm').attr('action', updateUrl);
                    $('#edit_institution').val(institution);
                    $('#edit_degree').val(degree);
                    $('#edit_field_of_study').val(field);
                    $('#edit_start_date').val(startDate);
                    $('#edit_end_date').val(endDate);
                    $('#edit_description').val(description);

                    if (isCurrent == '1') {
                        $('#edit_is_current').prop('checked', true);
                        $('#edit_end_date').prop('disabled', true);
                    } else {
                        $('#edit_is_current').prop('checked', false);
                        $('#edit_end_date').prop('disabled', false);
                    }

                    $('#editEducationModalLabel').html(
                        '<i class="bi bi-pencil-square me-2"></i>Editar: ' + degree);
                });

                // ------------------------------------------
                // AJAX: Editar educación
                // ------------------------------------------
                $('#editEducationForm').on('submit', function(e) {
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
                                    'editEducationModal'))
                                .hide();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Educación actualizada con éxito');

                            // Recargar página para mostrar los cambios
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al actualizar la educación';

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
                    const educationId = $(this).data('id');
                    const deleteUrl = baseUrl + '/' + educationId;
                    const degree = $(this).data('degree');

                    $('#deleteEducationForm').attr('action', deleteUrl);
                    $('#deleteEducationName').text(degree);
                });

                // ------------------------------------------
                // AJAX: Eliminar educación
                // ------------------------------------------
                $('#deleteEducationForm').on('submit', function(e) {
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
                                'deleteEducationModal')).hide();

                            // Mostrar mensaje de éxito
                            showAlert('success', response.success ||
                                'Educación eliminada con éxito');

                            // Recargar página para actualizar la lista
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Error al eliminar la educación';

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
                @if ($errors->any() && (old('institution') || old('degree')))
                    const modal = new bootstrap.Modal(document.getElementById('createEducationModal'));
                    modal.show();
                @endif
            });
        </script>
    @endsection
@endsection
