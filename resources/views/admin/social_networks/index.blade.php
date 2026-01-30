@extends('layouts.app')

@section('title', 'Redes Sociales')

@section('content')
    @php
        $iconOptions = [
            ['label' => 'Facebook', 'value' => 'bi bi-facebook', 'color' => '#1877F2'],
            ['label' => 'Instagram', 'value' => 'bi bi-instagram', 'color' => '#C13584'],
            ['label' => 'Twitter / X', 'value' => 'bi-twitter-x', 'color' => '#1DA1F2'],
            ['label' => 'LinkedIn', 'value' => 'bi bi-linkedin', 'color' => '#0A66C2'],
            ['label' => 'YouTube', 'value' => 'bi bi-youtube', 'color' => '#FF0000'],
            ['label' => 'TikTok', 'value' => 'bi bi-tiktok', 'color' => '#000000'],
            ['label' => 'WhatsApp', 'value' => 'bi bi-whatsapp', 'color' => '#25D366'],
            ['label' => 'Telegram', 'value' => 'bi bi-telegram', 'color' => '#0088cc'],
        ];
        $iconValues = collect($iconOptions)->pluck('value')->all();
        $createOldIcon = session('social_modal') === 'create' ? old('icon_class') : null;
        $createIconValue = $createOldIcon ?? ($iconOptions[0]['value'] ?? '');
        $createIconIsCustom = $createIconValue && !in_array($createIconValue, $iconValues, true);
        $editOldIcon = session('social_modal') === 'edit' ? old('icon_class') : null;
        $editIconIsCustom = $editOldIcon && !in_array($editOldIcon, $iconValues, true);
        $createIslandValue = session('social_modal') === 'create' ? (int) old('island_id', 0) : 0;
        $editIslandValue = session('social_modal') === 'edit' ? (int) old('island_id', 0) : null;
    @endphp
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1 fw-bold">Redes Sociales</h1>
                <p class="text-muted mb-0">Gestiona los iconos y enlaces que se muestran en la cabecera y el pie del portal.
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSocialModal">
                <i class="bi bi-plus-circle me-2"></i>Nueva red social
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Revisa la información ingresada.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($socialNetworks->count())
            <div class="card shadow-sm border-0 rounded-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Orden</th>
                                <th scope="col">Icono</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Isla</th>
                                <th scope="col">Enlace</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($socialNetworks as $network)
                                <tr>
                                    <td class="fw-semibold text-muted">{{ $network->order ?? 0 }}</td>
                                    <td>
                                        <i class="{{ $network->icon_class }} fs-4 text-primary"></i>
                                        <div class="small text-muted">{{ $network->icon_class }}</div>
                                    </td>
                                    <td class="fw-semibold">{{ $network->name }}</td>
                                    <td class="text-muted small">
                                        @if ($network->island_id > 0 && $network->island)
                                            {{ $network->island->name }}
                                        @else
                                            Todas las islas
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $network->url }}" target="_blank" rel="noopener"
                                            class="text-decoration-none">
                                            {{ $network->url }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($network->is_active)
                                            <span class="badge bg-success">Visible</span>
                                        @else
                                            <span class="badge bg-secondary">Oculta</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-secondary btn-sm me-2" type="button"
                                            data-bs-toggle="modal" data-bs-target="#editSocialModal"
                                            data-action="{{ route('admin.social_networks.update', $network) }}"
                                            data-name="{{ $network->name }}" data-icon-class="{{ $network->icon_class }}"
                                            data-url="{{ $network->url }}" data-order="{{ $network->order ?? 0 }}"
                                            data-island-id="{{ $network->island_id ?? 0 }}" data-id="{{ $network->id }}"
                                            data-active="{{ $network->is_active ? '1' : '0' }}">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>

                                        <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="modal"
                                            data-bs-target="#deleteSocialModal"
                                            data-action="{{ route('admin.social_networks.destroy', $network) }}"
                                            data-name="{{ $network->name }}">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="text-center py-5 border border-dashed rounded-3 bg-white">
                <i class="bi bi-share text-primary" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Aún no has añadido redes sociales.</h5>
                <p class="text-muted mb-4">Crea tus enlaces para mostrarlos automáticamente en la web pública.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSocialModal">
                    <i class="bi bi-plus-circle me-2"></i>Crear la primera red
                </button>
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createSocialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <div>
                        <h1 class="modal-title fs-5 mb-0">Nueva Red Social</h1>
                        <p class="text-muted mb-0">Define el nombre, icono y enlace que aparecerá en el portal.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('admin.social_networks.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_social_name" class="form-label fw-semibold">Nombre visible</label>
                            <input type="text" id="create_social_name" name="name" class="form-control"
                                value="{{ session('social_modal') === 'create' ? old('name') : '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icono</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i id="create_icon_preview" class="{{ $createIconValue ?: 'bi bi-star' }}"
                                        data-default-class="{{ $createIconValue ?: 'bi bi-star' }}"></i>
                                </span>
                                <select class="form-select icon-picker" data-preview-target="#create_icon_preview"
                                    data-target-input="#create_icon_value"
                                    data-custom-wrapper="#create_icon_custom_wrapper"
                                    data-custom-input="#create_icon_custom">
                                    @foreach ($iconOptions as $option)
                                        <option value="{{ $option['value'] }}" data-color="{{ $option['color'] }}"
                                            {{ !$createIconIsCustom && $createIconValue === $option['value'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                    <option value="__custom" {{ $createIconIsCustom ? 'selected' : '' }}>Otro icono de
                                        Bootstrap...</option>
                                </select>
                            </div>
                            <input type="hidden" name="icon_class" id="create_icon_value"
                                value="{{ $createIconValue }}">
                            <div class="mt-2 {{ $createIconIsCustom ? '' : 'd-none' }}" id="create_icon_custom_wrapper">
                                <input type="text" class="form-control icon-custom-input" id="create_icon_custom"
                                    placeholder="Ingresa la clase del icono"
                                    value="{{ $createIconIsCustom ? $createIconValue : '' }}">
                                <div class="form-text">Ejemplo: <code>bi bi-github</code></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="create_social_url" class="form-label fw-semibold">Enlace</label>
                            <input type="url" id="create_social_url" name="url" class="form-control"
                                value="{{ session('social_modal') === 'create' ? old('url') : '' }}" required
                                placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label for="create_social_island" class="form-label fw-semibold">Isla</label>
                            <select class="form-select" id="create_social_island" name="island_id">
                                <option value="0" @selected($createIslandValue === 0)>Todas las islas</option>
                                @foreach ($islands as $island)
                                    <option value="{{ $island->id }}" @selected($createIslandValue === $island->id)>
                                        {{ $island->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label for="create_social_order" class="form-label fw-semibold">Orden</label>
                                <input type="number" id="create_social_order" name="order" class="form-control"
                                    min="0"
                                    value="{{ session('social_modal') === 'create' ? old('order', 0) : 0 }}">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="create_social_active"
                                        name="is_active" value="1"
                                        {{ session('social_modal') === 'create' ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                                    <label class="form-check-label fw-semibold" for="create_social_active">Visible en el
                                        portal</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editSocialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <div>
                        <h1 class="modal-title fs-5 mb-0">Editar Red Social</h1>
                        <p class="text-muted mb-0">Actualiza la información existente.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form method="POST" action="#" id="editSocialForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_social_name" class="form-label fw-semibold">Nombre visible</label>
                            <input type="text" id="edit_social_name" name="name" class="form-control"
                                value="{{ session('social_modal') === 'edit' ? old('name') : '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icono</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i id="edit_icon_preview"
                                        class="{{ session('social_modal') === 'edit' ? $editOldIcon ?? ($iconOptions[0]['value'] ?? 'bi bi-star') : $iconOptions[0]['value'] ?? 'bi bi-star' }}"
                                        data-default-class="{{ $iconOptions[0]['value'] ?? 'bi bi-star' }}"></i>
                                </span>
                                <select class="form-select icon-picker" id="edit_icon_select"
                                    data-preview-target="#edit_icon_preview" data-target-input="#edit_icon_value"
                                    data-custom-wrapper="#edit_icon_custom_wrapper" data-custom-input="#edit_icon_custom">
                                    @foreach ($iconOptions as $option)
                                        <option value="{{ $option['value'] }}" data-color="{{ $option['color'] }}"
                                            {{ session('social_modal') === 'edit' && !$editIconIsCustom && $editOldIcon === $option['value'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                    <option value="__custom" {{ $editIconIsCustom ? 'selected' : '' }}>Otro icono de
                                        Bootstrap...</option>
                                </select>
                            </div>
                            <input type="hidden" name="icon_class" id="edit_icon_value"
                                value="{{ session('social_modal') === 'edit' ? $editOldIcon ?? '' : '' }}">
                            <div class="mt-2 {{ $editIconIsCustom ? '' : 'd-none' }}" id="edit_icon_custom_wrapper">
                                <input type="text" class="form-control icon-custom-input" id="edit_icon_custom"
                                    placeholder="Ingresa la clase del icono"
                                    value="{{ $editIconIsCustom ? $editOldIcon : '' }}">
                                <div class="form-text">Ejemplo: <code>bi bi-github</code></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_social_url" class="form-label fw-semibold">Enlace</label>
                            <input type="url" id="edit_social_url" name="url" class="form-control"
                                value="{{ session('social_modal') === 'edit' ? old('url') : '' }}" required
                                placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label for="edit_social_island" class="form-label fw-semibold">Isla</label>
                            <select class="form-select" id="edit_social_island" name="island_id">
                                <option value="0" @selected(session('social_modal') === 'edit' && $editIslandValue === 0)>
                                    Todas las islas
                                </option>
                                @foreach ($islands as $island)
                                    <option value="{{ $island->id }}" @selected(session('social_modal') === 'edit' && $editIslandValue === $island->id)>
                                        {{ $island->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label for="edit_social_order" class="form-label fw-semibold">Orden</label>
                                <input type="number" id="edit_social_order" name="order" class="form-control"
                                    min="0"
                                    value="{{ session('social_modal') === 'edit' ? old('order', 0) : '' }}">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="edit_social_active"
                                        name="is_active" value="1"
                                        {{ session('social_modal') === 'edit' ? (old('is_active') ? 'checked' : '') : '' }}>
                                    <label class="form-check-label fw-semibold" for="edit_social_active">Visible en el
                                        portal</label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="social_network_id" id="edit_social_network_id"
                            value="{{ session('social_modal') === 'edit' ? old('social_network_id') : '' }}">
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteSocialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5">Eliminar Red Social</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form method="POST" action="#" id="deleteSocialForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-0">¿Seguro que quieres eliminar <strong class="delete-social-name"></strong>? Esta
                            acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $editId = session('social_modal_id');
        $modalState = [
            'open' => session('social_modal'),
            'action' => $editId ? route('admin.social_networks.update', $editId) : null,
            'id' => $editId,
            'values' =>
                session('social_modal') === 'edit'
                    ? [
                        'name' => old('name'),
                        'icon_class' => old('icon_class'),
                        'url' => old('url'),
                        'order' => old('order'),
                        'is_active' => old('is_active') ? true : false,
                        'island_id' => (int) old('island_id', 0),
                    ]
                    : null,
        ];
    @endphp
    <script>
        window.socialNetworksModalState = @json($modalState);

        document.addEventListener('DOMContentLoaded', () => {
            const pickerMap = new WeakMap();

            const setupIconPicker = (select) => {
                if (!select) {
                    return null;
                }
                if (pickerMap.has(select)) {
                    return pickerMap.get(select);
                }

                const hiddenInput = document.querySelector(select.dataset.targetInput);
                const preview = document.querySelector(select.dataset.previewTarget);
                const customWrapper = document.querySelector(select.dataset.customWrapper);
                const customInput = document.querySelector(select.dataset.customInput);
                const defaultIconClass =
                    preview?.getAttribute('data-default-class') || 'bi bi-question-circle';

                const hasOption = (value) => {
                    return value && Array.from(select.options).some(option => option.value === value);
                };

                const applyValue = (value) => {
                    const iconValue = value || '';
                    if (hiddenInput) {
                        hiddenInput.value = iconValue;
                    }

                    if (preview) {
                        preview.className = iconValue || defaultIconClass;
                    }

                    if (iconValue && hasOption(iconValue)) {
                        select.value = iconValue;
                        if (customWrapper) {
                            customWrapper.classList.add('d-none');
                        }
                        if (customInput) {
                            customInput.value = '';
                        }
                    } else {
                        select.value = '__custom';
                        if (customWrapper) {
                            customWrapper.classList.remove('d-none');
                        }
                        if (customInput) {
                            customInput.value = iconValue;
                        }
                    }
                };

                select.addEventListener('change', () => {
                    if (select.value === '__custom') {
                        if (customWrapper) {
                            customWrapper.classList.remove('d-none');
                        }
                        if (customInput) {
                            customInput.focus();
                            applyValue(customInput.value.trim());
                        } else {
                            applyValue('');
                        }
                    } else {
                        if (customWrapper) {
                            customWrapper.classList.add('d-none');
                        }
                        applyValue(select.value);
                    }
                });

                customInput?.addEventListener('input', () => {
                    if (select.value === '__custom') {
                        applyValue(customInput.value.trim());
                    }
                });

                const api = {
                    setValue: (value) => applyValue(value),
                    refresh: () => {
                        if (hiddenInput?.value) {
                            applyValue(hiddenInput.value);
                        } else if (select.value !== '__custom') {
                            applyValue(select.value);
                        } else {
                            applyValue('');
                        }
                    },
                };

                api.refresh();
                pickerMap.set(select, api);
                return api;
            };

            const createModalEl = document.getElementById('createSocialModal');
            const editModalEl = document.getElementById('editSocialModal');
            const deleteModalEl = document.getElementById('deleteSocialModal');

            document.querySelectorAll('.icon-picker').forEach(select => setupIconPicker(select));

            if (editModalEl) {
                editModalEl.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const form = editModalEl.querySelector('form');
                    form.action = button.getAttribute('data-action');

                    editModalEl.querySelector('#edit_social_name').value = button.getAttribute(
                        'data-name') || '';
                    editModalEl.querySelector('#edit_social_url').value = button.getAttribute('data-url') ||
                        '';
                    editModalEl.querySelector('#edit_social_order').value = button.getAttribute(
                        'data-order') || 0;
                    const islandSelect = editModalEl.querySelector('#edit_social_island');
                    if (islandSelect) {
                        islandSelect.value = button.getAttribute('data-island-id') || 0;
                    }
                    editModalEl.querySelector('#edit_social_active').checked = button.getAttribute(
                        'data-active') === '1';
                    editModalEl.querySelector('#edit_social_network_id').value = button.getAttribute(
                        'data-id') || '';

                    const picker = setupIconPicker(editModalEl.querySelector('#edit_icon_select'));
                    picker?.setValue(button.getAttribute('data-icon-class') || '');
                });
            }

            if (deleteModalEl) {
                deleteModalEl.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const form = deleteModalEl.querySelector('form');
                    form.action = button.getAttribute('data-action');
                    deleteModalEl.querySelector('.delete-social-name').textContent =
                        button.getAttribute('data-name') || '';
                });
            }

            const state = window.socialNetworksModalState || {};

            if (state.open === 'create' && createModalEl) {
                const modal = new bootstrap.Modal(createModalEl);
                modal.show();

                const picker = setupIconPicker(createModalEl.querySelector('.icon-picker'));
                picker?.setValue(document.querySelector('#create_icon_value')?.value || '');
            }

            if (state.open === 'edit' && editModalEl) {
                const form = editModalEl.querySelector('form');
                if (state.action) {
                    form.action = state.action;
                }
                if (state.values) {
                    editModalEl.querySelector('#edit_social_name').value = state.values.name || '';
                    editModalEl.querySelector('#edit_social_url').value = state.values.url || '';
                    editModalEl.querySelector('#edit_social_order').value = state.values.order || 0;
                    const islandSelect = editModalEl.querySelector('#edit_social_island');
                    if (islandSelect) {
                        islandSelect.value = state.values.island_id ?? 0;
                    }
                    editModalEl.querySelector('#edit_social_active').checked = !!state.values.is_active;
                }
                if (state.id) {
                    editModalEl.querySelector('#edit_social_network_id').value = state.id;
                }

                const picker = setupIconPicker(editModalEl.querySelector('#edit_icon_select'));
                picker?.setValue(state.values?.icon_class || '');

                const modal = new bootstrap.Modal(editModalEl);
                modal.show();
            }
        });
    </script>
@endsection
