@extends('layouts.app')
@section('title', 'Gestión de Bonos del Portal')
@section('content')

    <style>
        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }
    </style>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="bi bi-tag-fill text-primary me-2"></i>
                            Gestión de Bonos
                        </h1>
                        <p class="text-muted mb-0">Administra los paquetes de créditos para empresas</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" id="btn-create-bono">
                        <i class="bi bi-plus-circle-fill me-2"></i> Nuevo Bono
                    </button>
                </div>

                {{-- Navigation Breadcrumb --}}
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-house-door-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Catálogo de Bonos</li>
                    </ol>
                </nav>

                {{-- Search and Filter Section --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" id="search-bono" class="form-control"
                                        placeholder="Buscar por nombre, descripción...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="filterStatus" class="form-select">
                                    <option value="">Todos los registros</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary w-100" type="button" id="btnClear">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bonos Grid --}}
                <div class="row" id="bonos-list">
                    <!-- Aquí se cargarán los bonos vía AJAX -->
                </div>

                {{-- No Results Message --}}
                <div id="no-bonos-message" class="card shadow-sm border-0 bg-light d-none">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-tag text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                        <h5 class="text-muted mb-2">No se encontraron bonos</h5>
                        <p class="text-muted">No hay bonos registrados o no coinciden con la búsqueda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Bono -->
    <div class="modal fade" id="bonoModal" tabindex="-1" aria-labelledby="bonoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bonoModalLabel">Crear Nuevo Bono</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="bono-form">
                    <div class="modal-body p-4">
                        <!-- Campo Oculto para ID -->
                        <input type="hidden" id="bono_id" name="bono_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Nombre del Bono *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    placeholder="Ej: Pack Básico">
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label fw-bold">Precio (€) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price"
                                        required min="0" placeholder="0.00">
                                </div>
                                <div class="invalid-feedback" id="price-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="credits_included" class="form-label fw-bold">Créditos Incluidos *</label>
                                <input type="number" class="form-control" id="credits_included" name="credits_included"
                                    required min="1" placeholder="Ej: 10">
                                <div class="invalid-feedback" id="credits_included-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="duration_days" class="form-label fw-bold">Duración (Días) *</label>
                                <input type="number" class="form-control" id="duration_days" name="duration_days"
                                    required min="1" placeholder="Ej: 30">
                                <div class="invalid-feedback" id="duration_days-error"></div>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Descripción detallada del bono..."></textarea>
                                <div class="invalid-feedback" id="description-error"></div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        checked>
                                    <label class="form-check-label" for="is_active">Bono Activo (visible para
                                        empresas)</label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger d-none mt-3" id="form-error-message" role="alert"></div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4" id="save-bono-btn">Guardar Bono</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bonoModalEl = document.getElementById('bonoModal');
            const bonoModal = new bootstrap.Modal(bonoModalEl);
            const bonoForm = document.getElementById('bono-form');
            const modalTitle = document.getElementById('bonoModalLabel');
            const modalHeader = document.querySelector('#bonoModal .modal-header');
            const bonosListCtx = document.getElementById('bonos-list');
            const noBonosMsg = document.getElementById('no-bonos-message');
            const searchInput = document.getElementById('search-bono');
            const filterStatus = document.getElementById('filterStatus');
            const btnClear = document.getElementById('btnClear');

            // URLs generadas por Blade
            const urls = {
                index: "{{ route('admin.bonos.index') }}",
                store: "{{ route('admin.bonos.store') }}",
                base: "{{ url('administracion/bonos') }}"
            };

            // ---- 1. Cargar Bonos ----
            function loadBonos() {
                fetch(urls.index, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            renderCards(res.data);
                        }
                    })
                    .catch(error => console.error('Error al cargar bonos:', error));
            }

            function renderCards(bonos) {
                bonosListCtx.innerHTML = '';

                // Filtrado en cliente (podría ser en servidor si fuera paginado)
                const term = searchInput.value.toLowerCase();
                const statusVal = filterStatus.value; // "1", "0" or ""

                const filtered = bonos.filter(bono => {
                    const nameMatch = bono.name.toLowerCase().includes(term);
                    const descMatch = (bono.description || '').toLowerCase().includes(term);

                    let statusMatch = true;
                    if (statusVal !== "") {
                        // bono.is_active puede ser true/false o 1/0
                        const isActive = (bono.is_active == 1 || bono.is_active === true);
                        const wantActive = (statusVal === "1");
                        statusMatch = (isActive === wantActive);
                    }

                    return (nameMatch || descMatch) && statusMatch;
                });

                if (filtered.length === 0) {
                    noBonosMsg.classList.remove('d-none');
                    return; // Stop here if empty
                }
                noBonosMsg.classList.add('d-none');

                filtered.forEach(bono => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-4 mb-4';

                    const description = bono.description || 'Sin descripción';
                    const truncatedDesc = description.length > 100 ? description.substring(0, 100) + '...' :
                        description;
                    const statusBadge = bono.is_active ?
                        '<span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle me-1"></i>Activo</span>' :
                        '<span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-x-circle me-1"></i>Inactivo</span>';

                    const formattedPrice = parseFloat(bono.price).toLocaleString('es-ES', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    col.innerHTML = `
                            <div class="card h-100 shadow-sm hover-shadow transition-all border-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title fw-bold text-primary mb-0">${bono.name}</h5>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-5"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <button class="dropdown-item btn-edit" type="button" data-id="${bono.id}">
                                                        <i class="bi bi-pencil-square text-warning me-2"></i> Editar
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item btn-delete text-danger" type="button" data-id="${bono.id}">
                                                        <i class="bi bi-trash-fill me-2"></i> Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        ${statusBadge}
                                    </div>

                                    <h3 class="mb-3 fw-bold">${formattedPrice} €</h3>

                                    <div class="d-flex justify-content-between text-muted mb-3 small">
                                        <span><i class="bi bi-coin me-1"></i> ${bono.credits_included} Créditos</span>
                                        <span><i class="bi bi-calendar-event me-1"></i> ${bono.duration_days} Días</span>
                                    </div>

                                    <p class="card-text text-secondary small border-top pt-3">
                                        ${truncatedDesc}
                                    </p>
                                </div>
                            </div>
                        `;
                    bonosListCtx.appendChild(col);
                });

                // Re-attach listeners is easier by delegation or re-query
                // Here using re-query for simplicity within Vanilla JS structure
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        openEditModal(id);
                    });
                });

                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        deleteBono(id);
                    });
                });
            }

            // ---- 2. Filtros ----
            function refreshView() {
                // Re-fetch para asegurar datos frescos o simplemente re-renderizar si tuvieramos los datos cacheados
                // En este ejemplo simple, volvemos a cargar del backend para simplicidad
                loadBonos();
            }

            if (searchInput) {
                searchInput.addEventListener('input',
                    refreshView); // 'input' is better than 'keyup' (handles paste)
            }
            if (filterStatus) {
                filterStatus.addEventListener('change', refreshView);
            }
            if (btnClear) {
                btnClear.addEventListener('click', () => {
                    searchInput.value = '';
                    filterStatus.value = '';
                    refreshView();
                });
            }

            // ---- 3. Crear / Abrir Modal ----
            document.getElementById('btn-create-bono').addEventListener('click', () => {
                openCreateModal();
            });

            function openCreateModal() {
                modalTitle.textContent = 'Crear Nuevo Bono';
                modalHeader.classList.remove('bg-warning', 'text-dark');
                modalHeader.classList.add('bg-primary', 'text-white');
                bonoForm.reset();
                document.getElementById('bono_id').value = '';
                clearValidationErrors();
                document.getElementById('is_active').checked = true;
                bonoModal.show();
            }

            // ---- 4. Editar ----
            function openEditModal(id) {
                modalTitle.textContent = 'Editar Bono';
                // Cambiar estilo header para diferenciar edición
                modalHeader.classList.remove('bg-primary', 'text-white');
                modalHeader.classList.add('bg-warning', 'text-dark');

                clearValidationErrors();

                fetch(`${urls.base}/${id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            const data = res.data;
                            document.getElementById('bono_id').value = data.id;
                            document.getElementById('name').value = data.name;
                            document.getElementById('price').value = data.price;
                            document.getElementById('credits_included').value = data.credits_included;
                            document.getElementById('duration_days').value = data.duration_days;
                            document.getElementById('description').value = data.description || '';
                            document.getElementById('is_active').checked = !!data.is_active;

                            bonoModal.show();
                        } else {
                            alert('No se pudo cargar la información del bono.');
                        }
                    })
                    .catch(err => console.error(err));
            }

            // ---- 5. Guardar (Store / Update) ----
            bonoForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const id = document.getElementById('bono_id').value;
                const isEdit = !!id;
                const url = isEdit ? `${urls.base}/${id}` : urls.store;
                const method = isEdit ? 'PUT' : 'POST';

                const formData = new FormData(bonoForm);
                const dataObj = Object.fromEntries(formData.entries());
                dataObj.is_active = document.getElementById('is_active').checked ? 1 : 0;

                const btnSubmit = document.getElementById('save-bono-btn');
                const originalText = btnSubmit.innerHTML;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

                fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(dataObj)
                    })
                    .then(response => response.json().then(data => ({
                        status: response.status,
                        body: data
                    })))
                    .then(({
                        status,
                        body
                    }) => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;

                        clearValidationErrors();
                        if (status === 422) {
                            showValidationErrors(body.errors);
                        } else if (body.success) {
                            bonoModal.hide();
                            loadBonos();
                            // Optional: Show success toast/alert
                        } else {
                            alert('Ocurrió un error: ' + (body.message || 'Desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;
                    });
            });

            // ---- 6. Eliminar ----
            function deleteBono(id) {
                if (!confirm('¿Estás seguro de eliminar este bono? Esta acción no se puede deshacer.')) return;

                fetch(`${urls.base}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            loadBonos();
                        } else {
                            alert('Error al eliminar: ' + (res.message || 'Desconocido'));
                        }
                    })
                    .catch(err => console.error(err));
            }

            // ---- Utilidades ----
            function showValidationErrors(errors) {
                for (const [field, messages] of Object.entries(errors)) {
                    const input = document.getElementById(field);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorDiv = document.getElementById(`${field}-error`);
                        if (errorDiv) {
                            errorDiv.textContent = messages[0];
                        }
                    }
                }
            }

            function clearValidationErrors() {
                const inputs = bonoForm.querySelectorAll('.is-invalid');
                inputs.forEach(input => input.classList.remove('is-invalid'));
                const feedBacks = bonoForm.querySelectorAll('.invalid-feedback');
                feedBacks.forEach(fb => fb.textContent = '');
            }

            // Inicializar
            loadBonos();
        });
    </script>
@endsection

@endsection
