@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }

        .avatar-placeholder {
            width: 100px;
            height: 100px;
            background-color: #f8f9fa;
            border: 3px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 1rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .sector-autocomplete-wrapper .form-text {
            font-size: 0.85rem;
        }

        .sector-tags-wrapper {
            border: 1px dashed #ced4da;
            border-radius: 0.75rem;
            padding: 0.75rem;
            background-color: #f8f9fa;
            min-height: 52px;
        }

        .sector-pill {
            background-color: #0d6efd;
            color: #fff;
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.2);
        }

        .sector-pill .btn-close {
            width: 0.6rem;
            height: 0.6rem;
            font-size: 0.65rem;
        }

        #sector_search:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .ui-autocomplete {
            z-index: 2000 !important;
        }
    </style>
@endsection

@section('content')
    <div class="container pb-5">
        <!-- Header Section -->
        <div class="profile-header shadow-sm mt-3 rounded-4 p-4 p-md-5">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2">
                        {{ $profile ? 'Editar Perfil Corporativo' : 'Crear Perfil Corporativo' }}
                    </h1>
                    <p class="lead mb-0 opacity-75">
                        Administra la información de tu empresa, tu presentación al mundo.
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <i class="bi bi-building-fill-gear display-1 opacity-25"></i>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $initialSectorData = ($profile?->sectors ?? collect())->map(function ($sector) {
                $label = $sector->parent ? $sector->parent->name . ' · ' . $sector->name : $sector->name;

                return [
                    'id' => $sector->id,
                    'label' => $label,
                ];
            });
        @endphp

        <form method="POST" action="{{ route('empresa.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="row g-4">
                <!-- Left Column: Main Info -->
                <div class="col-lg-8">
                    <!-- General Information Card -->
                    <div class="card mb-4 h-100">
                        <div class="card-body p-4">
                            <h2 class="form-section-title mt-0"><i
                                    class="bi bi-info-circle me-2 text-primary"></i>Información
                                General</h2>
                            <p class="text-muted small mb-4">Detalles básicos visibles en sus ofertas y perfil público.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label">Nombre Comercial <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-briefcase"></i></span>
                                        <input type="text"
                                            class="form-control border-start-0 ps-0 @error('company_name') is-invalid @enderror"
                                            name="company_name" id="company_name"
                                            value="{{ old('company_name', $profile->company_name ?? '') }}" required
                                            placeholder="Ej. Tech Solutions">
                                    </div>
                                    @error('company_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Público</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-envelope"></i></span>
                                        <input type="email"
                                            class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                            name="email" id="email" value="{{ old('email', $profile->email ?? '') }}"
                                            placeholder="contacto@empresa.com">
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-telephone"></i></span>
                                        <input type="text"
                                            class="form-control border-start-0 ps-0 @error('phone') is-invalid @enderror"
                                            name="phone" id="phone" value="{{ old('phone', $profile->phone ?? '') }}"
                                            placeholder="+34 600 000 000">
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="website_url" class="form-label">Sitio Web</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-globe"></i></span>
                                        <input type="url"
                                            class="form-control border-start-0 ps-0 @error('website_url') is-invalid @enderror"
                                            name="website_url" id="website_url"
                                            value="{{ old('website_url', $profile->website_url ?? '') }}"
                                            placeholder="https://www.empresa.com">
                                    </div>
                                    @error('website_url')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="company_city" class="form-label">Ciudad <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="company_city" name="city"
                                        class="form-control @error('city') is-invalid @enderror"
                                        value="{{ old('city', $profile->city ?? '') }}" required
                                        placeholder="Escribe para buscar tu ciudad" autocomplete="off">
                                    @error('city')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="company_country" class="form-label">País <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="company_country" name="country" readonly
                                        class="form-control @error('country') is-invalid @enderror"
                                        value="{{ old('country', $profile->country ?? '') }}" required
                                        placeholder="Selecciona una ciudad">
                                    @error('country')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <input type="hidden" id="company_province">
                                <input type="hidden" id="company_island">

                                <div class="col-12">
                                    <label for="description" class="form-label">Descripción de la Empresa</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description"
                                        rows="4" placeholder="Describe brevemente la misión y actividad de tu empresa...">{{ old('description', $profile->description ?? '') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Una buena descripción ayuda a atraer mejor talento.</div>
                                </div>

                                <div class="col-12">
                                    <label for="sector_search" class="form-label">Sectores de actividad</label>
                                    <div class="sector-autocomplete-wrapper">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="bi bi-search"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0"
                                                name="sector_search" id="sector_search"
                                                placeholder="Escribe para buscar sectores existentes..."
                                                autocomplete="off" {{ $profile ? '' : 'disabled' }}>
                                        </div>
                                        <div class="form-text">
                                            @if ($profile)
                                                Elige sectores ya creados en el catálogo. Cada selección se guarda
                                                automáticamente.
                                            @else
                                                Guarda los datos generales antes de añadir sectores.
                                            @endif
                                        </div>
                                    </div>

                                    <div id="sector-feedback" class="mt-2"></div>

                                    <div id="selected-sectors" class="sector-tags-wrapper d-flex flex-wrap gap-2 mt-3">
                                        @forelse (($profile?->sectors ?? collect()) as $sector)
                                            @php
                                                $label = $sector->parent
                                                    ? $sector->parent->name . ' · ' . $sector->name
                                                    : $sector->name;
                                            @endphp
                                            <span class="sector-pill" data-sector-id="{{ $sector->id }}">
                                                <i class="bi bi-briefcase me-1"></i> {{ $label }}
                                                <button type="button"
                                                    class="btn-close btn-close-white ms-2 remove-sector"
                                                    data-sector-id="{{ $sector->id }}"
                                                    aria-label="Eliminar sector"></button>
                                            </span>
                                        @empty
                                            <p class="text-muted mb-0" id="no-sectors-placeholder">
                                                Aún no has agregado sectores.
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legal & Fiscal Data Card -->
                    <div class="card mb-4 mt-4">
                        <div class="card-body p-4">
                            <h2 class="form-section-title mt-0"><i
                                    class="bi bi-file-earmark-text me-2 text-primary"></i>Datos
                                Legales y Fiscales</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="legal_name" class="form-label">Razón Social <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('legal_name') is-invalid @enderror"
                                        name="legal_name" id="legal_name"
                                        value="{{ old('legal_name', $profile->legal_name ?? '') }}" required>
                                    @error('legal_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="vat_id" class="form-label">NIF / CIF <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vat_id') is-invalid @enderror"
                                        name="vat_id" id="vat_id"
                                        value="{{ old('vat_id', $profile->vat_id ?? '') }}" required>
                                    @error('vat_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="fiscal_address" class="form-label">Dirección Fiscal <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="bi bi-geo-alt"></i></span>
                                        <input type="text"
                                            class="form-control border-start-0 ps-0 @error('fiscal_address') is-invalid @enderror"
                                            name="fiscal_address" id="fiscal_address"
                                            value="{{ old('fiscal_address', $profile->fiscal_address ?? '') }}" required>
                                    </div>
                                    @error('fiscal_address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Multimedia & Internal Contact -->
                <div class="col-lg-4">
                    <!-- Multimedia Card -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h2 class="form-section-title mt-0"><i class="bi bi-images me-2 text-primary"></i>Imagen
                                Corporativa</h2>

                            <!-- Logo Preview -->
                            <div class="text-center mb-4">
                                <div class="avatar-placeholder mx-auto mb-3">
                                    @if ($profile && $profile->logo_url)
                                        <img src="{{ URL::asset($profile->logo_url) }}" alt="Logo"
                                            class="w-100 h-100 object-fit-cover rounded-circle">
                                    @else
                                        <i class="bi bi-building display-4 text-muted"></i>
                                    @endif
                                </div>
                                <small class="text-muted d-block">Previsualización del logo</small>
                            </div>

                            <div class="mb-3">
                                <label for="logo_url" class="form-label">Subir Logo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="bi bi-upload"></i></span>
                                    <input type="file" class="form-control @error('logo_url') is-invalid @enderror"
                                        name="logo_url" id="logo_url" accept="image/*">
                                </div>
                                <div class="form-text">Formatos aceptados: JPEG, PNG, JPG, GIF, SVG. Máx 2MB.</div>
                                @error('logo_url')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="video_url" class="form-label">URL Video Promocional</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="bi bi-play-btn"></i></span>
                                    <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                                        name="video_url" id="video_url"
                                        value="{{ old('video_url', $profile->video_url ?? '') }}"
                                        placeholder="Youtube / Vimeo...">
                                </div>
                                @error('video_url')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Internal Contact Card -->
                    <div class="card mb-4">
                        <div class="card-body p-4 bg-light rounded-3">
                            <h2 class="form-section-title mt-0 border-bottom-0 mb-3"><i
                                    class="bi bi-person-rolodex me-2 text-primary"></i>Contacto Interno</h2>
                            <p class="small text-muted mb-3">Estos datos son para uso administrativo y no serán públicos.
                            </p>

                            <div class="mb-3">
                                <label for="contact" class="form-label small mb-1">Persona de Contacto</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('contact') is-invalid @enderror"
                                    name="contact" id="contact" value="{{ old('contact', $profile->contact ?? '') }}">
                                @error('contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contact_phone" class="form-label small mb-1">Teléfono Directo</label>
                                <input type="text"
                                    class="form-control form-control-sm @error('contact_phone') is-invalid @enderror"
                                    name="contact_phone" id="contact_phone"
                                    value="{{ old('contact_phone', $profile->contact_phone ?? '') }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contact_email" class="form-label small mb-1">Email Interno</label>
                                <input type="email"
                                    class="form-control form-control-sm @error('contact_email') is-invalid @enderror"
                                    name="contact_email" id="contact_email"
                                    value="{{ old('contact_email', $profile->contact_email ?? '') }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                    <i class="bi bi-save me-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @php
        $sectorRoutes = [
            'store' => Route::has('empresa.profile.sectors.store') ? route('empresa.profile.sectors.store') : '',
            'search' => Route::has('empresa.profile.sectors.search') ? route('empresa.profile.sectors.search') : '',
            'destroy' => Route::has('empresa.profile.sectors.destroy')
                ? route('empresa.profile.sectors.destroy', ['sector' => '__SECTOR__'])
                : '',
        ];
    @endphp
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('sector_search');
            const selectedContainer = document.getElementById('selected-sectors');
            const feedbackContainer = document.getElementById('sector-feedback');

            if (!searchInput || !selectedContainer || !feedbackContainer) {
                return;
            }

            const canManageSectors = @json((bool) $profile);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const attachUrl = "{{ $sectorRoutes['store'] }}";
            const searchUrl = "{{ $sectorRoutes['search'] }}";
            const detachUrlTemplate = "{{ $sectorRoutes['destroy'] }}";

            const initialSectors = @json($initialSectorData->values());
            const sectorMap = new Map();
            initialSectors.forEach(sector => {
                sectorMap.set(String(sector.id), sector.label);
            });

            function renderSelectedSectors() {
                selectedContainer.innerHTML = '';

                if (sectorMap.size === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'text-muted mb-0';
                    empty.id = 'no-sectors-placeholder';
                    empty.textContent = 'Aún no has agregado sectores.';
                    selectedContainer.appendChild(empty);
                    return;
                }

                sectorMap.forEach((label, id) => {
                    const pill = document.createElement('span');
                    pill.className = 'sector-pill';
                    pill.dataset.sectorId = id;

                    const icon = document.createElement('i');
                    icon.className = 'bi bi-briefcase me-1';
                    pill.appendChild(icon);

                    pill.appendChild(document.createTextNode(label));

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn-close btn-close-white ms-2 remove-sector';
                    removeBtn.dataset.sectorId = id;
                    removeBtn.setAttribute('aria-label', 'Eliminar sector');
                    pill.appendChild(removeBtn);

                    selectedContainer.appendChild(pill);
                });
            }

            function showFeedback(message, type = 'success') {
                feedbackContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show py-2 px-3" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                `;
            }

            async function attachSector(id, label) {
                const sectorId = String(id);
                if (sectorMap.has(sectorId)) {
                    showFeedback('Ese sector ya está asociado.', 'warning');
                    return;
                }

                try {
                    const response = await fetch(attachUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            sector_id: id,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        showFeedback(data.error ?? 'No se pudo guardar el sector.', 'danger');
                        return;
                    }

                    sectorMap.set(sectorId, label);
                    renderSelectedSectors();
                    showFeedback(data.message ?? 'Sector añadido correctamente.');
                    searchInput.value = '';
                } catch (error) {
                    showFeedback('Ha ocurrido un error inesperado.', 'danger');
                }
            }

            async function detachSector(id) {
                const sectorId = String(id);

                try {
                    const response = await fetch(detachUrlTemplate.replace('__SECTOR__', sectorId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        showFeedback(data.error ?? 'No se pudo eliminar el sector.', 'danger');
                        return;
                    }

                    sectorMap.delete(sectorId);
                    renderSelectedSectors();
                    showFeedback(data.message ?? 'Sector eliminado correctamente.', 'info');
                } catch (error) {
                    showFeedback('Ha ocurrido un error inesperado.', 'danger');
                }
            }

            renderSelectedSectors();

            if (!canManageSectors) {
                return;
            }

            $('#sector_search').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: searchUrl,
                        dataType: 'json',
                        data: {
                            term: request.term,
                        },
                        success: function(data) {
                            response(data);
                        },
                        error: function() {
                            response([]);
                        },
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    event.preventDefault();
                    attachSector(ui.item.id, ui.item.label);
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    $('#sector_search').val(ui.item.label);
                },
            });

            selectedContainer.addEventListener('click', function(event) {
                const button = event.target.closest('.remove-sector');
                if (!button) {
                    return;
                }

                const sectorId = button.dataset.sectorId;
                if (sectorId) {
                    detachSector(sectorId);
                }
            });
        });
    </script>
    @include('components.location-autocomplete-script', [
        'citySelector' => '#company_city',
        'countrySelector' => '#company_country',
        'provinceSelector' => '#company_province',
        'islandSelector' => '#company_island',
    ])
@endsection
