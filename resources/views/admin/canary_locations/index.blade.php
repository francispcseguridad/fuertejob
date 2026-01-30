@extends('layouts.app')

@section('title', 'Municipios/Localidades Canarias')

@section('content')
    <div class="container py-4">
        {{-- Hero --}}
        <div class="position-relative overflow-hidden rounded-4 mb-4 shadow-sm" style="background: #1b476c;">
            <div class="row g-0 align-items-center text-white p-4 p-md-5">
                <div class="col-md-8">
                    <p class="text-uppercase fw-semibold mb-2 small opacity-75">Geografía • Canarias</p>
                    <h1 class="display-6 fw-bold mb-2">Municipios y localidades</h1>
                    <p class="lead mb-0 text-white-75">Refuerza LocationIQ con un catálogo curado para búsquedas precisas en
                        las islas.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-light btn-lg fw-semibold shadow-sm" id="btnNew">
                        <i class="bi bi-plus-lg me-2"></i>Nuevo registro
                    </button>
                </div>
            </div>
            <div class="position-absolute top-0 end-0 opacity-25 pe-4 pt-3">
                <i class="bi bi-geo-alt-fill" style="font-size: 6rem; color: #a9b6ff;"></i>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.localidades.index') }}" class="row gy-3 gx-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary">Buscar ciudad/municipio</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" value="{{ $search }}" class="form-control"
                                placeholder="Ej: Arrecife, La Laguna">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary">Isla</label>
                        <select name="island" class="form-select form-select-lg">
                            <option value="">Todas las islas</option>
                            @foreach ($islandOptions as $option)
                                <option value="{{ $option }}" {{ $option === $island ? 'selected' : '' }}>
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary">Provincia</label>
                        <select name="province" class="form-select form-select-lg">
                            <option value="">Todas las provincias</option>
                            @foreach ($provinceOptions as $option)
                                <option value="{{ $option }}" {{ $option === $province ? 'selected' : '' }}>
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-funnel me-1"></i> Aplicar filtros
                        </button>
                        <a href="{{ route('admin.localidades.index') }}" class="btn btn-light btn-lg border">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-muted">
                            <th class="ps-4">Ciudad / Municipio</th>
                            <th>Isla</th>
                            <th>Provincia</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $location)
                            <tr data-id="{{ $location->id }}" data-city="{{ $location->city }}"
                                data-island="{{ $location->island }}" data-province="{{ $location->province }}"
                                data-country="{{ $location->country }}"
                                data-url-update="{{ route('admin.localidades.update', $location) }}"
                                data-url-delete="{{ route('admin.localidades.destroy', $location) }}">
                                <td class="ps-4">
                                    <span class="fw-semibold">{{ $location->city }}</span>
                                </td>
                                <td>
                                    @if ($location->island)
                                        <span
                                            class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill">{{ $location->island }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($location->province)
                                        <span
                                            class="badge bg-secondary-subtle text-secondary fw-semibold px-3 py-2 rounded-pill">{{ $location->province }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 js-edit">Editar</button>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-1 js-delete">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay registros aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($locations->hasPages())
                <div class="card-body pb-3">
                    {{ $locations->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal CRUD --}}
    <div class="modal fade" id="localidadModal" tabindex="-1" aria-labelledby="localidadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <p class="text-uppercase small fw-semibold text-primary mb-1" id="modalModeLabel">Nueva</p>
                        <h5 class="modal-title fw-bold" id="localidadModalLabel">Localidad</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="modalErrors"></div>
                    <form id="localidadForm">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <input type="hidden" name="country" id="country" value="España">
                        <div class="mb-3">
                            <label for="city" class="form-label">Ciudad / Municipio *</label>
                            <input type="text" class="form-control" id="cityInput" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="island" class="form-label">Isla</label>
                            <select class="form-select" id="islandInput" name="island">
                                <option value="">Selecciona isla</option>
                                @foreach ($islandOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="province" class="form-label">Provincia (auto)</label>
                            <input type="text" class="form-control" id="provinceDisplay" value="" readonly>
                            <input type="hidden" id="provinceInput" name="province" value="">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveLocalidad">Guardar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalElement = document.getElementById('localidadModal');
            const modal = new bootstrap.Modal(modalElement);
            const form = document.getElementById('localidadForm');
            const errorsBox = document.getElementById('modalErrors');
            const saveBtn = document.getElementById('saveLocalidad');
            const methodInput = document.getElementById('formMethod');
            const cityInput = document.getElementById('cityInput');
            const islandInput = document.getElementById('islandInput');
            const provinceDisplay = document.getElementById('provinceDisplay');
            const provinceInput = document.getElementById('provinceInput');
            const countryInput = document.getElementById('country');
            const modeLabel = document.getElementById('modalModeLabel');
            const btnNew = document.getElementById('btnNew');

            let currentAction = "{{ route('admin.localidades.store') }}";
            const islandProvinceMap = @json($islandProvinceMap);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const resetForm = () => {
                form.reset();
                errorsBox.classList.add('d-none');
                errorsBox.innerHTML = '';
                methodInput.value = 'POST';
                countryInput.value = 'España';
                provinceDisplay.value = '';
                provinceInput.value = '';
            };

            const showErrors = (errors) => {
                errorsBox.classList.remove('d-none');
                errorsBox.innerHTML = '';
                Object.values(errors).forEach(msgs => {
                    msgs.forEach(msg => {
                        const div = document.createElement('div');
                        div.textContent = msg;
                        errorsBox.appendChild(div);
                    });
                });
            };

            btnNew.addEventListener('click', () => {
                resetForm();
                modeLabel.textContent = 'Nueva';
                currentAction = "{{ route('admin.localidades.store') }}";
                modal.show();
            });

            document.querySelectorAll('.js-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    resetForm();
                    const row = btn.closest('tr');
                    cityInput.value = row.dataset.city || '';
                    islandInput.value = row.dataset.island || '';
                    const provinceFromRow = row.dataset.province || '';
                    provinceDisplay.value = provinceFromRow;
                    provinceInput.value = provinceFromRow;
                    countryInput.value = row.dataset.country || 'España';
                    currentAction = row.dataset.urlUpdate;
                    methodInput.value = 'PUT';
                    modeLabel.textContent = 'Editar';
                    modal.show();
                });
            });

            saveBtn.addEventListener('click', () => {
                errorsBox.classList.add('d-none');
                const formData = new FormData(form);
                const islandSelected = islandInput.value;
                if (islandSelected && islandProvinceMap[islandSelected]) {
                    formData.set('province', islandProvinceMap[islandSelected]);
                }
                fetch(currentAction, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    })
                    .then(async response => {
                        if (response.ok) return response.json();
                        const data = await response.json();
                        throw data;
                    })
                    .then(() => {
                        modal.hide();
                        window.location.reload();
                    })
                    .catch(err => {
                        if (err && err.errors) {
                            showErrors(err.errors);
                        } else {
                            showErrors({
                                general: ['Ocurrió un error al guardar.']
                            });
                        }
                    });
            });

            document.querySelectorAll('.js-delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const row = btn.closest('tr');
                    const url = row.dataset.urlDelete;
                    const name = row.dataset.city || 'esta localidad';
                    const confirmDelete = () => {
                        const fd = new FormData();
                        fd.append('_method', 'DELETE');
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: fd
                            })
                            .then(async res => {
                                if (res.ok) return res.json();
                                const data = await res.json().catch(() => ({}));
                                throw data;
                            })
                            .then(() => window.location.reload())
                            .catch((err) => {
                                const msg = err?.message ||
                                    'No se pudo eliminar. Inténtalo de nuevo.';
                                alert(msg);
                            });
                    };

                    if (window.Swal) {
                        Swal.fire({
                            title: '¿Eliminar?',
                            text: `Se borrará ${name}.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then(result => {
                            if (result.isConfirmed) confirmDelete();
                        });
                    } else {
                        if (confirm(`¿Seguro que quieres eliminar ${name}?`)) confirmDelete();
                    }
                });
            });

            islandInput.addEventListener('change', () => {
                const selected = islandInput.value;
                const province = islandProvinceMap[selected] || '';
                provinceDisplay.value = province;
                provinceInput.value = province;
            });
        });
    </script>
@endpush
