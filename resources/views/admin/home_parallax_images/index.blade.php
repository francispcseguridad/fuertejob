@extends('layouts.app')

@section('title', 'Gestión de Imágenes Parallax')

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
                            <i class="bi bi-layers-half fs-4 text-white"></i>
                        </div>
                        <span class="text-uppercase small fw-bold text-white-50 tracking-wider">Diseño & Estilo</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-3">Imágenes Parallax</h2>
                    <p class="mb-0 text-white-75 fs-5 fw-light" style="max-width: 600px;">
                        Gestiona las imágenes de fondo con efecto parallax para separar secciones.
                        Crea transiciones visuales impactantes en tu portada.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold shadow-lg hover-scale px-4 py-3"
                        data-bs-toggle="modal" data-bs-target="#createParallaxModal">
                        <i class="bi bi-plus-lg me-2 text-primary"></i>Nueva Imagen
                    </button>
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        @if ($images->count() > 0)
            <div class="row g-4">
                @foreach ($images as $image)
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-lift group">
                            {{-- Image Preview --}}
                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                <img src="{{ $image->image }}" class="w-100 h-100 object-fit-cover"
                                    alt="Parallax {{ $image->id }}">
                                <div class="position-absolute top-0 end-0 p-3 z-2">
                                    <span
                                        class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm
                                {{ $image->is_active ? 'bg-success text-white' : 'bg-secondary text-white-50' }} backdrop-blur">
                                        {{ $image->is_active ? 'Visible' : 'Oculto' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 mb-3 text-muted small">
                                    <i class="bi bi-hash text-primary opacity-50"></i>
                                    <span class="text-uppercase tracking-wider fw-bold" style="font-size: 11px;">ID:
                                        {{ $image->id }}</span>
                                </div>

                                <p class="text-muted mb-4 small">
                                    <i class="bi bi-file-image me-1"></i> {{ basename($image->image) }}
                                </p>

                                <hr class="border-secondary border-opacity-10 my-0 mb-3">

                                {{-- Actions --}}
                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium"
                                        data-view-trigger data-image='@json($image)'>
                                        <i class="bi bi-eye me-1"></i> Ver
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button
                                            class="btn btn-light text-primary btn-icon rounded-circle hover-primary shadow-sm"
                                            data-edit-trigger data-image='@json($image)'
                                            data-action="{{ route('admin.home_parallax_images.update', $image->id) }}"
                                            data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <button
                                            class="btn btn-light text-danger btn-icon rounded-circle hover-danger shadow-sm"
                                            data-delete-trigger
                                            data-action="{{ route('admin.home_parallax_images.destroy', $image->id) }}"
                                            data-id="{{ $image->id }}" data-bs-toggle="tooltip" title="Eliminar">
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
                    <i class="bi bi-images" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold text-muted mb-2">Sin imágenes parallax</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                    Aún no has subido ninguna imagen para los efectos parallax. Sube una para comenzar.
                </p>
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold hover-scale" data-bs-toggle="modal"
                    data-bs-target="#createParallaxModal">
                    <i class="bi bi-plus-lg me-2"></i> Crear Primera Imagen
                </button>
            </div>
        @endif
    </div>

    {{-- MODALS --}}

    {{-- Create Modal --}}
    <div class="modal fade" id="createParallaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Nuevo Contenido</p>
                        <h5 class="modal-title fw-bold text-dark">Subir Imagen Parallax</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.home_parallax_images.store') }}" method="POST" class="modal-body p-4 pt-2"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        {{-- Visuals --}}
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Imagen (Se recortará a
                                    2000x1333)</label>
                                <input type="file" class="form-control" id="create_image_input" accept="image/*"
                                    required>
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
                            <i class="bi bi-save me-2"></i>Guardar Imagen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editParallaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Edición</p>
                        <h5 class="modal-title fw-bold text-dark">Modificar Imagen</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editParallaxForm" method="POST" class="modal-body p-4 pt-2" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        {{-- Visuals --}}
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Imagen (Dejar vacío
                                    para mantener actual)</label>

                                {{-- Current Image Preview --}}
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
    <div class="modal fade" id="viewParallaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-0 overflow-hidden rounded-4">
                    <img id="view_image_full" src="" class="w-100"
                        style="max-height: 500px; object-fit: contain; background: #333;">
                    <div class="p-3 bg-light border-top text-end">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 fw-medium"
                            data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteParallaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">¿Estás seguro?</h5>
                    <p class="text-muted mb-4 small">
                        Estás a punto de eliminar la imagen ID: <span id="delete_id_preview"
                            class="fw-bold text-dark"></span>. <br>
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                            data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteParallaxForm" method="POST">
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
                editForm: document.getElementById('editParallaxForm'),
                editActive: document.getElementById('edit_is_active'),
                editCurrentPreview: document.getElementById('edit_current_preview'),
                editCurrentContainer: document.getElementById('edit_current_img_container'),

                viewImage: document.getElementById('view_image_full'),

                deleteForm: document.getElementById('deleteParallaxForm'),
                deletePreview: document.getElementById('delete_id_preview'),
            };

            const viewModal = new bootstrap.Modal(document.getElementById('viewParallaxModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editParallaxModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteParallaxModal'));

            // --- CROPPER LOGIC ---
            let createCropper = null;
            let editCropper = null;

            function initCropper(imgElement, file, isCreate) {
                const url = URL.createObjectURL(file);
                imgElement.src = url;

                if (isCreate && createCropper) createCropper.destroy();
                if (!isCreate && editCropper) editCropper.destroy();

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

            // Form Submit Interception
            const createForm = document.querySelector(
                'form[action="{{ route('admin.home_parallax_images.store') }}"]');
            const createCroppedInput = document.getElementById('create_cropped_image');

            createForm.addEventListener('submit', function(e) {
                if (createCropper) {
                    e.preventDefault();
                    const canvas = createCropper.getCroppedCanvas({
                        width: 2000,
                        height: 1333
                    });
                    createCroppedInput.value = canvas.toDataURL('image/jpeg', 0.85);
                    createForm.submit();
                }
            });

            const editForm = document.getElementById('editParallaxForm');
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

            // Clean up
            document.getElementById('createParallaxModal').addEventListener('hidden.bs.modal', function() {
                if (createCropper) {
                    createCropper.destroy();
                    createCropper = null;
                }
                createInput.value = '';
                createContainer.classList.add('d-none');
            });

            document.getElementById('editParallaxModal').addEventListener('hidden.bs.modal', function() {
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
                    const image = JSON.parse(editBtn.dataset.image);
                    const action = editBtn.dataset.action;

                    els.editForm.action = action;
                    els.editActive.checked = !!image.is_active;

                    if (image.image) {
                        els.editCurrentPreview.src = image.image;
                        els.editCurrentContainer.classList.remove('d-none');
                    } else {
                        els.editCurrentContainer.classList.add('d-none');
                    }

                    editContainer.classList.add('d-none');
                    editModal.show();
                }

                // View
                const viewBtn = e.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    e.preventDefault();
                    const image = JSON.parse(viewBtn.dataset.image);

                    els.viewImage.src = image.image || '';
                    viewModal.show();
                }

                // Delete
                const deleteBtn = e.target.closest('[data-delete-trigger]');
                if (deleteBtn) {
                    e.preventDefault();
                    const action = deleteBtn.dataset.action;
                    const id = deleteBtn.dataset.id;

                    els.deleteForm.action = action;
                    els.deletePreview.textContent = id;

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
