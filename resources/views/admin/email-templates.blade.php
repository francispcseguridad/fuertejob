@extends('layouts.app')
@section('title', 'Plantillas de Email')

@section('content')
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1">Gestión de Plantillas</h1>
                <p class="text-muted mb-0">Administra los correos automáticos que se envían desde la plataforma.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-lg rounded-pill shadow-sm" onclick="openCreateModal()">
                    <i class="bi bi-plus-circle me-2"></i> Nueva Plantilla
                </button>
            </div>
        </div>

        {{-- Mensajes de sesión --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error de validación</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Card listado -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-primary"><i class="bi bi-envelope-paper me-2"></i>Plantillas existentes
                    </h5>
                    <small class="text-muted">Actualiza asuntos, cuerpos y claves de sistema.</small>
                </div>
                <span class="badge bg-light text-muted border">Total: {{ $templates->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th scope="col">Nombre interno</th>
                                <th scope="col">Clave</th>
                                <th scope="col">Asunto</th>
                                <th scope="col" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $template)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $template->name }}</td>
                                    <td>
                                        <span
                                            class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $template->type }}</span>
                                    </td>
                                    <td class="text-muted">{{ Str::limit($template->subject, 80) }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="openEditModal(this)"
                                                data-update-url="{{ route('admin.email-templates.update', $template) }}"
                                                data-name="{{ e($template->name) }}" data-type="{{ e($template->type) }}"
                                                data-subject="{{ e($template->subject) }}"
                                                data-body="{{ e($template->body) }}">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </button>
                                            <form action="{{ route('admin.email-templates.destroy', $template) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar la plantilla {{ $template->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox me-2"></i>No hay plantillas registradas todavía.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div class="modal fade" id="emailTemplateModal" tabindex="-1" aria-labelledby="emailTemplateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTitle">Crear Plantilla</h5>
                        <small class="text-muted" id="modalSubtitle">Define los datos principales del correo.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="templateForm" method="POST">
                    @csrf
                    <input type="hidden" id="methodField" name="_method">
                    <div class="modal-body pt-0">
                        <div class="mb-3">
                            <label for="templateName" class="form-label fw-semibold">Nombre interno</label>
                            <input type="text" class="form-control" id="templateName" name="name" required>
                            <small class="text-muted">Solo visible para administradores.</small>
                        </div>
                        <div class="mb-3">
                            <label for="templateType" class="form-label fw-semibold">Clave única</label>
                            <input type="text" class="form-control" id="templateType" name="type" required>
                            <small class="text-muted">Ej: user_welcome, password_reset. No se puede repetir.</small>
                        </div>
                        <div class="mb-3">
                            <label for="templateSubject" class="form-label fw-semibold">Asunto</label>
                            <input type="text" class="form-control" id="templateSubject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="templateBodyEditor" class="form-label fw-semibold">Cuerpo del correo</label>
                            <div id="templateBodyEditor" class="form-control" contenteditable="true"
                                style="min-height: 240px; overflow-y: auto;"></div>
                            <textarea id="templateBody" name="body" class="d-none"></textarea>
                            <div id="templateBodyFeedback" class="invalid-feedback d-block text-danger small d-none">
                                Ingresa el contenido del correo.
                            </div>
                            <small class="text-muted">Puedes usar HTML o sintaxis Blade básica; el contenido se renderiza
                                tal como llegará al correo.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Plantilla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        const createTemplateUrl = "{{ route('admin.email-templates.store') }}";
        const modalElement = document.getElementById('emailTemplateModal');
        const templateForm = document.getElementById('templateForm');
        const methodField = document.getElementById('methodField');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const nameInput = document.getElementById('templateName');
        const typeInput = document.getElementById('templateType');
        const subjectInput = document.getElementById('templateSubject');
        const bodyInput = document.getElementById('templateBody');
        const bodyEditor = document.getElementById('templateBodyEditor');
        const bodyFeedback = document.getElementById('templateBodyFeedback');
        const modalInstance = new bootstrap.Modal(modalElement);

        function resetMethodField() {
            methodField.value = '';
            methodField.disabled = true;
        }

        function openCreateModal() {
            templateForm.reset();
            templateForm.action = createTemplateUrl;
            modalTitle.textContent = 'Crear Plantilla de Email';
            modalSubtitle.textContent = 'Configura el asunto, cuerpo y claves del nuevo mensaje.';
            typeInput.readOnly = false;
            bodyEditor.innerHTML = '';
            bodyEditor.classList.remove('is-invalid');
            bodyFeedback.classList.add('d-none');
            resetMethodField();
            modalInstance.show();
        }

        function decodeHtml(encodedString) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = encodedString;
            return textarea.value;
        }

        function openEditModal(button) {
            const data = button.dataset;
            templateForm.action = data.updateUrl;
            modalTitle.textContent = `Editar: ${data.name}`;
            modalSubtitle.textContent = 'Actualiza el contenido enviado a los usuarios.';

            nameInput.value = data.name || '';
            typeInput.value = data.type || '';
            typeInput.readOnly = true;
            subjectInput.value = data.subject || '';
            bodyEditor.innerHTML = data.body ? decodeHtml(data.body) : '';
            bodyEditor.classList.remove('is-invalid');
            bodyFeedback.classList.add('d-none');

            methodField.disabled = false;
            methodField.value = 'PUT';

            modalInstance.show();
        }

        templateForm.addEventListener('submit', (event) => {
            const htmlContent = bodyEditor.innerHTML.trim();
            const plainContent = bodyEditor.textContent.replace(/\u200B/g, '').trim();

            if (!plainContent.length) {
                event.preventDefault();
                bodyEditor.classList.add('is-invalid');
                bodyFeedback.classList.remove('d-none');
                bodyEditor.focus();
                return;
            }

            bodyEditor.classList.remove('is-invalid');
            bodyFeedback.classList.add('d-none');
            bodyInput.value = htmlContent;
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            templateForm.reset();
            typeInput.readOnly = false;
            bodyEditor.innerHTML = '';
            bodyEditor.classList.remove('is-invalid');
            bodyFeedback.classList.add('d-none');
            resetMethodField();
        });
    </script>
@endsection
