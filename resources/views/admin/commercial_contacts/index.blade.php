@extends('layouts.app')
@section('title', 'Contactos Comerciales')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Contactos Comerciales</h1>
                <p class="text-muted mb-0">Solicitudes recibidas desde academias, inmobiliarias y otros espacios interesados
                    en anunciarse.</p>
            </div>
            <a href="{{ route('admin.contactos-comerciales.index') }}" class="btn btn-outline-primary">Refrescar</a>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.contactos-comerciales.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="origin">Origen</label>
                        <select name="origin" id="origin" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($origins as $origin)
                                <option value="{{ $origin }}" @selected(($filters['origin'] ?? '') === $origin)>{{ $origin }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="is_read">Estado</label>
                        <select name="is_read" id="is_read" class="form-select">
                            <option value="">Todos</option>
                            <option value="read" @selected(($filters['is_read'] ?? '') === 'read')>Leídos</option>
                            <option value="unread" @selected(($filters['is_read'] ?? '') === 'unread')>No leídos</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.contactos-comerciales.index') }}"
                            class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Origen</th>
                            <th>Leído</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr data-contact-row="{{ $contact->id }}">
                                <td>{{ $contact->id }}</td>
                                <td>{{ $contact->name }}</td>
                                <td>{{ $contact->phone }}</td>
                                <td>{{ $contact->email }}</td>
                                <td>{{ $contact->origin }}</td>
                                <td>
                                    <span
                                        class="badge contact-read-badge bg-{{ $contact->is_read ? 'success' : 'warning' }}">
                                        {{ $contact->is_read ? 'Leído' : 'No leído' }}
                                    </span>
                                </td>
                                <td>
                                    {{ optional($contact->created_at)->format('d/m/Y H:i') ?? 'Fecha no disponible' }}
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary contact-detail-btn"
                                        data-bs-toggle="modal" data-bs-target="#commercialContactModal"
                                        data-contact-id="{{ $contact->id }}"
                                        data-show-url="{{ route('admin.contactos-comerciales.show', $contact) }}"
                                        data-update-url="{{ route('admin.contactos-comerciales.update', $contact) }}">
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No hay contactos comerciales registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $contacts->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="commercialContactModal" tabindex="-1" aria-labelledby="commercialContactModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="commercialContactModalLabel">
                            Detalle del contacto
                        </h5>
                        <span class="badge bg-light text-dark" data-contact-modal-badge>--- </span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" role="alert" data-contact-modal-error>
                        No se pudo cargar la información. Intenta de nuevo más tarde.
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <p class="text-muted mb-1">ID</p>
                            <p class="fw-semibold mb-0" data-contact-modal-id>---</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Estado</p>
                            <p class="fw-semibold mb-0" data-contact-modal-status>---</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Nombre</p>
                            <p class="fw-semibold mb-0" data-contact-modal-name>---</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Origen</p>
                            <p class="fw-semibold mb-0" data-contact-modal-origin>---</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Correo</p>
                            <p class="mb-0" data-contact-modal-email>---</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Teléfono</p>
                            <p class="mb-0" data-contact-modal-phone>---</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Fecha</p>
                            <p class="mb-0" data-contact-modal-created>---</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">IP</p>
                            <p class="mb-0" data-contact-modal-ip>---</p>
                        </div>
                    </div>
                    <div>
                        <h6>Detalle</h6>
                        <p class="text-muted mb-0" data-contact-modal-detail>---</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button type="button" id="contact-modal-toggle" class="btn btn-primary" disabled>
                        Cargando...
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const modalEl = document.getElementById('commercialContactModal');
                if (!modalEl) return;

                const toggleButton = modalEl.querySelector('#contact-modal-toggle');
                const badgeEl = modalEl.querySelector('[data-contact-modal-badge]');
                const idEl = modalEl.querySelector('[data-contact-modal-id]');
                const statusEl = modalEl.querySelector('[data-contact-modal-status]');
                const nameEl = modalEl.querySelector('[data-contact-modal-name]');
                const originEl = modalEl.querySelector('[data-contact-modal-origin]');
                const emailEl = modalEl.querySelector('[data-contact-modal-email]');
                const phoneEl = modalEl.querySelector('[data-contact-modal-phone]');
                const createdEl = modalEl.querySelector('[data-contact-modal-created]');
                const ipEl = modalEl.querySelector('[data-contact-modal-ip]');
                const detailEl = modalEl.querySelector('[data-contact-modal-detail]');
                const errorEl = modalEl.querySelector('[data-contact-modal-error]');

                let currentContact = null;
                let currentRow = null;
                let currentUpdateUrl = null;
                const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.content : '';

                const setPlaceholders = () => {
                    nameEl.textContent = 'Cargando...';
                    originEl.textContent = 'Cargando...';
                    emailEl.textContent = 'Cargando...';
                    phoneEl.textContent = 'Cargando...';
                    createdEl.textContent = 'Cargando...';
                    ipEl.textContent = 'Cargando...';
                    detailEl.textContent = 'Cargando...';
                    statusEl.textContent = 'Cargando...';
                    badgeEl.textContent = 'Cargando...';
                    badgeEl.className = 'badge bg-light text-dark';
                    toggleButton.textContent = 'Cargando...';
                    toggleButton.disabled = true;
                    toggleButton.classList.remove('btn-success', 'btn-warning');
                    toggleButton.classList.add('btn-primary');
                    errorEl.classList.add('d-none');
                };

                const updateModal = (contact) => {
                    currentContact = contact;
                    errorEl.classList.add('d-none');
                    badgeEl.textContent = contact.is_read ? 'Leído' : 'No leído';
                    badgeEl.className = contact.is_read ? 'badge bg-success' : 'badge bg-warning text-dark';
                    idEl.textContent = `#${contact.id}`;
                    statusEl.textContent = contact.is_read ? 'Leído' : 'No leído';
                    nameEl.textContent = contact.name;
                    originEl.textContent = contact.origin;
                    emailEl.textContent = contact.email;
                    phoneEl.textContent = contact.phone;
                    createdEl.textContent = contact.created_at_formatted ?? 'Fecha no disponible';
                    ipEl.textContent = contact.ip_address ?? 'N/A';
                    detailEl.textContent = contact.detail || 'Sin detalle adicional.';
                    const nextLabel = contact.is_read ? 'Marcar como no leído' : 'Marcar como leído';
                    toggleButton.textContent = nextLabel;
                    toggleButton.disabled = false;
                    toggleButton.classList.remove('btn-primary', 'btn-success', 'btn-warning');
                    toggleButton.classList.add(contact.is_read ? 'btn-warning' : 'btn-success');
                };

                const updateRowState = (contact) => {
                    if (!currentRow) return;
                    const badge = currentRow.querySelector('.contact-read-badge');
                    if (badge) {
                        badge.textContent = contact.is_read ? 'Leído' : 'No leído';
                        badge.classList.toggle('bg-success', contact.is_read);
                        badge.classList.toggle('bg-warning', !contact.is_read);
                        badge.classList.toggle('text-dark', !contact.is_read);
                    }
                    const createdCell = currentRow.querySelector('[data-contact-created]');
                    if (createdCell) {
                        createdCell.textContent = contact.created_at_formatted ?? 'Fecha no disponible';
                    }
                };

                const loadContact = async (showUrl, updateUrl, row) => {
                    currentRow = row;
                    currentUpdateUrl = updateUrl;
                    setPlaceholders();

                    try {
                        const response = await fetch(showUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('No se pudo obtener el contacto');
                        }

                        const payload = await response.json();
                        if (!payload.contact) {
                            throw new Error('Respuesta inválida del servidor');
                        }

                        updateModal(payload.contact);
                    } catch (error) {
                        console.error(error);
                        errorEl.classList.remove('d-none');
                        toggleButton.textContent = 'Marcar como leído';
                        toggleButton.disabled = true;
                        nameEl.textContent = 'Error al cargar';
                        originEl.textContent = '-';
                        emailEl.textContent = '-';
                        phoneEl.textContent = '-';
                        createdEl.textContent = '-';
                        ipEl.textContent = '-';
                        detailEl.textContent = 'No se pudo cargar el detalle.';
                        badgeEl.textContent = 'Error';
                        statusEl.textContent = 'Error';
                    }
                };

                document.body.addEventListener('click', function(event) {
                    const trigger = event.target.closest('.contact-detail-btn');
                    if (!trigger) return;
                    const showUrl = trigger.dataset.showUrl;
                    const updateUrl = trigger.dataset.updateUrl;
                    if (!showUrl || !updateUrl) return;
                    const row = trigger.closest('[data-contact-row]');
                    loadContact(showUrl, updateUrl, row);
                });

                toggleButton.addEventListener('click', async function() {
                    if (!currentContact || !currentUpdateUrl) return;
                    const payload = {
                        is_read: currentContact.is_read ? 0 : 1,
                    };

                    toggleButton.disabled = true;

                    try {
                        const response = await fetch(currentUpdateUrl, {
                            method: 'PATCH',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload),
                        });

                        if (!response.ok) {
                            throw new Error('No se pudo actualizar el estado');
                        }

                        const updated = await response.json();
                        if (!updated.contact) {
                            throw new Error('Respuesta inválida del servidor');
                        }

                        currentContact = updated.contact;
                        updateModal(currentContact);
                        updateRowState(currentContact);
                    } catch (error) {
                        console.error(error);
                    } finally {
                        toggleButton.disabled = false;
                    }
                });

                modalEl.addEventListener('hidden.bs.modal', function() {
                    currentContact = null;
                    currentRow = null;
                    currentUpdateUrl = null;
                });
            })();
        </script>
    @endpush
@endsection
