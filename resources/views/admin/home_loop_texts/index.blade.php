@extends('layouts.app')

@section('title', 'Gestión de Textos Loop')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Textos en Bucle (News Ticker)</h1>
                <p class="text-muted small mb-0">Gestiona los textos que se repiten en el banner con imagen en 600x600.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLoopTextModal">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Texto
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if ($texts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="min-width: 90px;">Imagen</th>
                                    <th>Contenido</th>
                                    <th>URL</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($texts as $text)
                                    <tr>
                                        <td class="ps-4 pe-0">
                                            <div class="d-flex align-items-center py-2">
                                                @if ($text->image)
                                                    <img src="{{ asset($text->image) }}" alt="Imagen {{ $text->id }}"
                                                        class="rounded shadow-sm"
                                                        style="width:56px;height:56px;object-fit:cover;">
                                                @else
                                                    <div class="bg-secondary bg-opacity-10 border border-dashed border-secondary rounded d-flex align-items-center justify-content-center"
                                                        style="width:56px;height:56px;">
                                                        <i class="bi bi-image text-secondary"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="ps-0">
                                            <p class="mb-0 text-truncate" style="max-width: 480px;">
                                                {{ Str::limit(strip_tags($text->content), 130) }}
                                            </p>
                                        </td>
                                        <td class="text-break" style="max-width: 220px;">
                                            @if ($text->url)
                                                <a href="{{ $text->url }}" target="_blank" rel="noopener"
                                                    class="text-decoration-none text-primary">{{ Str::limit($text->url, 40) }}</a>
                                            @else
                                                <span class="text-muted small">Sin URL</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if ($text->is_active)
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-white rounded-pill px-3">Activo</span>
                                            @else
                                                <span
                                                    class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-end align-middle pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    data-view-trigger data-text='@json($text)'>
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-edit-trigger data-text='@json($text)'
                                                    data-action="{{ route('admin.home_loop_texts.update', $text->id) }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.home_loop_texts.destroy', $text->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Está seguro de que desea eliminar este texto?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted">
                            <i class="bi bi-type fs-1"></i>
                        </div>
                        <h5 class="text-muted">No hay textos creados</h5>
                        <p class="text-muted small">Añade textos para la sección de noticias en bucle.</p>
                        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal"
                            data-bs-target="#createLoopTextModal">
                            <i class="bi bi-plus-lg me-1"></i> Crear Texto
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createLoopTextModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Nuevo Texto</p>
                        <h5 class="modal-title fw-bold text-dark">Crear Texto Loop</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="createLoopTextForm" action="{{ route('admin.home_loop_texts.store') }}" method="POST"
                    class="modal-body p-4 pt-2">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contenido</label>
                        <textarea name="content" id="create_content" rows="4" class="form-control d-none"
                            placeholder="Ej: · Tu Próximo Éxito Comienza Aquí..."></textarea>
                        <div id="create_content_editor" class="quill-editor border"></div>
                        <div class="form-text">Este texto se repetirá en el banner de noticias en bucle.</div>
                    </div>
                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active"
                            value="1" checked>
                        <label class="form-check-label" for="create_is_active">Activo</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Imagen 600x600</label>
                        <input class="form-control" type="file" id="create_image_input" accept="image/*">
                        <div class="form-text">Selecciona una imagen cuadrada para que aparezca junto al texto (opcional).
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Página web</label>
                        <input class="form-control" type="url" name="url" id="create_url"
                            placeholder="https://ejemplo.com">
                        <div class="form-text">La URL se mostrará para acompañar el texto en el loop.</div>
                    </div>
                    <div id="create_crop_container" class="d-none mt-3">
                        <div class="img-preview-container rounded-3">
                            <img id="create_image_preview" class="img-fluid" alt="Preview">
                        </div>
                    </div>
                    <input type="hidden" name="cropped_image" id="create_cropped_image">
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editLoopTextModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-secondary small fw-bold mb-1 tracking-wider">Actualización</p>
                        <h5 class="modal-title fw-bold text-dark">Editar Texto Loop</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editLoopTextForm" action="#" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contenido</label>
                        <textarea name="content" id="edit_content" rows="4" class="form-control d-none"></textarea>
                        <div id="edit_content_editor" class="quill-editor border"></div>
                    </div>
                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active"
                            value="1">
                        <label class="form-check-label" for="edit_is_active">Activo</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reemplazar imagen</label>
                        <input class="form-control" type="file" id="edit_image_input" accept="image/*">
                        <div class="form-text">Sube una nueva imagen cuadrada para reemplazar la existente (opcional).
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Página web</label>
                        <input class="form-control" type="url" name="url" id="edit_url"
                            placeholder="https://ejemplo.com">
                        <div class="form-text">Actualiza o limpia la URL asociada con este texto.</div>
                    </div>
                    <div id="edit_crop_container" class="d-none mt-3">
                        <div class="img-preview-container rounded-3">
                            <img id="edit_image_preview" class="img-fluid" alt="Preview">
                        </div>
                    </div>
                    <div id="edit_current_img_container" class="d-none text-center mt-4">
                        <p class="text-muted small mb-2">Imagen actual</p>
                        <img id="edit_current_preview" class="img-fluid rounded-3 shadow-sm" alt="Actual">
                    </div>
                    <input type="hidden" name="cropped_image" id="edit_cropped_image">
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Modal --}}
    <div class="modal fade" id="viewLoopTextModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-muted small fw-semibold mb-1 tracking-wider">Detalles</p>
                        <h5 class="modal-title fw-bold">Vista previa del Texto</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-2">
                    <span id="view_loop_status_badge" class="badge mb-3"></span>
                    <p id="view_loop_content" class="lead text-dark mb-2"></p>
                    <div id="view_loop_url_container" class="d-none mb-3">
                        <p class="small text-uppercase text-muted mb-1">Página web</p>
                        <a id="view_loop_url" class="d-block text-primary fw-semibold" target="_blank"
                            rel="noopener"></a>
                    </div>
                    <div id="view_loop_image_container" class="d-none">
                        <img id="view_loop_image" class="img-fluid rounded-3 shadow-sm mb-2" alt="Imagen Loop">
                        <p class="small text-muted" id="view_loop_image_caption"></p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-medium" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createLoopTextForm');
            const editForm = document.getElementById('editLoopTextForm');
            const editModalElement = document.getElementById('editLoopTextModal');
            const editCropContainer = document.getElementById('edit_crop_container');
            const editCurrentContainer = document.getElementById('edit_current_img_container');
            const editCurrentPreview = document.getElementById('edit_current_preview');
            const editUrlInput = document.getElementById('edit_url');
            const createContentTextarea = document.getElementById('create_content');
            const editContentTextarea = document.getElementById('edit_content');

            const quillToolbarOptions = [
                [{
                    header: [1, 2, 3, false]
                }],
                ['bold', 'italic', 'underline', 'strike'],
                [{
                    list: 'ordered'
                }, {
                    list: 'bullet'
                }, {
                    indent: '-1'
                }, {
                    indent: '+1'
                }],
                [{
                    align: []
                }],
                ['blockquote', 'link'],
                ['clean']
            ];

            const createQuill = new Quill('#create_content_editor', {
                modules: {
                    toolbar: quillToolbarOptions
                },
                theme: 'snow',
                placeholder: 'Ej: · Tu Próximo Éxito Comienza Aquí...'
            });

            const editQuill = new Quill('#edit_content_editor', {
                modules: {
                    toolbar: quillToolbarOptions
                },
                theme: 'snow',
                placeholder: 'Escribe o pega el contenido enriquecido aquí'
            });

            function wrapLoopText(html) {
                if (!html) {
                    return '';
                }
                const trimmed = html.trim();
                if (!trimmed) {
                    return '';
                }

                const marker = 'data-loop-style="true"';
                if (trimmed.startsWith('<div') && trimmed.includes(marker)) {
                    return trimmed;
                }

                return `<div ${marker} style="color:#fff;background:#1d486c;padding:0.65rem 1rem;border-radius:0.75rem;">${trimmed}</div>`;
            }

            function syncEditorContent(editor, textareaElement) {
                textareaElement.value = wrapLoopText(editor.root.innerHTML);
            }

            function resetCreateEditor() {
                createQuill.setContents([]);
                createContentTextarea.value = '';
            }

            function resetEditEditor() {
                editQuill.setContents([]);
                editContentTextarea.value = '';
            }

            let createCropper = null;
            let editCropper = null;

            function initCropper(imgElement, file, type) {
                const url = URL.createObjectURL(file);
                imgElement.src = url;

                if (type === 'create' && createCropper) {
                    createCropper.destroy();
                }
                if (type === 'edit' && editCropper) {
                    editCropper.destroy();
                }

                const instance = new Cropper(imgElement, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                });

                if (type === 'create') {
                    createCropper = instance;
                } else if (type === 'edit') {
                    editCropper = instance;
                }
            }

            // Create input
            const createInput = document.getElementById('create_image_input');
            const createPreview = document.getElementById('create_image_preview');
            const createCropContainer = document.getElementById('create_crop_container');

            createInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    createCropContainer.classList.remove('d-none');
                    initCropper(createPreview, file, 'create');
                }
            });

            // Edit input
            const editInput = document.getElementById('edit_image_input');
            const editPreview = document.getElementById('edit_image_preview');

            editInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    editCropContainer.classList.remove('d-none');
                    initCropper(editPreview, file, 'edit');
                }
            });

            // Handle form submissions
            createForm.addEventListener('submit', function(e) {
                syncEditorContent(createQuill, createContentTextarea);

                if (!createContentTextarea.value.trim()) {
                    e.preventDefault();
                    // You might want to show a more user-friendly error message here
                    alert('El contenido es obligatorio');
                    return;
                }

                if (createCropper) {
                    e.preventDefault();
                    const canvas = createCropper.getCroppedCanvas({
                        width: 600,
                        height: 600
                    });
                    document.getElementById('create_cropped_image').value = canvas.toDataURL('image/jpeg',
                        0.9);
                    this.submit();
                }
            });

            editForm.addEventListener('submit', function(e) {
                syncEditorContent(editQuill, editContentTextarea);

                if (!editContentTextarea.value.trim()) {
                    e.preventDefault();
                    alert('El contenido es obligatorio');
                    return;
                }

                if (editCropper) {
                    e.preventDefault();
                    const canvas = editCropper.getCroppedCanvas({
                        width: 600,
                        height: 600
                    });
                    document.getElementById('edit_cropped_image').value = canvas.toDataURL('image/jpeg',
                        0.9);
                    this.submit();
                }
            });

            document.getElementById('createLoopTextModal').addEventListener('hidden.bs.modal', function() {
                if (createCropper) {
                    createCropper.destroy();
                    createCropper = null;
                }
                createInput.value = '';
                createCropContainer.classList.add('d-none');
                document.getElementById('create_cropped_image').value = '';
                createPreview.removeAttribute('src');
                document.getElementById('create_url').value = '';
                resetCreateEditor();
            });

            editModalElement.addEventListener('hidden.bs.modal', function() {
                if (editCropper) {
                    editCropper.destroy();
                    editCropper = null;
                }
                editInput.value = '';
                editCropContainer.classList.add('d-none');
                document.getElementById('edit_cropped_image').value = '';
                editUrlInput.value = '';
                resetEditEditor();
            });

            const viewModal = new bootstrap.Modal(document.getElementById('viewLoopTextModal'));
            const editModal = new bootstrap.Modal(editModalElement);

            const viewContent = document.getElementById('view_loop_content');
            const viewBadge = document.getElementById('view_loop_status_badge');
            const viewImage = document.getElementById('view_loop_image');
            const viewImageContainer = document.getElementById('view_loop_image_container');
            const viewImageCaption = document.getElementById('view_loop_image_caption');
            const viewUrlContainer = document.getElementById('view_loop_url_container');
            const viewUrlLink = document.getElementById('view_loop_url');

            document.body.addEventListener('click', function(event) {
                const viewBtn = event.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    event.preventDefault();
                    const text = JSON.parse(viewBtn.dataset.text);
                    viewContent.innerHTML = text.content || '<span class="text-muted">Sin contenido</span>';
                    if (text.is_active) {
                        viewBadge.textContent = 'Activo';
                        viewBadge.className = 'badge rounded-pill bg-success text-white';
                    } else {
                        viewBadge.textContent = 'Inactivo';
                        viewBadge.className = 'badge rounded-pill bg-secondary text-white';
                    }

                    if (text.image) {
                        viewImage.src = text.image;
                        viewImageContainer.classList.remove('d-none');
                        viewImageCaption.textContent = text.image;
                    } else {
                        viewImage.removeAttribute('src');
                        viewImageContainer.classList.add('d-none');
                    }
                    if (text.url) {
                        viewUrlLink.textContent = text.url;
                        viewUrlLink.href = text.url;
                        viewUrlContainer.classList.remove('d-none');
                    } else {
                        viewUrlLink.removeAttribute('href');
                        viewUrlContainer.classList.add('d-none');
                    }

                    viewModal.show();
                    return;
                }

                const editBtn = event.target.closest('[data-edit-trigger]');
                if (editBtn) {
                    event.preventDefault();
                    const text = JSON.parse(editBtn.dataset.text);
                    editForm.action = editBtn.dataset.action;
                    editQuill.setContents([]);
                    editQuill.clipboard.dangerouslyPasteHTML(text.content || '');
                    editContentTextarea.value = text.content || '';
                    document.getElementById('edit_is_active').checked = !!text.is_active;

                    if (text.image) {
                        editCurrentPreview.src = text.image;
                        editCurrentContainer.classList.remove('d-none');
                    } else {
                        editCurrentContainer.classList.add('d-none');
                        editCurrentPreview.removeAttribute('src');
                    }
                    editUrlInput.value = text.url || '';

                    editCropContainer.classList.add('d-none');
                    editModal.show();
                    return;
                }
            });
        });
    </script>

    <style>
        .form-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }

        .img-preview-container {
            background: #f8f9fa;
            min-height: 240px;
            border: 1px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .img-preview-container img {
            max-height: 340px;
            width: auto;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        .quill-editor {
            min-height: 220px;
            border-radius: 0.75rem;
        }

        .quill-editor .ql-toolbar {
            border-radius: 0.75rem 0.75rem 0 0;
            background: #0f335c;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .quill-editor .ql-toolbar button,
        .quill-editor .ql-toolbar .ql-picker-label {
            color: #fff;
        }

        .quill-editor .ql-editor {
            background: #1d486c;
            color: #fff;
            min-height: 180px;
            border-left: 1px solid rgba(255, 255, 255, 0.25);
            border-right: 1px solid rgba(255, 255, 255, 0.25);
            border-bottom: 1px solid rgba(255, 255, 255, 0.25);
            padding: 0.75rem;
        }
    </style>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
@endsection
