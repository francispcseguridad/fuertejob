@extends('layouts.app')
@section('title', 'Gestión de Sectores')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1">Sectores y Subsectores</h1>
                <p class="text-muted mb-0">Administra la jerarquía de sectores que se muestran en el portal.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-lg rounded-pill shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#sectorModal" onclick="openCreateModal()">
                    <i class="bi bi-plus-circle me-2"></i> Nuevo Sector
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Errores de validación
                </h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-primary"><i class="bi bi-diagram-3 me-2"></i>Listado de sectores</h5>
                    <small class="text-muted">Incluye sectores principales y subsectores.</small>
                </div>
                <span class="badge bg-light text-muted border">Total: {{ $sectors->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th>Nombre</th>
                                <th>Slug</th>
                                <th>Padre</th>
                                <th>Orden</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sectors as $sector)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $sector->name }}</td>
                                    <td><code>{{ $sector->slug }}</code></td>
                                    <td>{{ $sector->parent?->name ?? 'Principal' }}</td>
                                    <td>{{ $sector->sort_order ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $sector->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $sector->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#sectorModal"
                                                data-sector='@json($sector)'
                                                onclick="openEditModal(this)">
                                                <i class="bi bi-pencil-square me-1"></i>Editar
                                            </button>
                                            <form action="{{ route('admin.sectores.destroy', $sector) }}" method="POST"
                                                onsubmit="return confirm('¿Eliminar el sector {{ $sector->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox me-2"></i>No hay sectores registrados aún.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sectorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="sectorModalTitle">Nuevo Sector</h5>
                        <small class="text-muted" id="sectorModalSubtitle">Configura la información general del sector.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="sectorForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="sectorMethodField">
                    <div class="modal-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombre</label>
                                <input type="text" class="form-control" name="name" id="sectorName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Slug</label>
                                <input type="text" class="form-control" name="slug" id="sectorSlug"
                                    placeholder="Se genera automáticamente si lo dejas vacío">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sector padre</label>
                                <select class="form-select" name="parent_id" id="sectorParent">
                                    <option value="">— Principal —</option>
                                    @foreach ($parentOptions as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Orden</label>
                                <input type="number" class="form-control" name="sort_order" id="sectorOrder"
                                    min="0">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active"
                                        id="sectorActive" checked>
                                    <label class="form-check-label" for="sectorActive">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="sectorSubmitButton">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        const sectorForm = document.getElementById('sectorForm');
        const methodField = document.getElementById('sectorMethodField');
        const nameInput = document.getElementById('sectorName');
        const slugInput = document.getElementById('sectorSlug');
        const parentSelect = document.getElementById('sectorParent');
        const orderInput = document.getElementById('sectorOrder');
        const activeInput = document.getElementById('sectorActive');
        const modalTitle = document.getElementById('sectorModalTitle');
        const modalSubtitle = document.getElementById('sectorModalSubtitle');
        const submitButton = document.getElementById('sectorSubmitButton');

        function openCreateModal() {
            sectorForm.action = "{{ route('admin.sectores.store') }}";
            sectorForm.reset();
            methodField.value = '';
            methodField.disabled = true;
            activeInput.checked = true;
            modalTitle.textContent = 'Nuevo Sector';
            modalSubtitle.textContent = 'Crea un sector principal o asigna un padre para un subsector.';
            submitButton.textContent = 'Crear sector';
        }

        function openEditModal(button) {
            const sector = JSON.parse(button.getAttribute('data-sector'));
            sectorForm.action = button.getAttribute('data-update-url') ||
                `{{ url('administracion/sectores') }}/${sector.id}`;

            methodField.disabled = false;
            methodField.value = 'PUT';

            nameInput.value = sector.name;
            slugInput.value = sector.slug ?? '';
            parentSelect.value = sector.parent_id ?? '';
            orderInput.value = sector.sort_order ?? '';
            activeInput.checked = Boolean(sector.is_active);

            modalTitle.textContent = 'Editar sector';
            modalSubtitle.textContent = `Actualiza los detalles de ${sector.name}.`;
            submitButton.textContent = 'Actualizar sector';
        }
    </script>
@endsection
