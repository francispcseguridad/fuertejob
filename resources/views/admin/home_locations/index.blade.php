@extends('layouts.app')

@section('title', 'Gestión de Ubicaciones')

@section('content')
    <div class="mb-4" style="padding: 20px;">
        {{-- Header Card --}}
        <div class="rounded-4 bg-primary bg-gradient text-white p-4 p-md-5 shadow-sm position-relative overflow-hidden mb-5">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="bi bi-map-fill" style="font-size: 10rem;"></i>
            </div>
            <div class="row align-items-center position-relative z-1">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px;">
                            <i class="bi bi-geo-alt-fill fs-4 text-white"></i>
                        </div>
                        <span class="text-uppercase small fw-bold text-white-50 tracking-wider">Configuración &
                            Portada</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-3">Ubicaciones Destacadas</h2>
                    <p class="mb-0 text-white-75 fs-5 fw-light" style="max-width: 600px;">
                        Gestiona las tarjetas de localizaciones que tus usuarios ven al entrar.
                        Organiza el orden y la visibilidad de las zonas principales.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold shadow-lg hover-scale px-4 py-3"
                        data-bs-toggle="modal" data-bs-target="#createLocationModal">
                        <i class="bi bi-plus-lg me-2 text-primary"></i>Nueva Ubicación
                    </button>
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        @if ($locations->count() > 0)
            <div class="row g-4">
                @foreach ($locations as $location)
                    <div class="col-md-6 col-xl-4">
                        <div
                            class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-lift group">
                            {{-- Image Header --}}
                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                @if ($location->image)
                                    <img src="{{ $location->image }}" class="w-100 h-100 object-fit-cover"
                                        alt="{{ $location->name }}">
                                @else
                                    <div
                                        class="w-100 h-100 bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted">
                                        <i class="bi bi-image fs-1 opacity-25"></i>
                                    </div>
                                @endif

                                <div class="position-absolute top-0 end-0 p-3 z-2">
                                    <span
                                        class="badge rounded-pill px-3 py-2 fw-semibold shadow-sm
                                {{ $location->is_active ? 'bg-success text-white' : 'bg-secondary text-white-50' }} backdrop-blur">
                                        {{ $location->is_active ? 'Visible' : 'Oculto' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="fw-bold text-dark mb-0 text-truncate" title="{{ $location->name }}">
                                        {{ $location->name }}
                                    </h4>
                                    <span class="badge bg-light text-dark border ms-2">
                                        Orden: {{ $location->order }}
                                    </span>
                                </div>

                                <hr class="border-secondary border-opacity-10 my-0 mb-3 mt-auto">

                                {{-- Actions --}}
                                <div class="d-flex align-items-center justify-content-between">
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium"
                                        data-view-trigger data-location='@json($location)'>
                                        <i class="bi bi-eye me-1"></i> Ver
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button
                                            class="btn btn-light text-primary btn-icon rounded-circle hover-primary shadow-sm"
                                            data-edit-trigger data-location='@json($location)'
                                            data-action="{{ route('admin.home_locations.update', $location->id) }}"
                                            data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <button
                                            class="btn btn-light text-danger btn-icon rounded-circle hover-danger shadow-sm"
                                            data-delete-trigger
                                            data-action="{{ route('admin.home_locations.destroy', $location->id) }}"
                                            data-name="{{ $location->name }}" data-bs-toggle="tooltip" title="Eliminar">
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
                    <i class="bi bi-geo-alt" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold text-muted mb-2">Sin ubicaciones configuradas</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                    Aún no has creado ninguna ubicación para mostrar en la home.
                </p>
                <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold hover-scale" data-bs-toggle="modal"
                    data-bs-target="#createLocationModal">
                    <i class="bi bi-plus-lg me-2"></i> Crear Primera Ubicación
                </button>
            </div>
        @endif
    </div>

    {{-- MODALS --}}

    {{-- Create Modal --}}
    <div class="modal fade" id="createLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Nuevo Contenido</p>
                        <h5 class="modal-title fw-bold text-dark">Crear Ubicación</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.home_locations.store') }}" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Nombre</label>
                                <input type="text"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    name="name" id="create_name" required placeholder="Ej: Puerto del Rosario">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Orden</label>
                                <input type="number"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    name="order" value="0">
                            </div>

                            <div class="col-12">
                                <label class="form-label small text-muted">Imagen de Fondo (Se recortará a 836x665)</label>
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
                            <i class="bi bi-save me-2"></i>Guardar Ubicación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg form-glass">
                <div class="modal-header border-0 pb-0">
                    <div class="ps-2">
                        <p class="text-uppercase text-primary small fw-bold mb-1 tracking-wider">Edición</p>
                        <h5 class="modal-title fw-bold text-dark">Modificar Ubicación</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editLocationForm" method="POST" class="modal-body p-4 pt-2">
                    @csrf
                    @method('PUT')
                    <div class="p-3 bg-light bg-opacity-50 rounded-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Nombre</label>
                                <input type="text"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-uppercase text-muted">Orden</label>
                                <input type="number"
                                    class="form-control form-control-lg rounded-3 border-0 bg-white shadow-sm"
                                    id="edit_order" name="order">
                            </div>

                            <div class="col-12">
                                <label class="form-label small text-muted">Imagen (Se recortará a 836x665)</label>

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
    <div class="modal fade" id="viewLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-0 overflow-hidden rounded-4">
                    {{-- Mock --}}
                    <div class="position-relative p-4 text-center text-white d-flex flex-column align-items-center justify-content-center"
                        style="min-height: 300px; background-size: cover; background-position: center;" id="view_bg">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-40"
                            style="backdrop-filter: blur(2px);"></div>
                        <div class="position-relative z-1 w-100">
                            <h2 class="fw-bold mb-0 display-6" id="view_name">Nombre</h2>
                            <span id="view_status_badge"
                                class="badge rounded-pill bg-light text-dark mt-3 shadow-sm">Status</span>
                        </div>
                    </div>

                    <div class="p-4 bg-white">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Detalles</h6>
                        <div class="list-group list-group-flush rounded-3 border-0">
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted small">Orden de aparición</span>
                                <span id="view_order" class="fw-bold text-dark">-</span>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="text-muted small mb-1">URL de Imagen</div>
                                <code class="d-block bg-light p-2 rounded small text-wrap text-break"
                                    id="view_img_url">-</code>
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

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteLocationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger opacity-75">
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2">¿Estás seguro?</h5>
                    <p class="text-muted mb-4 small">
                        Estás a punto de eliminar la ubicación <br>
                        "<span id="delete_name_preview" class="fw-bold text-dark"></span>". <br>
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                            data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteLocationForm" method="POST">
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
                editForm: document.getElementById('editLocationForm'),
                editName: document.getElementById('edit_name'),
                editOrder: document.getElementById('edit_order'),
                editActive: document.getElementById('edit_is_active'),
                editCurrentPreview: document.getElementById('edit_current_preview'),
                editCurrentContainer: document.getElementById('edit_current_img_container'),

                viewName: document.getElementById('view_name'),
                viewOrder: document.getElementById('view_order'),
                viewBg: document.getElementById('view_bg'),
                viewStatus: document.getElementById('view_status_badge'),
                viewImgUrl: document.getElementById('view_img_url'),

                deleteForm: document.getElementById('deleteLocationForm'),
                deletePreview: document.getElementById('delete_name_preview'),
            };

            const LOCATIONIQ_KEY = 'pk.d52886ad23ebf6a01e455bb91b89bcc1';
            const LOCATIONIQ_TYPES = ['island', 'state', 'province', 'region', 'country', 'state_district',
                'county'];
            const LOCATIONIQ_TAG_PARAM = LOCATIONIQ_TYPES.map(type => `place:${type}`).join(',');
            const LOCATIONIQ_ALLOWED = new Set(LOCATIONIQ_TYPES);

            function formatTypeLabel(type) {
                if (!type) return '';
                const cleaned = type.replace(/_/g, ' ');
                return cleaned.charAt(0).toUpperCase() + cleaned.slice(1);
            }

            function initLocationIQAutocomplete(input) {
                if (!input) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'autocomplete-container';

                const resultsContainer = document.createElement('div');
                resultsContainer.className = 'autocomplete-results d-none';

                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                wrapper.appendChild(resultsContainer);

                let debounce = null;

                const hideResults = () => {
                    resultsContainer.classList.add('d-none');
                    resultsContainer.innerHTML = '';
                };

                input.addEventListener('input', function() {
                    const query = this.value.trim();

                    if (query.length < 3) {
                        hideResults();
                        return;
                    }

                    clearTimeout(debounce);
                    debounce = setTimeout(() => {
                        const endpoint =
                            `https://api.locationiq.com/v1/autocomplete?key=${LOCATIONIQ_KEY}&q=${encodeURIComponent(query)}&limit=6&tag=${LOCATIONIQ_TAG_PARAM}`;

                        fetch(endpoint)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Error en LocationIQ');
                                }
                                return response.json();
                            })
                            .then(data => {
                                resultsContainer.innerHTML = '';

                                const suggestions = Array.isArray(data) ? data.filter(item => !
                                    item.type ||
                                    LOCATIONIQ_ALLOWED.has(item.type)) : [];

                                if (!suggestions.length) {
                                    hideResults();
                                    return;
                                }

                                suggestions.forEach(item => {
                                    const div = document.createElement('div');
                                    div.className = 'autocomplete-item';

                                    const address = item.address || {};
                                    const primaryName = item.display_place || address
                                        .state ||
                                        address.province || address.region ||
                                        address.country ||
                                        (item.display_name ? item.display_name.split(
                                            ',')[0] : '') || query;

                                    const details = [];
                                    const typeLabel = formatTypeLabel(item.type);
                                    if (typeLabel) details.push(typeLabel);

                                    const province = address.province || address
                                        .state || address.region || '';
                                    if (province && province !== primaryName) {
                                        details.push(province);
                                    }

                                    if (address.country && address.country !==
                                        primaryName) {
                                        details.push(address.country);
                                    }

                                    div.innerHTML =
                                        `<strong>${primaryName}</strong>${details.length ? `<br><small class='text-muted'>${details.join(' · ')}</small>` : ''}`;

                                    div.addEventListener('click', () => {
                                        input.value = primaryName;
                                        hideResults();
                                    });

                                    resultsContainer.appendChild(div);
                                });

                                resultsContainer.classList.remove('d-none');
                            })
                            .catch(error => {
                                console.error('LocationIQ Error:', error);
                                hideResults();
                            });
                    }, 300);
                });

                document.addEventListener('click', function(event) {
                    if (!wrapper.contains(event.target)) {
                        hideResults();
                    }
                });
            }

            const viewModal = new bootstrap.Modal(document.getElementById('viewLocationModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editLocationModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteLocationModal'));

            initLocationIQAutocomplete(document.getElementById('create_name'));
            initLocationIQAutocomplete(els.editName);

            // --- CROPPER LOGIC ---
            let createCropper = null;
            let editCropper = null;

            function initCropper(imgElement, file, isCreate) {
                const url = URL.createObjectURL(file);
                imgElement.src = url;

                if (isCreate && createCropper) createCropper.destroy();
                if (!isCreate && editCropper) editCropper.destroy();

                const cropper = new Cropper(imgElement, {
                    aspectRatio: 836 / 665,
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

            // Intercept Forms
            const createForm = document.querySelector('form[action="{{ route('admin.home_locations.store') }}"]');
            const createCroppedInput = document.getElementById('create_cropped_image');

            createForm.addEventListener('submit', function(e) {
                if (createCropper) {
                    e.preventDefault();
                    const canvas = createCropper.getCroppedCanvas({
                        width: 836,
                        height: 665
                    });
                    createCroppedInput.value = canvas.toDataURL('image/jpeg', 0.85);
                    createForm.submit();
                }
            });

            const editCroppedInput = document.getElementById('edit_cropped_image');
            els.editForm.addEventListener('submit', function(e) {
                if (editCropper) {
                    e.preventDefault();
                    const canvas = editCropper.getCroppedCanvas({
                        width: 836,
                        height: 665
                    });
                    editCroppedInput.value = canvas.toDataURL('image/jpeg', 0.85);
                    els.editForm.submit();
                }
            });

            // Clean up
            document.getElementById('createLocationModal').addEventListener('hidden.bs.modal', function() {
                if (createCropper) {
                    createCropper.destroy();
                    createCropper = null;
                }
                createInput.value = '';
                createContainer.classList.add('d-none');
            });

            document.getElementById('editLocationModal').addEventListener('hidden.bs.modal', function() {
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
                    const location = JSON.parse(editBtn.dataset.location);
                    const action = editBtn.dataset.action;

                    els.editForm.action = action;
                    els.editName.value = location.name || '';
                    els.editOrder.value = location.order || 0;
                    els.editActive.checked = !!location.is_active;

                    // Handle Image Preview
                    if (location.image) {
                        els.editCurrentPreview.src = location.image;
                        els.editCurrentContainer.classList.remove('d-none');
                    } else {
                        els.editCurrentContainer.classList.add('d-none');
                    }

                    // Reset crop
                    editContainer.classList.add('d-none');

                    editModal.show();
                }

                // View
                const viewBtn = e.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    e.preventDefault();
                    const location = JSON.parse(viewBtn.dataset.location);

                    els.viewName.textContent = location.name || 'Sin Nombre';
                    els.viewOrder.textContent = location.order || '0';

                    // Background/Image
                    if (location.image) {
                        els.viewBg.style.backgroundImage = `url('${location.image}')`;
                        els.viewImgUrl.textContent = location.image;
                    } else {
                        els.viewBg.style.backgroundImage = 'none';
                        els.viewBg.style.backgroundColor = '#6c757d';
                        els.viewImgUrl.textContent = 'Ninguna';
                    }

                    // Status
                    if (location.is_active) {
                        els.viewStatus.textContent = 'Visible';
                        els.viewStatus.className =
                            'badge rounded-pill bg-success text-white mt-3 shadow-sm';
                    } else {
                        els.viewStatus.textContent = 'Oculto';
                        els.viewStatus.className =
                            'badge rounded-pill bg-secondary text-white mt-3 shadow-sm';
                    }

                    viewModal.show();
                }

                // Delete
                const deleteBtn = e.target.closest('[data-delete-trigger]');
                if (deleteBtn) {
                    e.preventDefault();
                    const action = deleteBtn.dataset.action;
                    const name = deleteBtn.dataset.name;

                    els.deleteForm.action = action;
                    els.deletePreview.textContent = name;

                    deleteModal.show();
                }
            });
        });
    </script>

    <style>
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

        /* LocationIQ Autocomplete Styles */
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1055;
            background: #fff;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            max-height: 240px;
            overflow-y: auto;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .autocomplete-item {
            padding: 0.65rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background 0.2s ease;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-item:hover {
            background: #f1f3f5;
        }

        /* Cropper Styles */
        .img-preview-container {
            background: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
        }

        .cropper-view-box,
        .cropper-face {
            border-radius: 4px;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
@endsection
