@extends('layouts.app')

@section('title', 'Gestión de Inmobiliarias')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Inmobiliarias</h1>
                <p class="text-muted small mb-0">Administración de inmobiliarias con los datos de contacto y logotipo en
                    600×600.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInmobiliariaModal">
                <i class="bi bi-plus-lg me-1"></i> Nueva inmobiliaria
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if ($inmobiliarias->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="min-width: 90px;">Logo</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Página web</th>
                                    <th>Isla</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inmobiliarias as $inmobiliaria)
                                    <tr>
                                        <td class="ps-4 pe-0">
                                            <div class="d-flex align-items-center py-2">
                                                @if ($inmobiliaria->logo)
                                                    <img src="{{ asset($inmobiliaria->logo) }}"
                                                        alt="Logo {{ $inmobiliaria->name }}" class="rounded shadow-sm"
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
                                            <strong>{{ $inmobiliaria->name }}</strong>
                                        </td>
                                        <td class="text-muted" style="max-width: 210px;">
                                            {{ $inmobiliaria->address }}
                                        </td>
                                        <td>{{ $inmobiliaria->phone }}</td>
                                        <td>{{ $inmobiliaria->email }}</td>
                                        <td class="text-break" style="max-width: 200px;">
                                            @if ($inmobiliaria->website)
                                                <a href="{{ $inmobiliaria->website }}" target="_blank" rel="noopener"
                                                    class="text-decoration-none text-primary">{{ Str::limit($inmobiliaria->website, 40) }}</a>
                                            @else
                                                <span class="text-muted small">Sin sitio</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $inmobiliaria->island->name ?? '—' }}
                                        </td>
                                        <td class="text-end align-middle pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-edit-trigger
                                                    data-inmobiliaria="{{ json_encode([
                                                        'name' => $inmobiliaria->name,
                                                        'address' => $inmobiliaria->address,
                                                        'phone' => $inmobiliaria->phone,
                                                        'email' => $inmobiliaria->email,
                                                        'website' => $inmobiliaria->website,
                                                        'island_id' => $inmobiliaria->island_id,
                                                        'logo' => $inmobiliaria->logo ? asset($inmobiliaria->logo) : null,
                                                    ]) }}"
                                                    data-action="{{ route('admin.inmobiliarias.update', $inmobiliaria->id) }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form
                                                    action="{{ route('admin.inmobiliarias.destroy', $inmobiliaria->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Eliminar esta inmobiliaria?');">
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
                            <i class="bi bi-building fs-1"></i>
                        </div>
                        <h5 class="text-muted">No hay inmobiliarias registradas</h5>
                        <p class="text-muted small">Agrega los datos de contacto y logotipos para mostrar en el portal.</p>
                        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal"
                            data-bs-target="#createInmobiliariaModal">
                            <i class="bi bi-plus-lg me-1"></i> Crear inmobiliaria
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createInmobiliariaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Nueva inmobiliaria</p>
                        <h5 class="modal-title fw-bold text-dark">Crear inmobiliaria</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="createInmobiliariaForm" action="{{ route('admin.inmobiliarias.store') }}" method="POST"
                    class="modal-body p-4 pt-2">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" name="name" class="form-control" required
                                placeholder="Nombre de la inmobiliaria">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Isla</label>
                            <select name="island_id" class="form-select" required>
                                <option value="" selected disabled>Selecciona una isla</option>
                                @foreach ($islands as $island)
                                    <option value="{{ $island->id }}">{{ $island->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" name="address" class="form-control" required
                                placeholder="Calle, número, ciudad">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="phone" class="form-control" required placeholder="+34 ...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" required
                                placeholder="contacto@ejemplo.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Página web</label>
                            <input type="url" name="website" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Logotipo 600×600</label>
                        <input type="file" id="create_logo_input" class="form-control" accept="image/*">
                        <div class="form-text">Sube una imagen cuadrada para recortarla y guardarla como 600x600.</div>
                    </div>
                    <div id="create_crop_container" class="d-none mt-3">
                        <div class="img-preview-container rounded-3">
                            <img id="create_logo_preview" class="img-fluid" alt="Vista previa">
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
    <div class="modal fade" id="editInmobiliariaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-secondary small fw-bold mb-1 tracking-wider">Actualización</p>
                        <h5 class="modal-title fw-bold text-dark">Editar inmobiliaria</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editInmobiliariaForm" action="#" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Isla</label>
                            <select name="island_id" id="edit_island_id" class="form-select" required>
                                <option value="" disabled>Selecciona una isla</option>
                                @foreach ($islands as $island)
                                    <option value="{{ $island->id }}">{{ $island->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" name="address" id="edit_address" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Página web</label>
                            <input type="url" name="website" id="edit_website" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Reemplazar logotipo (opcional)</label>
                        <input type="file" id="edit_logo_input" class="form-control" accept="image/*">
                    </div>
                    <div id="edit_crop_container" class="d-none mt-3">
                        <div class="img-preview-container rounded-3">
                            <img id="edit_logo_preview" class="img-fluid" alt="Vista previa">
                        </div>
                    </div>
                    <div id="edit_current_logo_container" class="d-none mt-4 text-center">
                        <p class="text-muted small mb-2">Logotipo actual</p>
                        <img id="edit_current_logo" class="img-fluid rounded-3 shadow-sm" style="max-height:140px"
                            alt="Actual">
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
@endsection

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <style>
        .form-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }

        .img-preview-container {
            background: #f8f9fa;
            min-height: 220px;
            border: 1px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .img-preview-container img {
            max-height: 320px;
            width: auto;
        }

        .border-dashed {
            border-style: dashed !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createInmobiliariaForm');
            const editForm = document.getElementById('editInmobiliariaForm');
            const editModalElement = document.getElementById('editInmobiliariaModal');

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
                } else {
                    editCropper = instance;
                }
            }

            const createInput = document.getElementById('create_logo_input');
            const createPreview = document.getElementById('create_logo_preview');
            const createCropContainer = document.getElementById('create_crop_container');

            createInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    createCropContainer.classList.remove('d-none');
                    initCropper(createPreview, file, 'create');
                }
            });

            const editInput = document.getElementById('edit_logo_input');
            const editPreview = document.getElementById('edit_logo_preview');
            const editCropContainer = document.getElementById('edit_crop_container');
            const editCurrentContainer = document.getElementById('edit_current_logo_container');
            const editCurrentLogo = document.getElementById('edit_current_logo');
            const editIslandSelect = document.getElementById('edit_island_id');

            editInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    editCropContainer.classList.remove('d-none');
                    initCropper(editPreview, file, 'edit');
                }
            });

            createForm.addEventListener('submit', function(e) {
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

            document.getElementById('createInmobiliariaModal').addEventListener('hidden.bs.modal', function() {
                if (createCropper) {
                    createCropper.destroy();
                    createCropper = null;
                }
                createInput.value = '';
                createCropContainer.classList.add('d-none');
                document.getElementById('create_cropped_image').value = '';
                createPreview.removeAttribute('src');
                createForm.reset();
            });

            editModalElement.addEventListener('hidden.bs.modal', function() {
                if (editCropper) {
                    editCropper.destroy();
                    editCropper = null;
                }
                editInput.value = '';
                editCropContainer.classList.add('d-none');
                document.getElementById('edit_cropped_image').value = '';
                editIslandSelect.value = '';
                editCurrentContainer.classList.add('d-none');
            });

            const editModal = new bootstrap.Modal(editModalElement);

            document.body.addEventListener('click', function(event) {
                const editBtn = event.target.closest('[data-edit-trigger]');
                if (editBtn) {
                    event.preventDefault();
                    const data = JSON.parse(editBtn.dataset.inmobiliaria);
                    editForm.action = editBtn.dataset.action;
                    document.getElementById('edit_name').value = data.name || '';
                    document.getElementById('edit_address').value = data.address || '';
                    document.getElementById('edit_phone').value = data.phone || '';
                    document.getElementById('edit_email').value = data.email || '';
                    document.getElementById('edit_website').value = data.website || '';
                    editIslandSelect.value = data.island_id || '';

                    if (data.logo) {
                        editCurrentLogo.src = data.logo;
                        editCurrentContainer.classList.remove('d-none');
                    } else {
                        editCurrentContainer.classList.add('d-none');
                        editCurrentLogo.removeAttribute('src');
                    }

                    editCropContainer.classList.add('d-none');
                    editPreview.removeAttribute('src');
                    editModal.show();
                }
            });
        });
    </script>
@endsection
