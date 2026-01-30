@extends('layouts.app')

@section('title', 'Gestión de Secciones de Búsqueda')

@section('content')
    <div class="mb-4" style="padding: 20px;">
        {{-- Header Card --}}
        <div class="rounded-4 bg-primary bg-gradient text-white p-4 p-md-5 shadow-sm position-relative overflow-hidden mb-5">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="bi bi-search" style="font-size: 10rem;"></i>
            </div>
            <div class="row align-items-center position-relative z-1">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-search fs-4 text-white"></i>
                        </div>
                        <span class="text-uppercase small fw-bold text-white-50 tracking-wider">Configuración General</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-3">Secciones de Búsqueda</h2>
                    <p class="mb-0 text-white-75 fs-5 fw-light" style="max-width: 600px;">
                        Gestiona los títulos y subtítulos de las secciones de búsqueda en la portada.
                        Define qué mensaje ven tus usuarios al buscar ofertas.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold shadow-lg hover-scale px-4 py-3"
                        data-bs-toggle="modal" data-bs-target="#createSectionModal">
                        <i class="bi bi-plus-lg me-2 text-primary"></i>Nueva Sección
                    </button>
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        @if ($sections->count() > 0)
            <div class="row g-4">
                @foreach ($sections as $section)
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-lift group">
                            <div class="position-absolute top-0 end-0 p-3 z-2">
                                <div class="d-flex gap-2">
                                    <span
                                        class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm
                                {{ $section->is_active ? 'bg-success text-white' : 'bg-secondary text-white-50' }} backdrop-blur">
                                        {{ $section->is_active ? 'Visible' : 'Oculto' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="card-body p-4 pt-5 d-flex flex-column h-100">
                                <div class="d-flex align-items-center gap-2 mb-3 mt-2 text-muted small">
                                    <i class="bi bi-hash text-primary opacity-50"></i>
                                    <span class="text-uppercase tracking-wider fw-bold" style="font-size: 11px;">ID:
                                        {{ $section->id }}</span>
                                </div>

                                <h4 class="fw-bold text-dark mb-2 text-truncate" title="{{ $section->title }}">
                                    {{ $section->title }}
                                </h4>

                                <p class="text-muted mb-4 small flex-grow-1"
                                    style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">
                                    {{ $section->subtitle ?: 'Sin subtítulo definido' }}
                                </p>

                                <hr class="border-secondary border-opacity-10 my-0 mb-3">

                                {{-- Actions --}}
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium"
                                        data-view-trigger data-section='@json($section)'>
                                        <i class="bi bi-eye me-1"></i> Ver
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button
                                            class="btn btn-light text-primary btn-icon rounded-circle hover-primary shadow-sm"
                                            data-edit-trigger data-section='@json($section)'
                                            data-action="{{ route('admin.home_search_sections.update', $section->id) }}"
                                            data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <button
                                            class="btn btn-light text-danger btn-icon rounded-circle hover-danger shadow-sm"
                                            data-delete-trigger
                                            data-action="{{ route('admin.home_search_sections.destroy', $section->id) }}"
                                            data-title="{{ $section->title }}" data-bs-toggle="tooltip" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Decoration --}}
                            <div class="position-absolute bottom-0 start-0 w-100 h-1 bg-gradient-primary opacity-50"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5 rounded-4 border border-dashed border-2 bg-light bg-opacity-50">
                <div class="mb-4 text-muted opacity-25">
                    <i class="bi bi-search" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold text-muted mb-2">Sin secciones configuradas</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                    Aún no has creado ninguna sección de búsqueda. Crea una para definir el texto del buscador.
                </p>
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold hover-scale" data-bs-toggle="modal"
                    data-bs-target="#createSectionModal">
                    <i class="bi bi-plus-lg me-2"></i> Crear Primera Sección
                </button>
            </div>
        @endif
    </div>

    {{-- MODALS --}}

    {{-- Create Modal --}}
    <div class="modal fade" id="createSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Nuevo Contenido</p>
                        <h5 class="modal-title fw-bold text-dark">Crear Sección de Búsqueda</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.home_search_sections.store') }}" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        {{-- Main Info --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Título</label>
                                <input type="text"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    name="title" required placeholder="Ej: Busca tu empleo ideal">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Subtítulo</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    name="subtitle" placeholder="Ej: Miles de ofertas disponibles">
                            </div>
                        </div>

                        {{-- Options --}}
                        <h6 class="fw-bold text-muted mb-3 small text-uppercase border-bottom pb-2">Opciones</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div
                                    class="form-check form-switch p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3">
                                    <input class="form-check-input ms-0" type="checkbox" role="switch"
                                        id="create_active" name="is_active" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="create_active">Visible</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-0 pb-0 mt-3">
                        <button type="button"
                            class="btn btn-light rounded-pill px-4 fw-medium text-secondary hover-scale"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold hover-scale shadow-sm">
                            <i class="bi bi-save me-2"></i>Guardar Sección
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Edición</p>
                        <h5 class="modal-title fw-bold text-dark">Modificar Sección</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSectionForm" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    @method('PUT')
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        {{-- Main Info --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Título</label>
                                <input type="text"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_title" name="title" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Subtítulo</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_subtitle" name="subtitle">
                            </div>
                        </div>

                        {{-- Options --}}
                        <h6 class="fw-bold text-muted mb-3 small text-uppercase border-bottom pb-2">Opciones</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div
                                    class="form-check form-switch p-3 bg-white rounded-3 shadow-sm d-flex align-items-center gap-3">
                                    <input class="form-check-input ms-0" type="checkbox" role="switch"
                                        id="edit_is_active" name="is_active" value="1">
                                    <label class="form-check-label fw-semibold" for="edit_is_active">Visible</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-0 pb-0 mt-3">
                        <button type="button"
                            class="btn btn-light rounded-pill px-4 fw-medium text-secondary hover-scale"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold hover-scale shadow-sm">
                            <i class="bi bi-check-lg me-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Modal --}}
    <div class="modal fade" id="viewSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-0 overflow-hidden rounded-4">
                    {{-- Mock Preview --}}
                    <div class="position-relative p-5 ps-4 pe-4 text-center text-white d-flex flex-column align-items-center justify-content-center bg-primary"
                        style="min-height: 200px;">
                        <div class="position-relative z-1 w-100">
                            <h2 class="fw-bold mb-2 display-6" id="view_title">Título</h2>
                            <p class="lead fw-light mb-0" id="view_subtitle">Subtítulo</p>
                        </div>
                    </div>

                    {{-- Details List --}}
                    <div class="p-4 bg-white">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Detalles Técnicos</h6>
                        <div class="list-group list-group-flush rounded-3 border-0">
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted small">Estado</span>
                                <span id="view_status_badge" class="badge rounded-pill bg-light text-dark">Default</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted small">ID</span>
                                <span id="view_id" class="text-muted small font-monospace">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-light border-top text-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 fw-medium"
                            data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">¿Estás seguro?</h5>
                    <p class="text-muted mb-4 small">
                        Estás a punto de eliminar la sección <br>
                        "<span id="delete_title_preview" class="fw-bold text-dark"></span>". <br>
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                            data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteSectionForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-trash-fill me-2"></i>Sí, Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- TOOLTIPS ---
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl));

            // --- ELEMENTS ---
            const els = {
                editForm: document.getElementById('editSectionForm'),
                editTitle: document.getElementById('edit_title'),
                editSubtitle: document.getElementById('edit_subtitle'),
                editActive: document.getElementById('edit_is_active'),

                viewTitle: document.getElementById('view_title'),
                viewSubtitle: document.getElementById('view_subtitle'),
                viewStatus: document.getElementById('view_status_badge'),
                viewId: document.getElementById('view_id'),

                deleteForm: document.getElementById('deleteSectionForm'),
                deletePreview: document.getElementById('delete_title_preview'),
            };

            const viewModal = new bootstrap.Modal(document.getElementById('viewSectionModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editSectionModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteSectionModal'));

            // --- EVENT DELEGATION ---
            document.body.addEventListener('click', function(e) {

                // Edit
                const editBtn = e.target.closest('[data-edit-trigger]');
                if (editBtn) {
                    e.preventDefault();
                    const section = JSON.parse(editBtn.dataset.section);
                    const action = editBtn.dataset.action;

                    els.editForm.action = action;
                    els.editTitle.value = section.title || '';
                    els.editSubtitle.value = section.subtitle || '';
                    els.editActive.checked = !!section.is_active;

                    editModal.show();
                }

                // View
                const viewBtn = e.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    e.preventDefault();
                    const section = JSON.parse(viewBtn.dataset.section);

                    els.viewTitle.textContent = section.title || 'Sin Título';
                    els.viewSubtitle.textContent = section.subtitle || '';
                    els.viewId.textContent = section.id;

                    // Status
                    if (section.is_active) {
                        els.viewStatus.textContent = 'Visible';
                        els.viewStatus.className = 'badge rounded-pill bg-success text-white';
                    } else {
                        els.viewStatus.textContent = 'Oculto';
                        els.viewStatus.className = 'badge rounded-pill bg-secondary text-white';
                    }

                    viewModal.show();
                }

                // Delete
                const deleteBtn = e.target.closest('[data-delete-trigger]');
                if (deleteBtn) {
                    e.preventDefault();
                    const action = deleteBtn.dataset.action;
                    const title = deleteBtn.dataset.title;

                    els.deleteForm.action = action;
                    els.deletePreview.textContent = title;

                    deleteModal.show();
                }
            });
        });
    </script>

    <style>
        /* Custom Styles for this View */
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #052c65 100%);
        }

        .hover-lift {
            transition: transform 0.25s cubic-bezier(0.1, 0.7, 0.1, 1), box-shadow 0.25s;
        }

        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .15) !important;
        }

        .hover-scale:hover {
            transform: scale(1.03);
            transition: transform 0.2s;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .backdrop-blur {
            backdrop-filter: blur(8px);
        }

        .form-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
@endsection
