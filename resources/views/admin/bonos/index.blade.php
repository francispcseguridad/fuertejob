@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Catálogo de Bonos</h1>
            <button type="button" class="btn btn-primary" onclick="openNewBono()">Nuevo bono</button>
        </div>

        <div class="card">
            <div class="card-header">Bonos existentes</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Duración</th>
                                <th>Anuncios</th>
                                <th>Días visibilidad</th>
                                <th>CV</th>
                                <th>Usuarios</th>
                                <th>Activo</th>
                                <th>Recomendado</th>
                                <th>Extra</th>
                                <th>Analytics</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bonos as $bono)
                                <tr>
                                    <td class="fw-semibold">{{ $bono->name }}</td>
                                    <td>€{{ number_format($bono->price, 2) }}</td>
                                    <td>{{ $bono->duration_days ?? '—' }}</td>
                                    <td>{{ $bono->offer_credits ?? 0 }}</td>
                                    <td>{{ $bono->visibility_days ?? 0 }}</td>
                                    <td>{{ $bono->cv_views ?? 0 }}</td>
                                    <td>{{ $bono->user_seats ?? 0 }}</td>
                                    <td>
                                        <span class="badge {{ $bono->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $bono->is_active ? 'Sí' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $bono->destacado ? 'bg-warning text-dark' : 'bg-light text-muted' }}">
                                            {{ $bono->destacado ? 'Sí' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $bono->is_extra ? 'bg-info text-white' : 'bg-light text-muted' }}">
                                            {{ $bono->is_extra ? 'Sí' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ optional($bono->analyticsModel)->name ?? '—' }}
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"
                                            data-bono='@json($bono, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)'
                                            onclick="openEditBonoFromButton(this)">Editar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.bonos.modal')
@endsection

@section('scripts')
    <script>
        const updateTemplate = `{{ route('admin.bonos.update', ['bono' => '__ID__']) }}`;
        const storeUrl = `{{ route('admin.bonos.store') }}`;

        const bonoModalEl = document.getElementById('bonoModal');
        const bonoModal = new bootstrap.Modal(bonoModalEl);
        const bonoForm = document.getElementById('bono-form');
        const bonoMethodInput = document.getElementById('bono_method');
        const destacadoSwitch = document.getElementById('destacado_switch');
        const isExtraSwitch = document.getElementById('is_extra_switch');
        const isExtraValueInput = document.getElementById('is_extra_value');
        const analyticsModelSelect = document.getElementById('analytics_model_id');

        function resetForm() {
            bonoForm.reset();
            document.getElementById('bono_id').value = '';
            bonoForm.action = storeUrl;
            bonoMethodInput.value = 'POST';
            bonoForm.offer_credits.value = 0;
            bonoForm.visibility_days.value = 0;
            bonoForm.cv_views.value = 0;
            bonoForm.user_seats.value = 0;
            if (analyticsModelSelect) analyticsModelSelect.value = '';
            if (destacadoSwitch) {
                destacadoSwitch.checked = false;
            }
            if (isExtraSwitch) {
                isExtraSwitch.checked = false;
                isExtraSwitch.dispatchEvent(new Event('change'));
            }
            if (isExtraValueInput) {
                isExtraValueInput.value = '0';
            }
            if (window.resetRichDescription) {
                window.resetRichDescription();
            }
        }

        function openNewBono() {
            resetForm();
            bonoModal.show();
        }

        function openEditBono(bono) {
            resetForm();
            document.getElementById('bono_id').value = bono.id;
            bonoForm.action = updateTemplate.replace('__ID__', bono.id);
            bonoMethodInput.value = 'PUT';
            bonoForm.name.value = bono.name ?? '';
            bonoForm.price.value = bono.price ?? '';
            bonoForm.duration_days.value = bono.duration_days ?? '';
            bonoForm.description.value = bono.description ?? '';
            bonoForm.is_active.checked = !!bono.is_active;
            bonoForm.offer_credits.value = bono.offer_credits ?? 0;
            bonoForm.visibility_days.value = bono.visibility_days ?? 0;
            bonoForm.cv_views.value = bono.cv_views ?? 0;
            bonoForm.user_seats.value = bono.user_seats ?? 0;
            if (analyticsModelSelect) {
                analyticsModelSelect.value = bono.analytics_model_id ?? '';
            }
            if (destacadoSwitch) {
                destacadoSwitch.checked = !!bono.destacado;
            }
            if (isExtraSwitch) {
                isExtraSwitch.checked = !!bono.is_extra;
                isExtraSwitch.dispatchEvent(new Event('change'));
            }
            if (isExtraValueInput) {
                isExtraValueInput.value = bono.is_extra ? '1' : '0';
            }
            if (window.loadRichDescription) {
                window.loadRichDescription(bono.description ?? '');
            }

            bonoModal.show();
        }

        function openEditBonoFromButton(button) {
            const raw = button.getAttribute('data-bono');
            if (!raw) return;
            openEditBono(JSON.parse(raw));
        }

        bonoForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('bono_id').value;
            const url = id ? updateTemplate.replace('__ID__', id) : storeUrl;
            const method = id ? 'POST' : 'POST';

            const formData = new FormData(bonoForm);
            formData.set('_method', id ? 'PUT' : 'POST');
            if (!formData.has('is_active')) formData.set('is_active', 0);
            if (destacadoSwitch) {
                formData.set('destacado', destacadoSwitch.checked ? 1 : 0);
            } else if (!formData.has('destacado')) {
                formData.set('destacado', 0);
            }
            if (window.updateRichDescription) {
                window.updateRichDescription();
            }
            if (isExtraValueInput) {
                formData.set('is_extra', isExtraValueInput.value);
            } else if (isExtraSwitch) {
                formData.set('is_extra', isExtraSwitch.checked ? 1 : 0);
            } else if (!formData.has('is_extra')) {
                formData.set('is_extra', 0);
            }

            const res = await fetch(url, {
                method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token')
                },
                body: formData
            });

            if (!res.ok) {
                let message = 'Error al guardar. Revisa los datos.';
                try {
                    const payload = await res.json();
                    if (payload && payload.errors) {
                        const firstKey = Object.keys(payload.errors)[0];
                        if (firstKey) {
                            message = payload.errors[firstKey][0] ?? message;
                        }
                    } else if (payload && payload.message) {
                        message = payload.message;
                    }
                } catch (err) {
                    // Ignore parse errors and show generic message.
                }
                alert(message);
                return;
            }

            if (res.redirected) {
                alert('La sesión ha expirado. Recarga la página e inténtalo de nuevo.');
                return;
            }

            try {
                const payload = await res.json();
                if (payload && payload.success === false) {
                    alert(payload.message || 'No se pudo guardar el bono.');
                    return;
                }
            } catch (err) {
                // If response is not JSON, continue to reload.
            }

            location.reload();
        });

        document.addEventListener('DOMContentLoaded', resetForm);
    </script>
@endsection
