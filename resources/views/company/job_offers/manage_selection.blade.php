@extends('layouts.app')

@section('title', 'Gestión de ' . $worker->user->name)

@section('content')
    <div class="container py-4">

        {{-- Header y Navegación --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-bold text-dark">
                    <i class="bi bi-gear-fill me-2 text-primary"></i>Gestión de Candidato
                </h1>
                <p class="text-muted lead mb-0">
                    <strong class="text-dark">{{ $worker->user->name }}</strong> para la oferta:
                    <strong class="text-primary">{{ $jobOffer->title }}</strong>
                </p>
                {{-- Enlace de regreso al listado --}}
                <a href="{{ route('empresa.candidatos.seleccionados.index', $jobOffer) }}"
                    class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la lista de seleccionados
                </a>
            </div>
            {{-- Indicador de última actualización --}}
            <div class="text-end">
                <span class="badge bg-light text-muted border p-2 rounded-pill shadow-sm">
                    Última actualización: <span id="updated-at" class="fw-semibold">
                        {{ \Carbon\Carbon::parse($selection->updated_at)->format('H:i:s') }}
                    </span>
                </span>
            </div>
        </div>

        {{-- Contenedor de Alertas (para mensajes AJAX) --}}
        <div id="ajax-alert-container" class="mb-4" style="position: sticky; top: 10px; z-index: 1050;">
        </div>


        <div class="row g-4">
            {{-- Columna Izquierda: Perfil y Datos Personales --}}
            <div class="col-lg-4">
                <div class="card shadow-sm h-100 border-0 rounded-4">
                    <div class="card-body p-4 text-center">
                        @php
                            $initials = '';
                            if ($worker->user->name ?? false) {
                                $parts = explode(' ', $worker->user->name);
                                $initials = strtoupper(
                                    substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''),
                                );
                            } else {
                                $initials = 'JP';
                            }
                        @endphp
                        {{-- Foto de Perfil --}}
                        <img src="{{ $worker->user->profile_picture ?? 'https://placehold.co/120x120/A0BFFF/FFFFFF?text=' . $initials }}"
                            alt="Foto de {{ $worker->user->name }}"
                            onerror="this.onerror=null;this.src='https://placehold.co/120x120/A0BFFF/FFFFFF?text={{ $initials }}';"
                            class="rounded-circle mb-3 border border-4 border-primary shadow-lg"
                            style="width: 120px; height: 120px; object-fit: cover;">

                        {{-- Nombre y Profesión --}}
                        <h4 class="card-title fw-bold text-dark mb-0">{{ $worker->user->name ?? 'Usuario Anónimo' }}</h4>
                        <p class="text-primary fw-semibold mb-3">{{ $worker->profession_title ?? 'Sin Título' }}</p>

                        {{-- Datos de la Selección no editables --}}
                        <div class="text-start mb-3 border-top pt-3">
                            <h6 class="fw-bold text-dark mb-2">Detalles del Registro</h6>
                            <div class="text-muted small mb-1">
                                <i class="bi bi-calendar-check me-2 text-primary"></i>
                                Fecha de Selección: <span class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($selection->selection_date)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="text-muted small mb-1">
                                <i class="bi bi-person-badge me-2 text-primary"></i>
                                Número: <span class="fw-semibold">{{ $selection->id }}</span>
                            </div>
                        </div>

                        <a href="{{ route('empresa.trabajadores.show', ['workerProfile' => $worker, 'jobOffer' => $jobOffer->id]) }}"
                            class="btn btn-outline-primary w-100 mt-3 rounded-pill shadow-sm btn-modern">
                            Ver Perfil Completo <i class="bi bi-box-arrow-up-right"></i>
                        </a>

                        {{-- Botón para Enviar Mensaje --}}
                        <button type="button" class="btn btn-primary w-100 mt-2 rounded-pill shadow-sm btn-modern"
                            data-bs-toggle="modal" data-bs-target="#messageModal">
                            <i class="bi bi-chat-text-fill me-2"></i> Enviar Mensaje
                        </button>
                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Formulario de Gestión del Proceso (candidate_selections) --}}
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4 mb-4">
                    <div class="card-body p-5">
                        <h3 class="fw-bold text-dark mb-4">
                            <i class="bi bi-list-task me-2 text-primary"></i>Datos Editables del Proceso
                        </h3>

                        <form id="selection-form-v2"
                            data-update-url="{{ route('empresa.candidatos.seleccionados.update', $selection) }}">
                            @csrf

                            {{-- Estado y Prioridad --}}
                            <div class="row g-3 mb-4">
                                {{-- 1. Estado Actual --}}
                                <div class="col-md-6">
                                    <label for="current_status" class="form-label fw-semibold text-secondary">
                                        <i class="bi bi-bar-chart-steps me-1"></i>Estado del Pipeline <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select id="current_status" name="current_status"
                                        class="form-select form-select-lg rounded-3">
                                        @foreach ($allStatuses as $status)
                                            <option value="{{ $status }}"
                                                {{ $selection->current_status === $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" data-field="current_status"></div>
                                </div>

                                {{-- 2. Prioridad --}}
                                <div class="col-md-6">
                                    <label for="priority" class="form-label fw-semibold text-secondary">
                                        <i class="bi bi-arrow-up-circle-fill me-1"></i>Prioridad (1-5, siendo 1 Máxima)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" id="priority" name="priority" min="1" max="5"
                                        required value="{{ $selection->priority }}"
                                        class="form-control form-control-lg rounded-3">
                                    <div class="invalid-feedback" data-field="priority"></div>
                                </div>
                            </div>

                            {{-- Salario y Tiempo Estimado --}}
                            <div class="row g-3 mb-4">
                                {{-- 3. Salario Esperado --}}
                                <div class="col-md-6">
                                    <label for="expected_salary" class="form-label fw-semibold text-secondary">
                                        <i class="bi bi-cash me-1"></i>Salario Esperado ($) <span
                                            class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text rounded-start-3">$</span>
                                        <input type="number" id="expected_salary" name="expected_salary" required
                                            min="0" value="{{ $selection->expected_salary }}"
                                            class="form-control rounded-end-3" placeholder="Ej: 35000">
                                    </div>
                                    <div class="invalid-feedback" data-field="expected_salary"></div>
                                </div>

                                {{-- 4. Tiempo para Contratación --}}
                                <div class="col-md-6">
                                    <label for="time_to_hire_days" class="form-label fw-semibold text-secondary">
                                        <i class="bi bi-clock me-1"></i>Tiempo Estimado (Días) <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="number" id="time_to_hire_days" name="time_to_hire_days" required
                                        min="0" value="{{ $selection->time_to_hire_days }}"
                                        class="form-control form-control-lg rounded-3" placeholder="Ej: 30">
                                    <div class="invalid-feedback" data-field="time_to_hire_days"></div>
                                </div>
                            </div>

                            {{-- Evaluación Inicial --}}



                            <div class="mb-4">
                                {{-- 5. Evaluación Inicial (Initial_assessment) --}}
                                <label for="initial_assessment" class="form-label fw-semibold text-secondary">
                                    <i class="bi bi-journal-text me-1"></i>Evaluación Inicial
                                </label>
                                <textarea id="initial_assessment" name="initial_assessment" rows="3" class="form-control rounded-3"
                                    placeholder="Resumen de la primera impresión o motivación de la selección">{{ $selection->initial_assessment }}</textarea>
                                <div class="invalid-feedback" data-field="initial_assessment"></div>
                            </div>


                        </form>

                        {{-- Botón de Guardar (Movido fuera del form para evitar submit doble) - RENAMED ID TO V2 --}}
                        <div class="mt-4">
                            <button type="button" id="save-selection-button-v2"
                                class="btn btn-success btn-lg rounded-pill px-5 shadow-lg">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                    aria-hidden="true" id="save-selection-spinner"></span>
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Segunda Tarjeta: Historial del Proceso de Selección (selection_process_log) --}}
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                            <h3 class="fw-bold text-dark mb-0">
                                <i class="bi bi-clock-history me-2 text-primary"></i>Historial del Proceso
                            </h3>
                            <button type="button" class="btn btn-primary rounded-pill btn-sm shadow-sm"
                                id="create-log-button" data-bs-toggle="modal" data-bs-target="#logModal"
                                data-mode="create">
                                <i class="bi bi-plus-lg me-1"></i> Añadir Etapa
                            </button>
                        </div>

                        {{-- Tabla de Logs --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="log-table">
                                <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Etapa</th>
                                        <th>Fecha</th>
                                        <th>Tipo Contacto</th>
                                        <th>Resultado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="log-table-body">
                                    {{-- Los logs se cargarán aquí vía AJAX --}}
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Cargando historial...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA CREAR/EDITAR LOGS --}}
    <div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold" id="logModalLabel">Registrar Etapa del Proceso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Formulario Log - RENAMED ID TO V2 --}}
                    <form id="log-form-v2">
                        @csrf
                        <input type="hidden" name="log_id" id="log_id">

                        <div class="row g-3">
                            {{-- Fila 1: Orden y Nombre de Etapa --}}
                            <div class="col-md-6">
                                <label for="stage_order" class="form-label fw-semibold">Orden de la Etapa <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="stage_order" id="stage_order" class="form-control" required
                                    min="1" placeholder="Ej: 1, 2, 3">
                                <div class="invalid-feedback" data-field="stage_order"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="stage_name" class="form-label fw-semibold">Nombre de la Etapa <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="stage_name" id="stage_name" class="form-control" required
                                    placeholder="Ej: Entrevista RRHH, Prueba Técnica">
                                <div class="invalid-feedback" data-field="stage_name"></div>
                            </div>

                            {{-- Fila 2: Fecha y Tipo de Contacto --}}
                            <div class="col-md-6">
                                <label for="stage_date" class="form-label fw-semibold">Fecha de la Etapa <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="stage_date" id="stage_date" class="form-control" required>
                                <div class="invalid-feedback" data-field="stage_date"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="contact_type" class="form-label fw-semibold">Tipo de Contacto <span
                                        class="text-danger">*</span></label>
                                <select name="contact_type" id="contact_type" class="form-select" required>
                                    <option value="">Selecciona...</option>
                                    <option value="Entrevista Presencial">Entrevista Presencial</option>
                                    <option value="Llamada">Llamada</option>
                                    <option value="Videoconferencia">Videoconferencia</option>
                                    <option value="Email">Email</option>
                                    <option value="Prueba Técnica">Prueba Técnica</option>
                                </select>
                                <div class="invalid-feedback" data-field="contact_type"></div>
                            </div>

                            {{-- Fila 3: Entrevistador y Resultado --}}
                            <div class="col-md-6">
                                <label for="interviewer_name" class="form-label fw-semibold">Nombre del
                                    Entrevistador</label>
                                <input type="text" name="interviewer_name" id="interviewer_name" class="form-control"
                                    placeholder="Ej: Juan Pérez">
                                <div class="invalid-feedback" data-field="interviewer_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="result" class="form-label fw-semibold">Resultado</label>
                                <select name="result" id="result" class="form-select">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Positivo">Positivo</option>
                                    <option value="Negativo">Negativo</option>
                                    <option value="Completado">Completado</option>
                                </select>
                                <div class="invalid-feedback" data-field="result"></div>
                            </div>

                            {{-- Fila 4: Notas y Siguiente Paso --}}
                            <div class="col-12">
                                <label for="interviewer_notes" class="form-label fw-semibold">Notas del
                                    Entrevistador</label>
                                <textarea name="interviewer_notes" id="interviewer_notes" rows="3" class="form-control"
                                    placeholder="Observaciones clave, fortalezas y debilidades."></textarea>
                                <div class="invalid-feedback" data-field="interviewer_notes"></div>
                            </div>
                            <div class="col-12">
                                <label for="next_step" class="form-label fw-semibold">Siguiente Paso</label>
                                <textarea name="next_step" id="next_step" rows="2" class="form-control"
                                    placeholder="Acciones a seguir después de esta etapa (Ej: Programar prueba técnica)."></textarea>
                                <div class="invalid-feedback" data-field="next_step"></div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                    {{-- Botón de Guardar Log - RENAMED ID TO V2 --}}
                    <button type="button" class="btn btn-primary rounded-pill" id="log-submit-button-v2">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"
                            id="log-spinner"></span>
                        <span id="log-submit-text">Guardar Registro</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    <div class="modal fade" id="deleteLogModal" tabindex="-1" aria-labelledby="deleteLogModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-danger text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold" id="deleteLogModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar permanentemente esta etapa del proceso?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill" id="confirm-delete-button"
                        data-log-id="">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"
                            id="delete-spinner"></span>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>


    @include('company.job_offers.manage_selection_message_modal')

    <style>
        /* Estilos generales */
        .btn-modern {
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
        }

        /* Estilos de validación */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .form-control.is-invalid+.invalid-feedback,
        .form-select.is-invalid+.invalid-feedback,
        .input-group>.form-control.is-invalid+.invalid-feedback {
            display: block;
        }

        /* Utilidad para logs */
        .stage-order {
            width: 50px;
            text-align: center;
            font-weight: bold;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =========================================================
            // CONFIGURACIÓN GLOBAL Y UTILIDADES
            // =========================================================
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const alertContainer = document.getElementById('ajax-alert-container');

            function showAlert(type, message) {
                const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
                alertContainer.innerHTML = alertHtml;
                alertContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                if (type === 'success') {
                    setTimeout(() => {
                        const alertElement = alertContainer.querySelector('.alert');
                        if (alertElement) {
                            new bootstrap.Alert(alertElement).close();
                        }
                    }, 5000);
                }
            }

            function clearValidationErrors(formElement) {
                formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            function displayValidationErrors(formElement, errors) {
                for (const fieldName in errors) {
                    if (errors.hasOwnProperty(fieldName)) {
                        const fieldElement = formElement.querySelector(`[name="${fieldName}"]`);
                        const feedbackElement = formElement.querySelector(
                            `.invalid-feedback[data-field="${fieldName}"]`);
                        if (fieldElement) fieldElement.classList.add('is-invalid');
                        if (feedbackElement) feedbackElement.textContent = errors[fieldName][0];
                    }
                }
            }

            // =========================================================
            // MÓDULO 1: GESTIÓN DEL FORMULARIO PRINCIPAL
            // =========================================================
            (function() {
                const form = document.getElementById('selection-form-v2');
                const btn = document.getElementById('save-selection-button-v2');

                if (!form || !btn) {
                    console.log('Módulo 1 (Principal) no inicializado: falta form o botón');
                    return;
                }

                const spinner = document.getElementById('save-selection-spinner');
                const updatedAtSpan = document.getElementById('updated-at');
                const updateUrl = form.dataset.updateUrl;

                // Variable de control para evitar doble ejecución
                let isProcessing = false;

                async function processMainSelectionForm(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation(); // CRÍTICO: detiene propagación inmediata

                    // Prevenir doble ejecución
                    if (isProcessing) {
                        console.log('Ya se está procesando el formulario principal, ignorando...');
                        return false;
                    }

                    console.log('🔵 Procesando FORMULARIO PRINCIPAL');
                    isProcessing = true;

                    // Bloquear UI
                    btn.disabled = true;
                    btn.textContent = 'Guardando...';
                    if (spinner) spinner.classList.remove('d-none');
                    clearValidationErrors(form);

                    try {
                        // Recolectar datos
                        const formData = {};
                        form.querySelectorAll('input, select, textarea').forEach(input => {
                            if (input.name) formData[input.name] = input.value;
                        });


                        // Petición
                        const response = await fetch(updateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw {
                                status: response.status,
                                data: data
                            };
                        }

                        if (data.success) {
                            showAlert('success', data.message);
                            if (updatedAtSpan && data.updatedAt) {
                                updatedAtSpan.textContent = data.updatedAt;
                            }
                        } else {
                            throw {
                                data: {
                                    error: data.error || 'Error desconocido'
                                }
                            };
                        }

                    } catch (errorObj) {
                        console.error('❌ Error en formulario principal:', errorObj);
                        const errorData = errorObj.data || {};

                        if (errorObj.status === 422 && errorData.messages) {
                            displayValidationErrors(form, errorData.messages);
                            showAlert('danger', 'Por favor, corrige los errores en el formulario.');
                        } else {
                            showAlert('danger', errorData.error ||
                                'Ocurrió un error al guardar la selección.');
                        }
                    } finally {
                        // Desbloquear UI
                        btn.disabled = false;
                        btn.textContent = 'Guardar Cambios';
                        if (spinner) spinner.classList.add('d-none');
                        isProcessing = false;
                    }

                    return false;
                }

                // IMPORTANTE: Solo UN listener por tipo
                btn.addEventListener('click', processMainSelectionForm, {
                    once: false
                });

                // Prevenir submit del form
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                });
            })();


            // =========================================================
            // MÓDULO 2: GESTIÓN DE LOGS (MODAL)
            // =========================================================
            (function() {
                const modalEl = document.getElementById('logModal');
                const form = document.getElementById('log-form-v2');
                const btn = document.getElementById('log-submit-button-v2');

                if (!modalEl || !form || !btn) {
                    console.log('Módulo 2 (Logs) no inicializado: falta form o botón');
                    return;
                }

                const spinner = document.getElementById('log-spinner');
                const tableBody = document.getElementById('log-table-body');
                const logBaseUrl = '{{ route('empresa.candidatos.log.index', [$selection]) }}';

                // Modal instance
                const modal = new bootstrap.Modal(modalEl);

                // Variable de control para evitar doble ejecución
                let isProcessing = false;

                // --- Funciones de Carga y Renderizado ---
                function createRowHtml(log) {
                    const resultClasses = {
                        'Positivo': 'bg-success',
                        'Negativo': 'bg-danger',
                        'Completado': 'bg-info',
                        'Pendiente': 'bg-secondary'
                    };
                    const badgesClass = resultClasses[log.result] || 'bg-secondary';
                    const logJson = JSON.stringify(log).replace(/"/g, '&quot;');

                    return `
                <tr data-log-id="${log.id}">
                    <td class="stage-order">${log.stage_order}</td>
                    <td>
                        <span class="fw-semibold">${log.stage_name}</span><br>
                        <small class="text-muted">${log.interviewer_name || 'N/A'}</small>
                    </td>
                    <td>${log.formatted_stage_date}</td>
                    <td>${log.contact_type}</td>
                    <td><span class="badge ${badgesClass} text-white">${log.result}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-info me-2 edit-log-btn" 
                                data-log="${logJson}" 
                                data-bs-toggle="modal" data-bs-target="#logModal" data-mode="edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-log-btn" 
                                data-id="${log.id}" 
                                data-bs-toggle="modal" data-bs-target="#deleteLogModal">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
           `;
                }

                function loadLogs() {
                    tableBody.innerHTML =
                        '<tr><td colspan="6" class="text-center text-primary"><span class="spinner-border spinner-border-sm me-2"></span> Cargando...</td></tr>';

                    fetch(logBaseUrl, {
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            tableBody.innerHTML = '';
                            if (data.success && data.logs.length > 0) {
                                data.logs.forEach(log => {
                                    tableBody.insertAdjacentHTML('beforeend', createRowHtml(log));
                                });
                            } else {
                                tableBody.innerHTML =
                                    '<tr><td colspan="6" class="text-center text-muted">No hay registros.</td></tr>';
                            }
                        })
                        .catch(() => {
                            tableBody.innerHTML =
                                '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos.</td></tr>';
                        });
                }

                loadLogs();

                // --- Lógica del Modal ---
                modalEl.addEventListener('show.bs.modal', function(event) {
                    const triggerBtn = event.relatedTarget;
                    const mode = triggerBtn.getAttribute('data-mode');

                    clearValidationErrors(form);
                    form.reset();
                    form.dataset.mode = mode;
                    document.getElementById('log_id').value = '';

                    const titleLabel = document.getElementById('logModalLabel');
                    const submitText = document.getElementById('log-submit-text');

                    if (mode === 'create') {
                        titleLabel.textContent = 'Añadir Nueva Etapa';
                        submitText.textContent = 'Guardar Registro';
                    } else {
                        titleLabel.textContent = 'Editar Etapa';
                        submitText.textContent = 'Actualizar Registro';

                        const rawLog = triggerBtn.getAttribute('data-log');
                        if (rawLog) {
                            const logData = JSON.parse(rawLog);
                            document.getElementById('log_id').value = logData.id;
                            document.getElementById('stage_order').value = logData.stage_order;
                            document.getElementById('stage_name').value = logData.stage_name;
                            document.getElementById('stage_date').value = logData.stage_date ? logData
                                .stage_date.split('T')[0] : '';
                            document.getElementById('contact_type').value = logData.contact_type;
                            document.getElementById('result').value = logData.result;
                            document.getElementById('interviewer_name').value = logData
                                .interviewer_name || '';
                            document.getElementById('interviewer_notes').value = logData
                                .interviewer_notes || '';
                            document.getElementById('next_step').value = logData.next_step || '';
                        }
                    }
                });

                // --- Lógica de Guardado ---
                async function processLogForm(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation(); // CRÍTICO

                    // Prevenir doble ejecución
                    if (isProcessing) {
                        return false;
                    }

                    isProcessing = true;

                    // Bloquear UI
                    btn.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');
                    clearValidationErrors(form);

                    try {
                        const mode = form.dataset.mode;
                        const logId = document.getElementById('log_id').value;

                        let url = logBaseUrl;
                        let method = 'POST';
                        if (mode === 'edit' && logId) {
                            url = `${logBaseUrl}/${logId}`;
                            method = 'POST';
                        }

                        console.log('📤 Enviando a:', url);

                        const formData = {};
                        form.querySelectorAll('input, select, textarea').forEach(input => {
                            if (input.name && input.name !== 'log_id') {
                                formData[input.name] = input.value;
                            }
                        });

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (!response.ok) throw {
                            status: response.status,
                            data: data
                        };

                        if (data.success) {
                            showAlert('success', data.message);
                            modal.hide();
                            loadLogs();
                        } else {
                            throw {
                                data: {
                                    error: data.error || 'Error del servidor'
                                }
                            };
                        }

                    } catch (errorObj) {
                        console.error('❌ Error en formulario de logs:', errorObj);
                        const errorData = errorObj.data || {};
                        if (errorObj.status === 422 && errorData.messages) {
                            displayValidationErrors(form, errorData.messages);
                            showAlert('danger', 'Corrige los errores del formulario.');
                        } else {
                            showAlert('danger', errorData.error || 'Error al guardar log.');
                        }
                    } finally {
                        btn.disabled = false;
                        if (spinner) spinner.classList.add('d-none');
                        isProcessing = false;
                    }

                    return false;
                }

                // IMPORTANTE: Solo UN listener
                btn.addEventListener('click', processLogForm, {
                    once: false
                });

                // Prevenir submit del form
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                });
            })();


            // =========================================================
            // MÓDULO 3: ELIMINACIÓN DE LOGS
            // =========================================================
            (function() {
                const modalEl = document.getElementById('deleteLogModal');
                const btn = document.getElementById('confirm-delete-button');

                if (!modalEl || !btn) return;

                const spinner = document.getElementById('delete-spinner');
                const modal = new bootstrap.Modal(modalEl);
                const logBaseUrl = '{{ route('empresa.candidatos.log.index', [$selection]) }}';

                let isProcessing = false;

                document.body.addEventListener('click', function(e) {
                    const deleteBtn = e.target.closest('.delete-log-btn');
                    if (deleteBtn) {
                        const logId = deleteBtn.getAttribute('data-id');
                        btn.setAttribute('data-log-id', logId);
                    }
                });

                async function processDelete(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (isProcessing) return;

                    const logId = btn.getAttribute('data-log-id');
                    if (!logId) return;

                    console.log('🔴 Procesando ELIMINACIÓN');
                    isProcessing = true;

                    btn.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');

                    try {
                        const response = await fetch(`${logBaseUrl}/${logId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            showAlert('success', data.message);
                            modal.hide();
                            location.reload();
                        } else {
                            showAlert('danger', data.error || 'Error al eliminar.');
                        }
                    } catch (e) {
                        showAlert('danger', 'Error de conexión.');
                    } finally {
                        btn.disabled = false;
                        btn.setAttribute('data-log-id', '');
                        if (spinner) spinner.classList.add('d-none');
                        isProcessing = false;
                    }
                }

                btn.addEventListener('click', processDelete);
            })();

        });
    </script>
@endsection
