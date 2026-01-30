@extends('layouts.app')

@section('title', 'Gestión de Menús')

@php
    $totalMenus = 0;
    $menus->each(function ($menu) use (&$totalMenus) {
        $totalMenus++;
        $totalMenus += $menu->children->count();
    });
@endphp

@section('content')
    <div class="mb-4" style="padding: 20px;">
        <div class="rounded-4 bg-primary bg-gradient text-white p-4 p-md-5 shadow-sm position-relative overflow-hidden">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="text-uppercase small text-white-50 mb-2">Navegación del portal</p>
                    <h2 class="fw-semibold mb-3">Diseña la experiencia de quienes visitan FuerteJob</h2>
                    <p class="mb-0 text-white-75">Optimiza los menús principales y sus submenús desde esta vista.
                        Visualiza, edita y organiza la jerarquía sin abandonar la página.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="card bg-white text-dark border-0 rounded-4 shadow">
                        <div class="card-body">
                            <p class="text-uppercase text-muted small mb-1">Elementos Totales</p>
                            <h3 class="display-6 fw-bold mb-0">{{ $totalMenus }}</h3>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light btn-lg rounded-pill mt-3 fw-semibold" data-bs-toggle="modal"
                        data-bs-target="#createMenuModal">
                        <i class="bi bi-plus-circle me-2"></i>Nuevo menú
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <div>
                    <h3 class="h4 fw-semibold mb-1">Árbol de navegación</h3>
                    <p class="text-muted mb-0">Gestiona cada enlace del portal y sus jerarquías.</p>
                </div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-semibold">Activo</span>
                    <span
                        class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2 fw-semibold">Inactivo</span>
                </div>
            </div>

            @if ($menus->count() > 0)
                <div class="d-flex flex-column gap-2 mb-0">
                    @foreach ($menus as $menu)
                        @include('admin.menus.partials.menu-item', ['menu' => $menu, 'level' => 0])
                    @endforeach
                </div>
            @else
                <div class="text-center border border-dashed rounded-4 p-5 bg-light-subtle">
                    <h4 class="fw-semibold text-muted mb-2">Aún no has creado ningún menú.</h4>
                    <p class="text-muted mb-3">Comienza añadiendo la navegación principal de tu portal.</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" data-bs-toggle="modal"
                        data-bs-target="#createMenuModal">
                        Crear primer elemento
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Ver --}}
    <div class="modal fade" id="viewMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Detalle del menú</p>
                        <h5 class="modal-title fw-semibold" data-view-title>Menu Title</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3">
                                <small class="text-uppercase text-muted d-block mb-1">Estado</small>
                                <span class="fw-semibold" data-view-status>Activo</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3">
                                <small class="text-uppercase text-muted d-block mb-1">Ubicación</small>
                                <span class="fw-semibold" data-view-location>Principal</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3">
                                <small class="text-uppercase text-muted d-block mb-1">Orden</small>
                                <span class="fw-semibold" data-view-order>0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3">
                                <small class="text-uppercase text-muted d-block mb-1">Padre</small>
                                <span class="fw-semibold" data-view-parent>-</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <small class="text-uppercase text-muted d-block mb-1">URL</small>
                        <a href="#" class="fw-semibold text-decoration-none" data-view-url target="_blank">-</a>
                    </div>
                    <div class="mt-4">
                        <small class="text-uppercase text-muted d-block mb-1">Submenús</small>
                        <div class="d-flex flex-wrap gap-2" data-view-children>
                            <span class="text-muted small">Sin submenús.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crear --}}
    <div class="modal fade" id="createMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Nuevo elemento</p>
                        <h5 class="modal-title fw-semibold">Crear Menú</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('admin.menus.store') }}" method="POST" class="modal-body pt-0">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="create_title" class="form-label fw-semibold">Título</label>
                            <input type="text" class="form-control rounded-3" id="create_title" name="title"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label for="create_url" class="form-label fw-semibold">URL</label>
                            <input type="text" class="form-control rounded-3" id="create_url" name="url"
                                placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label for="create_parent_id" class="form-label fw-semibold">Elemento padre</label>
                            <select class="form-select rounded-3" id="create_parent_id" name="parent_id">
                                <option value="">— Sin padre (nivel superior) —</option>
                                @foreach ($parents as $option)
                                    <option value="{{ $option->id }}">{{ $option->title }} ({{ $option->location }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="create_order" class="form-label fw-semibold">Orden</label>
                            <input type="number" class="form-control rounded-3" id="create_order" name="order"
                                value="0">
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <label for="create_location" class="form-label fw-semibold">Ubicación</label>
                            <select class="form-select rounded-3" id="create_location" name="location">
                                <option value="primary">Barra Principal (Header)</option>
                                <option value="footer_1">Footer Columna 1 (FuerteJob)</option>
                                <option value="footer_2">Footer Columna 2 (Empresas)</option>
                                <option value="footer_3">Footer Columna 3 (Solicitantes)</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex align-items-center gap-2 mt-2">
                            <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active"
                                value="1" checked>
                            <label class="form-check-label fw-semibold" for="create_is_active">
                                Mostrar en el portal
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Crear menú</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Editar menú</p>
                        <h5 class="modal-title fw-semibold" data-edit-title>Menú</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editMenuForm" method="POST" class="modal-body pt-0">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_title" class="form-label fw-semibold">Título</label>
                            <input type="text" class="form-control rounded-3" id="edit_title" name="title"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_url" class="form-label fw-semibold">URL</label>
                            <input type="text" class="form-control rounded-3" id="edit_url" name="url"
                                placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_parent_id" class="form-label fw-semibold">Elemento padre</label>
                            <select class="form-select rounded-3" id="edit_parent_id" name="parent_id">
                                <option value="">— Sin padre (nivel superior) —</option>
                                @foreach ($parents as $option)
                                    <option value="{{ $option->id }}">{{ $option->title }} ({{ $option->location }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="edit_order" class="form-label fw-semibold">Orden</label>
                            <input type="number" class="form-control rounded-3" id="edit_order" name="order">
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <label for="edit_location" class="form-label fw-semibold">Ubicación</label>
                            <select class="form-select rounded-3" id="edit_location" name="location">
                                <option value="primary">Barra Principal (Header)</option>
                                <option value="footer_1">Footer Columna 1 (FuerteJob)</option>
                                <option value="footer_2">Footer Columna 2 (Empresas)</option>
                                <option value="footer_3">Footer Columna 3 (Solicitantes)</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex align-items-center gap-2 mt-2">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active"
                                value="1">
                            <label class="form-check-label fw-semibold" for="edit_is_active">
                                Mostrar en el portal
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Scoped Styles for Menu Item */
        .menu-item-container {
            padding-left: 1.5rem;
            /* Base padding */
        }

        .btn-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.165, 0.84, 0.44, 1);
            font-size: 1.1rem;
            border: 1px solid transparent;
        }

        .btn-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .hover-primary:hover {
            background-color: var(--bs-primary);
            color: white !important;
        }

        .hover-danger:hover {
            background-color: var(--bs-danger);
            color: white !important;
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }
    </style>
@endpush

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- DATA MAPPING ---
            const locationLabels = {
                primary: 'Barra Principal (Header)',
                footer_1: 'Footer Columna 1 (FuerteJob)',
                footer_2: 'Footer Columna 2 (Empresas)',
                footer_3: 'Footer Columna 3 (Solicitantes)',
            };

            // --- INITIALIZE TOOLTIPS ---
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl));

            // --- MODAL INSTANCES ---
            const viewModal = new bootstrap.Modal(document.getElementById('viewMenuModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editMenuModal'));

            // --- DOM ELEMENTS (Cached) ---
            const els = {
                viewTitle: document.querySelector('[data-view-title]'),
                viewStatus: document.querySelector('[data-view-status]'),
                viewLocation: document.querySelector('[data-view-location]'),
                viewOrder: document.querySelector('[data-view-order]'),
                viewParent: document.querySelector('[data-view-parent]'),
                viewUrl: document.querySelector('[data-view-url]'),
                viewChildren: document.querySelector('[data-view-children]'),

                editForm: document.getElementById('editMenuForm'),
                editHeading: document.querySelector('[data-edit-title]'),
                editTitle: document.getElementById('edit_title'),
                editUrl: document.getElementById('edit_url'),
                editParent: document.getElementById('edit_parent_id'),
                editOrder: document.getElementById('edit_order'),
                editLocation: document.getElementById('edit_location'),
                editActive: document.getElementById('edit_is_active'),
            };

            // --- EVENT DELEGATION FOR BUTTONS ---
            document.body.addEventListener('click', function(e) {
                // VIEW BUTTON logic...
                const viewBtn = e.target.closest('[data-view-trigger]');
                if (viewBtn) {
                    e.preventDefault();
                    try {
                        const menu = JSON.parse(viewBtn.dataset.menu);
                        fillViewModal(menu);
                        viewModal.show();
                    } catch (err) {
                        console.error('Error parsing menu data for view modal:', err);
                    }
                }

                // EDIT BUTTON logic...
                const editBtn = e.target.closest('[data-edit-trigger]');
                if (editBtn) {
                    e.preventDefault();
                    try {
                        const menu = JSON.parse(editBtn.dataset.menu);
                        const actionUrl = editBtn.dataset.action;
                        fillEditModal(menu, actionUrl);
                        editModal.show();
                    } catch (err) {
                        console.error('Error parsing menu data for edit modal:', err);
                    }
                }
            });

            // --- HELPERS ---
            const statusBadge = isActive => isActive ?
                '<span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 fw-semibold">Activo</span>' :
                '<span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2 fw-semibold">Inactivo</span>';

            function fillViewModal(menu) {
                els.viewTitle.textContent = menu.title;
                els.viewStatus.innerHTML = statusBadge(menu.is_active);
                els.viewLocation.textContent = locationLabels[menu.location] || menu.location;
                els.viewOrder.textContent = menu.order ?? 0;
                els.viewParent.textContent = menu.parent_title || 'Sin padre';

                if (menu.url) {
                    els.viewUrl.textContent = menu.url;
                    els.viewUrl.href = menu.url;
                    els.viewUrl.classList.remove('text-muted');
                } else {
                    els.viewUrl.textContent = 'Sin URL definida';
                    els.viewUrl.href = '#';
                    els.viewUrl.classList.add('text-muted');
                }

                els.viewChildren.innerHTML = '';
                if (menu.children && menu.children.length) {
                    menu.children.forEach(child => {
                        const span = document.createElement('span');
                        span.className =
                            `badge rounded-pill px-3 py-2 ${child.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`;
                        span.textContent = `${child.title} · Orden ${child.order ?? 0}`;
                        els.viewChildren.appendChild(span);
                    });
                } else {
                    const span = document.createElement('span');
                    span.className = 'text-muted small';
                    span.textContent = 'Sin submenús.';
                    els.viewChildren.appendChild(span);
                }
            }

            function fillEditModal(menu, action) {
                els.editForm.action = action;
                els.editHeading.textContent = `Editando: ${menu.title}`;

                els.editTitle.value = menu.title || '';
                els.editUrl.value = menu.url || '';
                els.editOrder.value = menu.order ?? 0;
                els.editLocation.value = menu.location || 'primary';
                els.editActive.checked = !!menu.is_active;

                // Handle Parent Select
                els.editParent.value = menu.parent_id || '';
                Array.from(els.editParent.options).forEach(opt => {
                    opt.disabled = false;
                    // Disable self as parent
                    if (opt.value && Number(opt.value) === menu.id) {
                        opt.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection
