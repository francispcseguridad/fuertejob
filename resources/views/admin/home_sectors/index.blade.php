@extends('layouts.app')

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <style>
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
@endsection

@section('title', 'Gestión de Sectores Destacados')

@section('content')
    <div class="mb-4">
        <div class="rounded-4 bg-secondary bg-gradient text-white p-4 p-md-5 shadow-sm position-relative overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="bi bi-grid-3x3" style="font-size: 6rem;"></i>
            </div>
            <div class="row align-items-center position-relative z-1">
                <div class="col-lg-8">
                    <p class="text-uppercase small text-white-50 mb-2 fw-semibold">Contenido de Portada</p>
                    <h2 class="fw-bold mb-3">Sectores destacados en la home</h2>
                    <p class="mb-0 text-white-75">Controla las tarjetas que muestran los principales sectores laborales en
                        la
                        portada. Mantén un diseño fresco y actualizado desde aquí.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold hover-scale"
                        data-bs-toggle="modal" data-bs-target="#createSectorModal">
                        <i class="bi bi-plus-circle me-2 text-secondary"></i>
                        Nuevo sector
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if ($sectors->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sector</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sectors as $sector)
                                @php
                                    $sectorPayload = [
                                        'id' => $sector->id,
                                        'name' => $sector->name,
                                        'image' => $sector->image,
                                        'url' => $sector->url,
                                        'order' => $sector->order,
                                        'is_active' => (bool) $sector->is_active,
                                        'sector_id' => $sector->sector_reference_id,
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center"
                                                style="width: 56px; height: 56px;">
                                                @if ($sector->image)
                                                    <img src="{{ $sector->image }}" alt="{{ $sector->name }}"
                                                        class="rounded-circle"
                                                        style="width: 56px; height: 56px; object-fit: cover;">
                                                @else
                                                    <i class="bi bi-briefcase text-muted fs-4"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $sector->name }}</h6>
                                                <small
                                                    class="text-muted d-block">{{ $sector->url ?: 'Sin URL definida' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary fw-semibold">
                                            #{{ $sector->order }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill px-3 py-2 {{ $sector->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $sector->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold"
                                                data-view-trigger data-sector='@json($sectorPayload)'>
                                                <i class="bi bi-eye me-1"></i> Ver
                                            </button>
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold"
                                                data-edit-trigger data-sector='@json($sectorPayload)'
                                                data-action="{{ route('admin.home_sectors.update', $sector->id) }}">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </button>
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold"
                                                data-delete-trigger data-name="{{ $sector->name }}"
                                                data-action="{{ route('admin.home_sectors.destroy', $sector->id) }}">
                                                <i class="bi bi-trash me-1"></i> Borrar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3 text-muted opacity-50">
                        <i class="bi bi-briefcase fs-1"></i>
                    </div>
                    <h5 class="text-muted mb-1">No hay sectores creados</h5>
                    <p class="text-muted small mb-3">Crea sectores destacados para la página principal.</p>
                    <button class="btn btn-primary rounded-pill px-4 fw-semibold hover-scale" data-bs-toggle="modal"
                        data-bs-target="#createSectorModal">
                        Crear sector
                    </button>
                </div>
            @endif
        </div>
    </div>
@endsection

{{-- Modals --}}
<div class="modal fade" id="createSectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="text-uppercase small text-muted fw-semibold mb-1">Nuevo Sector</p>
                    <h5 class="modal-title fw-bold text-dark">Crear sector destacado</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.home_sectors.store') }}" method="POST" class="modal-body p-4 pt-2"
                enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Sector</label>
                        <div class="sector-autocomplete-wrapper">
                            <input type="text" class="form-control rounded-3 shadow-sm sector-autocomplete-input"
                                id="create_sector_search" autocomplete="off"
                                placeholder="Escribe para buscar el sector">
                            <div class="sector-autocomplete-results shadow" id="create_sector_results"></div>
                        </div>
                        <input type="hidden" name="name" id="create_sector_name" value="{{ old('name') }}">
                        <input type="hidden" name="sector_id" id="create_sector_id" value="{{ old('sector_id') }}">
                        <input type="hidden" name="url" id="create_sector_url" value="{{ old('url') }}">
                        <div class="mt-2">
                            <small class="text-muted d-block">Seleccionado</small>
                            <span class="badge bg-light text-muted" id="create_sector_label_badge">Sin sector
                                seleccionado</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted d-block">URL generada</small>
                            <code class="small d-block text-break" id="create_sector_url_preview">La URL se generará
                                automáticamente.</code>
                        </div>
                        <button type="button" class="btn btn-link p-0 small text-danger mt-1"
                            id="create_sector_clear" disabled>Limpiar selección</button>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Orden</label>
                        <input type="number" class="form-control rounded-3 shadow-sm" name="order" min="0"
                            value="{{ old('order', ($sectors->max('order') ?? 0) + 1) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Imagen destacada
                            (836x665)</label>
                        <input type="file" class="form-control" id="create_image_input" accept="image/*">
                        <input type="hidden" name="cropped_image" id="create_cropped_image">
                        <div class="mt-3 d-none" id="create_crop_container">
                            <div class="img-preview-container overflow-hidden">
                                <img id="create_image_preview" style="max-width: 100%; display: block;">
                            </div>
                            <div class="text-muted small mt-2 text-center">Ajusta el recorte (836x665) y se guardará
                                automáticamente via Base64.</div>
                        </div>
                        <small class="text-muted d-block mt-2">La imagen se recortará automáticamente al tamaño
                            requerido.</small>
                    </div>
                    <div class="col-12">
                        <div
                            class="form-check form-switch bg-light rounded-3 px-3 py-2 d-flex align-items-center justify-content-between shadow-sm">
                            <label class="form-check-label fw-semibold" for="create_is_active">Visible</label>
                            <input class="form-check-input" type="checkbox" role="switch" id="create_is_active"
                                name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-0 pt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-2"></i>Guardar sector
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editSectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="text-uppercase small text-muted fw-semibold mb-1">Editar</p>
                    <h5 class="modal-title fw-bold text-dark">Actualizar sector</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSectorForm" method="POST" class="modal-body p-4 pt-2" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Sector</label>
                        <div class="sector-autocomplete-wrapper">
                            <input type="text" class="form-control rounded-3 shadow-sm sector-autocomplete-input"
                                id="edit_sector_search" autocomplete="off" placeholder="Busca el sector" />
                            <div class="sector-autocomplete-results shadow" id="edit_sector_results"></div>
                        </div>
                        <input type="hidden" name="name" id="edit_sector_name">
                        <input type="hidden" name="sector_id" id="edit_sector_id">
                        <input type="hidden" name="url" id="edit_sector_url">
                        <div class="mt-2">
                            <small class="text-muted d-block">Seleccionado</small>
                            <span class="badge bg-light text-muted" id="edit_sector_label_badge">Sin sector
                                seleccionado</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted d-block">URL generada</small>
                            <code class="small d-block text-break" id="edit_sector_url_preview">La URL se generará
                                automáticamente.</code>
                        </div>
                        <button type="button" class="btn btn-link p-0 small text-danger mt-1" id="edit_sector_clear"
                            disabled>Limpiar selección</button>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Orden</label>
                        <input type="number" class="form-control rounded-3 shadow-sm" id="edit_order"
                            name="order" min="0" required>
                    </div>
                    <div class="col-12">
                        <div
                            class="form-check form-switch bg-light rounded-3 px-3 py-2 d-flex align-items-center justify-content-between shadow-sm">
                            <label class="form-check-label fw-semibold" for="edit_is_active">Visible</label>
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_is_active"
                                name="is_active" value="1">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Imagen actual</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="" alt="Imagen del sector" id="edit_current_image_preview"
                                class="img-fluid rounded shadow-sm d-none" style="max-height: 90px;">
                            <span class="text-muted small" id="edit_no_image_copy">Sin imagen cargada.</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted text-uppercase small fw-semibold">Actualizar imagen
                            (836x665)</label>
                        <input type="file" class="form-control" id="edit_image_input" accept="image/*">
                        <input type="hidden" name="cropped_image" id="edit_cropped_image">
                        <div class="mt-3 d-none" id="edit_crop_container">
                            <div class="img-preview-container overflow-hidden">
                                <img id="edit_image_preview" style="max-width: 100%; display: block;">
                            </div>
                            <div class="text-muted small mt-2 text-center">Ajusta el recorte (836x665) y se guardará
                                automáticamente via Base64.</div>
                        </div>
                        <small class="text-muted d-block mt-2">Deja este campo vacío para conservar la imagen
                            actual.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 px-0 pt-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-check2 me-2"></i>Actualizar sector
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewSectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="text-uppercase small text-muted fw-semibold mb-1">Detalle</p>
                    <h5 class="modal-title fw-bold">Vista rápida del sector</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mx-auto mb-3 sector-avatar">
                        <img id="view_image" class="img-fluid rounded-circle shadow-sm d-none" alt="Vista sector">
                        <div id="view_image_placeholder"
                            class="rounded-circle bg-light border d-flex align-items-center justify-content-center">
                            <i class="bi bi-briefcase text-muted fs-2"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1" id="view_name">Sector</h5>
                    <span class="badge rounded-pill" id="view_status_badge">Activo</span>
                </div>
                <div class="list-group list-group-flush rounded-3 border">
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Orden</span>
                        <span class="fw-semibold" id="view_order">0</span>
                    </div>
                    <div class="list-group-item">
                        <div class="text-muted small mb-1">URL destino</div>
                        <a id="view_url" href="#" target="_blank" rel="noopener"
                            class="text-decoration-none small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-link-45deg"></i> <span id="view_url_text"></span>
                        </a>
                    </div>
                    <div class="list-group-item">
                        <div class="text-muted small mb-1">Imagen</div>
                        <code class="small text-break d-block" id="view_image_url">-</code>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle-fill fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Eliminar sector?</h5>
                <p class="text-muted small mb-4">
                    Esta acción eliminará el sector <span class="fw-semibold" id="delete_sector_name"></span>. No se
                    puede deshacer.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteSectorForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                            <i class="bi bi-trash me-2"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        function setupSectorSelector(config) {
            const {
                input,
                results,
                hiddenName,
                hiddenId,
                hiddenUrl,
                labelEl,
                urlEl,
                clearBtn,
                wrapper,
                searchEndpoint,
                urlTemplate,
            } = config;

            const fallback = {
                hydrate() {},
                setSelection() {},
                reset() {},
            };

            if (!input || !results || !hiddenName || !hiddenId || !hiddenUrl) {
                return fallback;
            }

            const defaultLabel = 'Sin sector seleccionado';
            const defaultUrlCopy = 'La URL se generará automáticamente.';
            const root = wrapper || input.parentElement;
            const MIN_CHARS = 2;
            let debounceId = null;
            let controller = null;

            function updatePreview(label = null, url = null) {
                if (labelEl) {
                    labelEl.textContent = label || defaultLabel;
                }
                if (urlEl) {
                    urlEl.textContent = url || defaultUrlCopy;
                }
            }

            function buildSectorUrl(id) {
                if (!urlTemplate || !id) {
                    return '';
                }
                return urlTemplate.replace('__ID__', encodeURIComponent(id));
            }

            function hideResults() {
                results.classList.remove('show');
            }

            function resetSelection({
                preserveInput = false
            } = {}) {
                if (!preserveInput) {
                    input.value = '';
                }
                hiddenName.value = '';
                hiddenId.value = '';
                hiddenUrl.value = '';
                updatePreview();
                if (clearBtn) {
                    clearBtn.disabled = true;
                }
                hideResults();
            }

            function applySelection(selection) {
                if (!selection || !selection.id || !selection.label) {
                    resetSelection();
                    return;
                }

                hiddenName.value = selection.label;
                hiddenId.value = selection.id;
                const generatedUrl = selection.url || buildSectorUrl(selection.id);
                hiddenUrl.value = generatedUrl;
                input.value = selection.label;
                updatePreview(selection.label, generatedUrl);

                if (clearBtn) {
                    clearBtn.disabled = false;
                }
                hideResults();
            }

            function renderResults(items) {
                results.innerHTML = '';
                if (!items || !items.length) {
                    hideResults();
                    return;
                }

                items.forEach((item) => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'sector-autocomplete-option';
                    option.dataset.sectorOption = 'true';
                    option.dataset.id = item.id;
                    option.dataset.label = item.label;
                    option.textContent = item.label;
                    results.appendChild(option);
                });

                results.classList.add('show');
            }

            function performSearch(term) {
                if (!searchEndpoint) {
                    return;
                }

                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();
                fetch(`${searchEndpoint}?term=${encodeURIComponent(term)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Error al buscar sectores');
                        }
                        return response.json();
                    })
                    .then(renderResults)
                    .catch((error) => {
                        if (error.name !== 'AbortError') {
                            hideResults();
                        }
                    });
            }

            input.addEventListener('input', (event) => {
                const term = event.target.value.trim();

                resetSelection({
                    preserveInput: true,
                });

                if (term.length < MIN_CHARS) {
                    hideResults();
                    return;
                }

                if (debounceId) {
                    clearTimeout(debounceId);
                }

                debounceId = setTimeout(() => performSearch(term), 250);
            });

            results.addEventListener('click', (event) => {
                const option = event.target.closest('[data-sector-option]');
                if (!option) {
                    return;
                }
                applySelection({
                    id: option.dataset.id,
                    label: option.dataset.label,
                });
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', () => resetSelection());
            }

            document.addEventListener('click', (event) => {
                if (!root) {
                    return;
                }
                if (!root.contains(event.target) && !results.contains(event.target)) {
                    hideResults();
                }
            });

            function hydrate() {
                if (hiddenId.value && hiddenName.value) {
                    applySelection({
                        id: hiddenId.value,
                        label: hiddenName.value,
                        url: hiddenUrl.value,
                    });
                } else {
                    resetSelection();
                }
            }

            return {
                hydrate,
                setSelection: applySelection,
                reset: () => resetSelection(),
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sectorUrlTemplate =
                'https://www.fuertejob.com/empleos?search=&province=&island=&sectors%5B%5D=__ID__&modality=&contract_type=';
            const sectorSearchEndpoint = "{{ route('api.sectores.search') }}";

            const els = {
                createForm: document.querySelector('#createSectorModal form'),
                editForm: document.getElementById('editSectorForm'),
                editOrder: document.getElementById('edit_order'),
                editActive: document.getElementById('edit_is_active'),
                editCurrentImage: document.getElementById('edit_current_image_preview'),
                editNoImageCopy: document.getElementById('edit_no_image_copy'),
                viewImage: document.getElementById('view_image'),
                viewImagePlaceholder: document.getElementById('view_image_placeholder'),
                viewName: document.getElementById('view_name'),
                viewStatusBadge: document.getElementById('view_status_badge'),
                viewOrder: document.getElementById('view_order'),
                viewUrl: document.getElementById('view_url'),
                viewUrlText: document.getElementById('view_url_text'),
                viewImageUrl: document.getElementById('view_image_url'),
                deleteForm: document.getElementById('deleteSectorForm'),
                deleteName: document.getElementById('delete_sector_name'),
            };

            const createSearchInput = document.getElementById('create_sector_search');
            const editSearchInput = document.getElementById('edit_sector_search');

            const selectors = {
                create: setupSectorSelector({
                    input: createSearchInput,
                    results: document.getElementById('create_sector_results'),
                    hiddenName: document.getElementById('create_sector_name'),
                    hiddenId: document.getElementById('create_sector_id'),
                    hiddenUrl: document.getElementById('create_sector_url'),
                    labelEl: document.getElementById('create_sector_label_badge'),
                    urlEl: document.getElementById('create_sector_url_preview'),
                    clearBtn: document.getElementById('create_sector_clear'),
                    wrapper: createSearchInput ? createSearchInput.closest(
                        '.sector-autocomplete-wrapper') : null,
                    searchEndpoint: sectorSearchEndpoint,
                    urlTemplate: sectorUrlTemplate,
                }),
                edit: setupSectorSelector({
                    input: editSearchInput,
                    results: document.getElementById('edit_sector_results'),
                    hiddenName: document.getElementById('edit_sector_name'),
                    hiddenId: document.getElementById('edit_sector_id'),
                    hiddenUrl: document.getElementById('edit_sector_url'),
                    labelEl: document.getElementById('edit_sector_label_badge'),
                    urlEl: document.getElementById('edit_sector_url_preview'),
                    clearBtn: document.getElementById('edit_sector_clear'),
                    wrapper: editSearchInput ? editSearchInput.closest('.sector-autocomplete-wrapper') :
                        null,
                    searchEndpoint: sectorSearchEndpoint,
                    urlTemplate: sectorUrlTemplate,
                }),
            };

            selectors.create.hydrate();
            selectors.create.hydrate();
            selectors.edit.hydrate();

            const modals = {
                edit: new bootstrap.Modal(document.getElementById('editSectorModal')),
                view: new bootstrap.Modal(document.getElementById('viewSectorModal')),
                delete: new bootstrap.Modal(document.getElementById('deleteSectorModal')),
            };

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

            if (createInput) {
                createInput.addEventListener('change', function(e) {
                    const files = e.target.files;
                    if (files && files.length > 0) {
                        createContainer.classList.remove('d-none');
                        initCropper(createPreview, files[0], true);
                    }
                });
            }

            // Handle Edit Input
            const editInput = document.getElementById('edit_image_input');
            const editPreview = document.getElementById('edit_image_preview');
            const editContainer = document.getElementById('edit_crop_container');

            if (editInput) {
                editInput.addEventListener('change', function(e) {
                    const files = e.target.files;
                    if (files && files.length > 0) {
                        editContainer.classList.remove('d-none');
                        initCropper(editPreview, files[0], false);
                    }
                });
            }

            // Intercept Create Form
            const createForm = document.querySelector('#createSectorModal form');
            const createCroppedInput = document.getElementById('create_cropped_image');

            if (createForm) {
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
            }

            // Intercept Edit Form
            const editForm = document.getElementById('editSectorForm');
            const editCroppedInput = document.getElementById('edit_cropped_image');

            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    if (editCropper) {
                        e.preventDefault();
                        const canvas = editCropper.getCroppedCanvas({
                            width: 836,
                            height: 665
                        });
                        editCroppedInput.value = canvas.toDataURL('image/jpeg', 0.85);
                        editForm.submit();
                    }
                });
            }

            // Clean up on modal close
            const createModalEl = document.getElementById('createSectorModal');
            if (createModalEl) {
                createModalEl.addEventListener('hidden.bs.modal', function() {
                    if (createCropper) {
                        createCropper.destroy();
                        createCropper = null;
                    }
                    if (createInput) createInput.value = '';
                    if (createContainer) createContainer.classList.add('d-none');
                    if (els.createForm) els.createForm.reset();
                    selectors.create.reset();
                });
            }

            const editModalEl = document.getElementById('editSectorModal');
            if (editModalEl) {
                editModalEl.addEventListener('hidden.bs.modal', function() {
                    if (editCropper) {
                        editCropper.destroy();
                        editCropper = null;
                    }
                    if (editInput) editInput.value = '';
                    if (editContainer) editContainer.classList.add('d-none');
                    selectors.edit.reset();
                    if (els.editCurrentImage) {
                        els.editCurrentImage.src = '';
                        els.editCurrentImage.classList.add('d-none');
                    }
                    if (els.editNoImageCopy) {
                        els.editNoImageCopy.classList.remove('d-none');
                    }
                });
            }

            document.body.addEventListener('click', function(event) {
                const editBtn = event.target.closest('[data-edit-trigger]');
                if (editBtn) {
                    event.preventDefault();
                    const sector = JSON.parse(editBtn.dataset.sector);
                    if (els.editForm) {
                        els.editForm.action = editBtn.dataset.action;
                    }

                    selectors.edit.setSelection({
                        id: sector.sector_id,
                        label: sector.name,
                        url: sector.url,
                    });
                    els.editOrder.value = sector.order ?? 0;
                    els.editActive.checked = !!sector.is_active;

                    if (sector.image) {
                        els.editCurrentImage.src = sector.image;
                        els.editCurrentImage.classList.remove('d-none');
                        els.editNoImageCopy.classList.add('d-none');
                    } else {
                        els.editCurrentImage.classList.add('d-none');
                        els.editNoImageCopy.classList.remove('d-none');
                    }

                    modals.edit.show();
                    return;
                }

                const viewBtn = event.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    event.preventDefault();
                    const sector = JSON.parse(viewBtn.dataset.sector);

                    els.viewName.textContent = sector.name || 'Sin nombre';
                    els.viewOrder.textContent = sector.order ?? '0';

                    if (sector.url) {
                        els.viewUrl.href = sector.url;
                        els.viewUrlText.textContent = sector.url;
                        els.viewUrl.classList.remove('disabled', 'text-muted');
                    } else {
                        els.viewUrl.removeAttribute('href');
                        els.viewUrlText.textContent = 'Sin URL definida';
                        els.viewUrl.classList.add('disabled', 'text-muted');
                    }

                    if (sector.image) {
                        els.viewImage.src = sector.image;
                        els.viewImage.classList.remove('d-none');
                        els.viewImagePlaceholder.classList.add('d-none');
                        els.viewImageUrl.textContent = sector.image;
                    } else {
                        els.viewImage.src = '';
                        els.viewImage.classList.add('d-none');
                        els.viewImagePlaceholder.classList.remove('d-none');
                        els.viewImageUrl.textContent = 'Sin imagen';
                    }

                    if (sector.is_active) {
                        els.viewStatusBadge.textContent = 'Activo';
                        els.viewStatusBadge.className = 'badge rounded-pill bg-success-subtle text-success';
                    } else {
                        els.viewStatusBadge.textContent = 'Inactivo';
                        els.viewStatusBadge.className =
                            'badge rounded-pill bg-secondary-subtle text-secondary';
                    }

                    modals.view.show();
                    return;
                }

                const deleteBtn = event.target.closest('[data-delete-trigger]');
                if (deleteBtn) {
                    event.preventDefault();
                    if (els.deleteForm) {
                        els.deleteForm.action = deleteBtn.dataset.action;
                    }
                    els.deleteName.textContent = deleteBtn.dataset.name || '';

                    modals.delete.show();
                }
            });
        });
    </script>

    <style>
        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .sector-avatar {
            width: 90px;
            height: 90px;
            position: relative;
        }

        .sector-avatar img,
        .sector-avatar div {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sector-autocomplete-wrapper {
            position: relative;
        }

        .sector-autocomplete-results {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.08);
            max-height: 240px;
            overflow-y: auto;
            display: none;
            z-index: 15;
        }

        .sector-autocomplete-results.show {
            display: block;
        }

        .sector-autocomplete-option {
            width: 100%;
            text-align: left;
            background: transparent;
            border: 0;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
        }

        .sector-autocomplete-option:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection
