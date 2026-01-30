@extends('layouts.app')

@section('title', 'Gestión de Banners Hero')

@section('content')
    <div class="mb-4" style="padding: 20px;">
        {{-- Header Card --}}
        <div class="rounded-4 bg-primary bg-gradient text-white p-4 p-md-5 shadow-sm position-relative overflow-hidden mb-5">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="bi bi-images" style="font-size: 10rem;"></i>
            </div>
            <div class="row align-items-center position-relative z-1">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-megaphone-fill fs-4 text-white"></i>
                        </div>
                        <span class="text-uppercase small fw-bold text-white-50 tracking-wider">Marketing & Portada</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-3">Banners Principales (Heroes)</h2>
                    <p class="mb-0 text-white-75 fs-5 fw-light" style="max-width: 600px;">
                        Gestiona las secciones de impacto que tus usuarios ven al entrar.
                        Crea experiencias visuales atractivas para destacar tu propuesta de valor.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold shadow-lg hover-scale px-4 py-3"
                        data-bs-toggle="modal" data-bs-target="#createHeroModal">
                        <i class="bi bi-plus-lg me-2 text-primary"></i>Nuevo Banner
                    </button>
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        @if ($heroes->count() > 0)
            <div class="row g-4">
                @foreach ($heroes as $hero)
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-lift group">
                            <div class="position-absolute top-0 end-0 p-3 z-2">
                                <div class="d-flex gap-2">
                                    <span
                                        class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm
                                {{ $hero->is_active ? 'bg-success text-white' : 'bg-secondary text-white-50' }} backdrop-blur">
                                        {{ $hero->is_active ? 'Visible' : 'Oculto' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="card-body p-4 pt-5 d-flex flex-column h-100">
                                <div class="d-flex align-items-center gap-2 mb-3 mt-2 text-muted small">
                                    <i class="bi bi-hash text-primary opacity-50"></i>
                                    <span class="text-uppercase tracking-wider fw-bold" style="font-size: 11px;">ID:
                                        {{ $hero->id }}</span>
                                </div>

                                <h4 class="fw-bold text-dark mb-2 text-truncate" title="{{ $hero->title }}">
                                    {{ $hero->title }}
                                </h4>

                                <p class="text-muted mb-4 small flex-grow-1"
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">
                                    {{ $hero->subtitle ?: 'Sin subtítulo definido' }}
                                </p>

                                {{-- Metadata Buttons --}}
                                <div class="d-flex gap-2 mb-4 text-xs font-monospace">
                                    @if ($hero->button1_text)
                                        <span class="px-2 py-1 bg-light rounded border text-muted">
                                            <i class="bi bi-link-45deg me-1"></i> Btn 1
                                        </span>
                                    @endif
                                    @if ($hero->button2_text)
                                        <span class="px-2 py-1 bg-light rounded border text-muted">
                                            <i class="bi bi-link-45deg me-1"></i> Btn 2
                                        </span>
                                    @endif
                                    @if ($hero->background_image)
                                        <span class="px-2 py-1 bg-light rounded border text-muted"
                                            title="Tiene imagen de fondo">
                                            <i class="bi bi-image me-1"></i> Img
                                        </span>
                                    @endif
                                </div>

                                <hr class="border-secondary border-opacity-10 my-0 mb-3">

                                {{-- Actions --}}
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium"
                                        data-view-trigger data-hero='@json($hero)'>
                                        <i class="bi bi-eye me-1"></i> Ver
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button
                                            class="btn btn-light text-primary btn-icon rounded-circle hover-primary shadow-sm"
                                            data-edit-trigger data-hero='@json($hero)'
                                            data-action="{{ route('admin.home_heroes.update', $hero->id) }}"
                                            data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <button
                                            class="btn btn-light text-danger btn-icon rounded-circle hover-danger shadow-sm"
                                            data-delete-trigger
                                            data-action="{{ route('admin.home_heroes.destroy', $hero->id) }}"
                                            data-title="{{ $hero->title }}" data-bs-toggle="tooltip" title="Eliminar">
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
                    <i class="bi bi-layers-half" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold text-muted mb-2">Sin banners configurados</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                    Aún no has creado ningún banner hero. Comienza ahora para dar vida a la portada de tu portal.
                </p>
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold hover-scale" data-bs-toggle="modal"
                    data-bs-target="#createHeroModal">
                    <i class="bi bi-plus-lg me-2"></i> Crear Primer Banner
                </button>
            </div>
        @endif
    </div>

    {{-- MODALS --}}

    {{-- Create Modal --}}
    <div class="modal fade" id="createHeroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Nuevo Contenido</p>
                        <h5 class="modal-title fw-bold text-dark">Crear Banner Hero</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.home_heroes.store') }}" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        {{-- Main Info --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Título
                                    Principal</label>
                                <input type="text"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    name="title" required placeholder="Ej: ¡Encuentra tu próximo empleo!">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Subtítulo</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    name="subtitle" placeholder="Ej: Miles de ofertas te esperan en FuerteJob.">
                            </div>
                        </div>

                        {{-- Buttons Config --}}
                        <h6 class="fw-bold text-muted mb-3 small text-uppercase border-bottom pb-2">Botones de Acción (CTA)
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 1 (Texto)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    name="button1_text" placeholder="Ej: Ver Ofertas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 1 (URL)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    name="button1_url" placeholder="/ofertas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 2 (Texto)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    name="button2_text" placeholder="Ej: Publicar Oferta">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 2 (URL)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    name="button2_url" placeholder="/empresa/publicar">
                            </div>
                        </div>

                        {{-- Visuals --}}
                        <h6 class="fw-bold text-muted mb-3 small text-uppercase border-bottom pb-2">Visuales y Estado</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted">Imagen de Fondo (Se recortará a
                                    2000x1333)</label>
                                <input type="file" class="form-control" id="create_image_input" accept="image/*">
                                <input type="hidden" name="cropped_image" id="create_cropped_image">

                                {{-- Cropping Area --}}
                                <div class="mt-3 d-none" id="create_crop_container">
                                    <div class="img-preview-container overflow-hidden">
                                        <img id="create_image_preview" style="max-width: 100%; display: block;">
                                    </div>
                                    <div class="text-muted small mt-2 text-center">Ajusta el recorte y se guardará
                                        automáticamente al guardar.</div>
                                </div>
                            </div>
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
                            <i class="bi bi-save me-2"></i>Guardar Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editHeroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Edición</p>
                        <h5 class="modal-title fw-bold text-dark">Modificar Banner</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editHeroForm" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    @method('PUT')
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        {{-- Main Info --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Título
                                    Principal</label>
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

                        {{-- Buttons Config --}}
                        <h6 class="fw-bold text-muted mb-3 small text-uppercase border-bottom pb-2">Botones de Acción (CTA)
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 1 (Texto)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_btn1_text" name="button1_text">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 1 (URL)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_btn1_url" name="button1_url">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 2 (Texto)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_btn2_text" name="button2_text">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Botón 2 (URL)</label>
                                <input type="text" class="form-control rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_btn2_url" name="button2_url">
                            </div>
                        </div>

                        {{-- Visuals --}}
                        <h6 class="fw-bold text-muted mb-3 small text-uppercase border-bottom pb-2">Visuales y Estado</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted">Imagen de Fondo (Se recortará a
                                    2000x1333)</label>

                                {{-- Current Image Preview if exists --}}
                                <div id="edit_current_img_container" class="mb-3 d-none">
                                    <p class="small text-muted mb-1">Imagen Actual:</p>
                                    <img id="edit_current_preview" src="" class="img-fluid rounded shadow-sm"
                                        style="max-height: 200px;">
                                </div>

                                <input type="file" class="form-control" id="edit_image_input" accept="image/*">
                                <input type="hidden" name="cropped_image" id="edit_cropped_image">

                                {{-- Cropping Area --}}
                                <div class="mt-3 d-none" id="edit_crop_container">
                                    <div class="img-preview-container overflow-hidden">
                                        <img id="edit_image_preview" style="max-width: 100%; display: block;">
                                    </div>
                                    <div class="text-muted small mt-2 text-center">Ajusta el recorte y se guardará
                                        automáticamente al actualizar.</div>
                                </div>
                            </div>
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
    <div class="modal fade" id="viewHeroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-0 overflow-hidden rounded-4">
                    {{-- Mock Hero --}}
                    <div class="position-relative p-5 ps-4 pe-4 text-center text-white d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 300px; background-size: cover; background-position: center;" id="view_hero_bg">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50"
                            style="backdrop-filter: blur(2px);"></div>

                        <div class="position-relative z-1 w-100">
                            <h2 class="fw-bold mb-2 display-6" id="view_title">Título</h2>
                            <p class="lead fw-light mb-4" id="view_subtitle">Subtítulo</p>

                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <span class="btn btn-primary rounded-pill px-4" id="view_btn1">Btn 1</span>
                                <span class="btn btn-outline-light rounded-pill px-4" id="view_btn2">Btn 2</span>
                            </div>
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
                                <span class="text-muted small">URL Btn 1</span>
                                <span id="view_url1" class="text-truncate small" style="max-width: 200px;">-</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted small">URL Btn 2</span>
                                <span id="view_url2" class="text-truncate small" style="max-width: 200px;">-</span>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="text-muted small mb-1">Background URL</div>
                                <code class="d-block bg-light p-2 rounded small text-wrap text-break"
                                    id="view_bg_url">-</code>
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
    <div class="modal fade" id="deleteHeroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">¿Estás seguro?</h5>
                    <p class="text-muted mb-4 small">
                        Estás a punto de eliminar el banner <br>
                        "<span id="delete_title_preview" class="fw-bold text-dark"></span>". <br>
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                            data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteHeroForm" method="POST">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- TOOLTIPS ---
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl));

            // --- ELEMENTS ---
            const els = {
                editForm: document.getElementById('editHeroForm'),
                editTitle: document.getElementById('edit_title'),
                editSubtitle: document.getElementById('edit_subtitle'),
                editBtn1Text: document.getElementById('edit_btn1_text'),
                editBtn1Url: document.getElementById('edit_btn1_url'),
                editBtn2Text: document.getElementById('edit_btn2_text'),
                editBtn2Url: document.getElementById('edit_btn2_url'),
                // editBgImage: document.getElementById('edit_bg_image'), // Removed
                editActive: document.getElementById('edit_is_active'),
                editCurrentPreview: document.getElementById('edit_current_preview'),
                editCurrentContainer: document.getElementById('edit_current_img_container'),

                viewTitle: document.getElementById('view_title'),
                viewSubtitle: document.getElementById('view_subtitle'),
                viewBtn1: document.getElementById('view_btn1'),
                viewBtn2: document.getElementById('view_btn2'),
                viewBg: document.getElementById('view_hero_bg'),
                viewStatus: document.getElementById('view_status_badge'),
                viewUrl1: document.getElementById('view_url1'),
                viewUrl2: document.getElementById('view_url2'),
                viewBgUrl: document.getElementById('view_bg_url'),

                deleteForm: document.getElementById('deleteHeroForm'),
                deletePreview: document.getElementById('delete_title_preview'),
            };

            const viewModal = new bootstrap.Modal(document.getElementById('viewHeroModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editHeroModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteHeroModal'));

            // --- CROPPER LOGIC ---
            let createCropper = null;
            let editCropper = null;

            function initCropper(imgElement, file, isCreate) {
                const url = URL.createObjectURL(file);
                imgElement.src = url;

                // Destroy existing if any
                if (isCreate && createCropper) {
                    createCropper.destroy();
                }
                if (!isCreate && editCropper) {
                    editCropper.destroy();
                }

                const cropper = new Cropper(imgElement, {
                    aspectRatio: 2000 / 1333,
                    viewMode: 1,
                    autoCropArea: 1,
                });

                if (isCreate) createCropper = cropper;
                else editCropper = cropper;
            }

            // Handle Create Input
            const createInput = document.getElementById('create_image_input');
            const createPreview = document.getElementById('create_image_preview');
            const createContainer = document.getElementById('create_crop_container');

            createInput.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    createContainer.classList.remove('d-none');
                    initCropper(createPreview, files[0], true);
                }
            });

            // Handle Edit Input
            const editInput = document.getElementById('edit_image_input');
            const editPreview = document.getElementById('edit_image_preview');
            const editContainer = document.getElementById('edit_crop_container');

            editInput.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    editContainer.classList.remove('d-none');
                    initCropper(editPreview, files[0], false);
                }
            });

            // Intercept Forms to inject base64
            const createForm = document.querySelector('form[action="{{ route('admin.home_heroes.store') }}"]');
            const createCroppedInput = document.getElementById('create_cropped_image');

            createForm.addEventListener('submit', function(e) {
                if (createCropper) {
                    e.preventDefault(); // Stop valid submission momentarily
                    const canvas = createCropper.getCroppedCanvas({
                        width: 2000,
                        height: 1333
                    });
                    createCroppedInput.value = canvas.toDataURL('image/jpeg', 0.85);
                    createForm.submit(); // Re-submit
                }
            });

            const editForm = document.getElementById('editHeroForm');
            const editCroppedInput = document.getElementById('edit_cropped_image');

            editForm.addEventListener('submit', function(e) {
                if (editCropper) {
                    e.preventDefault();
                    const canvas = editCropper.getCroppedCanvas({
                        width: 2000,
                        height: 1333
                    });
                    editCroppedInput.value = canvas.toDataURL('image/jpeg', 0.85);
                    editForm.submit();
                }
            });

            // Clean up on modal close
            document.getElementById('createHeroModal').addEventListener('hidden.bs.modal', function() {
                if (createCropper) {
                    createCropper.destroy();
                    createCropper = null;
                }
                createInput.value = '';
                createContainer.classList.add('d-none');
            });

            document.getElementById('editHeroModal').addEventListener('hidden.bs.modal', function() {
                if (editCropper) {
                    editCropper.destroy();
                    editCropper = null;
                }
                editInput.value = '';
                editContainer.classList.add('d-none');
            });


            // --- EVENT DELEGATION ---
            document.body.addEventListener('click', function(e) {

                // Edit
                const editBtn = e.target.closest('[data-edit-trigger]');
                if (editBtn) {
                    e.preventDefault();
                    const hero = JSON.parse(editBtn.dataset.hero);
                    const action = editBtn.dataset.action;

                    els.editForm.action = action;
                    els.editTitle.value = hero.title || '';
                    els.editSubtitle.value = hero.subtitle || '';
                    els.editBtn1Text.value = hero.button1_text || '';
                    els.editBtn1Url.value = hero.button1_url || '';
                    els.editBtn2Text.value = hero.button2_text || '';
                    els.editBtn2Url.value = hero.button2_url || '';
                    // els.editBgImage.value = hero.background_image || ''; // Removed
                    els.editActive.checked = !!hero.is_active;

                    // Handle Image Preview
                    if (hero.background_image) {
                        els.editCurrentPreview.src = hero.background_image;
                        els.editCurrentContainer.classList.remove('d-none');
                    } else {
                        els.editCurrentContainer.classList.add('d-none');
                    }

                    // Hide cropper container initially
                    editContainer.classList.add('d-none');

                    editModal.show();
                }

                // View
                const viewBtn = e.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    e.preventDefault();
                    const hero = JSON.parse(viewBtn.dataset.hero);

                    els.viewTitle.textContent = hero.title || 'Sin Título';
                    els.viewSubtitle.textContent = hero.subtitle || '';

                    // Button 1
                    if (hero.button1_text) {
                        els.viewBtn1.textContent = hero.button1_text;
                        els.viewBtn1.style.display = 'inline-block';
                        els.viewUrl1.textContent = hero.button1_url || '#';
                    } else {
                        els.viewBtn1.style.display = 'none';
                        els.viewUrl1.textContent = '-';
                    }

                    // Button 2
                    if (hero.button2_text) {
                        els.viewBtn2.textContent = hero.button2_text;
                        els.viewBtn2.style.display = 'inline-block';
                        els.viewUrl2.textContent = hero.button2_url || '#';
                    } else {
                        els.viewBtn2.style.display = 'none';
                        els.viewUrl2.textContent = '-';
                    }

                    // Background
                    if (hero.background_image) {
                        els.viewBg.style.backgroundImage = `url('${hero.background_image}')`;
                        els.viewBgUrl.textContent = hero.background_image;
                    } else {
                        els.viewBg.style.backgroundImage = 'none';
                        els.viewBg.style.backgroundColor = '#6c757d'; // default gray
                        els.viewBgUrl.textContent = 'Ninguna';
                    }

                    // Status
                    if (hero.is_active) {
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

        /* Cropper Fixes */
        .cropper-view-box,
        .cropper-face {
            border-radius: 4px;
        }

        .img-preview-container {
            background: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
@endsection
```
