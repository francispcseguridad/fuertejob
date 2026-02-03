@extends('layouts.app')

@section('styles')
    <style>
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0 0 0.375rem 0.375rem;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-item:hover {
            background-color: #f1f3f5;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2>Editar Mi Perfil de Trabajador</h2>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">
                            <h6 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor,
                                corrige los
                                siguientes errores:</h6>
                            <hr>
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if ($errors->has('general'))
                            <div class="alert alert-danger" role="alert">
                                <strong>Error de Servidor:</strong> {{ $errors->first('general') }}
                            </div>
                        @endif

                        @php
                            $currentCv = \App\Models\Cv::where('worker_profile_id', $profile->id)
                                ->where('is_primary', true)
                                ->latest()
                                ->first();
                        @endphp

                        <form method="POST" action="{{ route('worker.profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <h4 class="mt-4 mb-3 text-primary">Datos de Contacto y Personales</h4>
                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Nombre</label>
                                    <input id="first_name" type="text"
                                        class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                        value="{{ old('first_name', $profile->first_name) }}" required>
                                    @error('first_name')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Apellido</label>
                                    <input id="last_name" type="text"
                                        class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                                        value="{{ old('last_name', $profile->last_name) }}" required>
                                    @error('last_name')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico (Login)</label>
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">Teléfono</label>
                                    <input id="phone_number" type="text"
                                        class="form-control @error('phone_number') is-invalid @enderror" name="phone_number"
                                        value="{{ old('phone_number', $profile->phone_number) }}">
                                    @error('phone_number')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <h4 class="mt-4 mb-3 text-primary">Detalles de Ubicación y Profesional</h4>
                            <hr>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="city" class="form-label">Ciudad de Residencia</label>
                                    <input id="city" type="text"
                                        class="form-control @error('city') is-invalid @enderror" name="city"
                                        value="{{ old('city', $profile->city) }}" autocomplete="off"
                                        placeholder="Escribe para buscar...">
                                    @error('city')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label">Provincia</label>
                                    <input id="province" type="text"
                                        class="form-control @error('province') is-invalid @enderror" name="province"
                                        value="{{ old('province', $profile->province) }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="island" class="form-label">Isla</label>
                                    <input id="island" type="text"
                                        class="form-control @error('island') is-invalid @enderror" name="island"
                                        value="{{ old('island', $profile->island) }}" readonly>
                                </div>
                                <input id="country" type="hidden"
                                    class="form-control @error('country') is-invalid @enderror" name="country"
                                    value="{{ old('country', $profile->country) }}">
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <label for="professional_summary" class="form-label">Resumen Profesional</label>
                                    <textarea id="professional_summary" class="form-control @error('professional_summary') is-invalid @enderror"
                                        name="professional_summary" rows="5">{{ old('professional_summary', $profile->professional_summary) }}</textarea>
                                    @error('professional_summary')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-primary fw-bold">Sectores y Puestos de interés</label>
                                    <div class="input-group mb-2 shadow-sm">
                                        <span class="input-group-text bg-white"><i
                                                class="bi bi-search text-muted"></i></span>
                                        <input type="text" id="desired-sector-input"
                                            class="form-control border-start-0" list="sector-suggestions"
                                            placeholder="Turismo, Hostelería, Recepcionista, Técnico..."
                                            autocomplete="off">
                                        <datalist id="sector-suggestions"></datalist>
                                        <button class="btn btn-primary px-4" type="button"
                                            id="add-desired-sector">Añadir</button>
                                    </div>
                                    <div id="desired-sector-tags" class="d-flex flex-wrap gap-2 mt-3">
                                        @foreach ($profile->desiredSectors ?? [] as $sector)
                                            <span
                                                class="badge bg-primary bg-opacity-10 border border-primary-subtle rounded-pill d-inline-flex align-items-center px-3 py-2">
                                                <span class="me-2">{{ $sector->name }}</span>
                                                <input type="hidden" name="desired_sectors[]"
                                                    value="{{ $sector->name }}">
                                                <button type="button" class="btn-close btn-close-white"
                                                    aria-label="Eliminar"
                                                    onclick="this.closest('span').remove()"></button>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 text-muted x-small">
                                        <i class="bi bi-info-circle me-1"></i> Busca en nuestro catálogo de más de 1000
                                        sectores o añade tus propios puestos específicos.
                                    </div>
                                </div>
                            </div>

                            <h4 class="mt-4 mb-3 text-primary">Curriculum Vitae</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="cv_file" class="form-label">Subir CV Actualizado (PDF, DOC, DOCX - Máx
                                        5MB)</label>
                                    @php
                                        $currentCv = \App\Models\Cv::where('worker_profile_id', $profile->id)
                                            ->where('is_primary', true)
                                            ->latest()
                                            ->first();
                                    @endphp
                                    @if ($currentCv)
                                        <div class="mb-2">
                                            <small class="text-success"><i class="fas fa-check-circle"></i> Tienes un CV
                                                cargado: <strong>{{ $currentCv->file_name }}</strong></small>
                                        </div>
                                    @endif
                                    <input id="cv_file" type="file"
                                        class="form-control @error('cv_file') is-invalid @enderror" name="cv_file"
                                        accept=".pdf,.doc,.docx">
                                    @error('cv_file')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                    <small class="text-muted">Si subes un nuevo archivo, reemplazará al anterior y se
                                        re-analizará tu perfil automáticamente.</small>
                                </div>
                            </div>

                            @if ($currentCv)
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="fas fa-file-pdf me-2 text-danger"></i>Vista
                                                    previa de tu CV</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="ratio ratio-4x3">
                                                    <iframe src="{{ route('cvs.serve', $currentCv->id) }}#zoom=90"
                                                        title="Vista previa de CV" style="border: 1px solid #e9ecef;"
                                                        allowfullscreen></iframe>
                                                </div>
                                                <div class="mt-2">
                                                    <a href="{{ route('cvs.serve', $currentCv->id) }}" target="_blank"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-external-link-alt me-1"></i> Abrir en nueva
                                                        pestaña
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <h4 class="mt-4 mb-3 text-primary">Foto y Opciones</h4>
                            <hr>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="profile_picture" class="form-label">Foto de Perfil</label>

                                    @if ($profile->hasCustomProfileImage())
                                        <div class="mb-3">
                                            <img src="{{ asset($profile->profile_image_url) }}"
                                                alt="Foto de Perfil Actual"
                                                style="max-width: 150px; height: auto; border-radius: 10%; border: 3px solid #007bff; object-fit: cover;">
                                        </div>
                                    @else
                                        <small class="text-muted d-block mb-2">Aún no tienes una foto de perfil
                                            cargada.</small>
                                    @endif

                                    <input id="profile_picture" type="file"
                                        class="form-control @error('profile_picture') is-invalid @enderror"
                                        name="profile_picture" accept="image/*">
                                    @error('profile_picture')
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                    @if ($profile->profile_image_url)
                                        <small class="text-muted d-block mt-1">
                                            Sube una nueva foto para reemplazar la actual.
                                        </small>
                                    @endif
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_available"
                                            name="is_available" value="1"
                                            {{ old('is_available', $profile->is_available) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_available">Disponible para ofertas</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">

                                <a href="{{ route('worker.dashboard') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Volver al Panel
                                </a>

                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>

                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cityInput = document.getElementById('city');
            const countryInput = document.getElementById('country');
            const desiredInput = document.getElementById('desired-position-input');
            const desiredTags = document.getElementById('desired-position-tags');
            const addDesiredBtn = document.getElementById('add-desired-position');
            const positionDatalist = document.getElementById('position-suggestions');
            const desiredSectorInput = document.getElementById('desired-sector-input');
            const desiredSectorTags = document.getElementById('desired-sector-tags');
            const addDesiredSectorBtn = document.getElementById('add-desired-sector');
            const sectorDatalist = document.getElementById('sector-suggestions');

            // --- CONFIGURACIÓN LOCATIONIQ ---
            const LOCATIONIQ_KEY = 'pk.d52886ad23ebf6a01e455bb91b89bcc1';

            let resultsContainer = document.createElement('div');
            resultsContainer.className = 'autocomplete-results d-none';

            // Crear wrapper para posicionamiento relativo
            let wrapper = document.createElement('div');
            wrapper.className = 'autocomplete-container';

            // Insertamos el wrapper antes del input y movemos el input dentro
            cityInput.parentNode.insertBefore(wrapper, cityInput);
            wrapper.appendChild(cityInput);
            wrapper.appendChild(resultsContainer);

            let timeout = null;

            cityInput.addEventListener('input', function() {
                const query = this.value;

                if (query.length < 3) {
                    resultsContainer.classList.add('d-none');
                    resultsContainer.innerHTML = '';
                    return;
                }

                // Debounce para evitar muchas peticiones
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    // Usamos la API de Autocomplete de LocationIQ
                    const url =
                        `https://api.locationiq.com/v1/autocomplete?key=${LOCATIONIQ_KEY}&q=${encodeURIComponent(query)}&limit=5&tag=place:city,place:town,place:village`;

                    fetch(url)
                        .then(response => {
                            if (!response.ok) throw new Error('Error en la petición');
                            return response.json();
                        })
                        .then(data => {
                            resultsContainer.innerHTML = '';

                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.className = 'autocomplete-item';

                                    // Extraer datos útiles
                                    const address = item.address || {};
                                    const city = address.city || address.town || address
                                        .village || address.hamlet || address.name;
                                    const country = address.country || '';

                                    // MEJORA: Priorizar provincia/condado sobre estado (para casos como Canarias/Las Palmas)
                                    const province = address.province || address
                                        .county || address.state || '';

                                    let island = address.island || '';

                                    // Inferencia de isla si falta (específico para Canarias)
                                    if (!island && (province.includes('Las Palmas') ||
                                            province.includes('Santa Cruz') || address
                                            .state.includes('Canar'))) {
                                        const lowerCity = city.toLowerCase();
                                        if (lowerCity.includes('gran canaria')) island =
                                            'Gran Canaria';
                                        else if (lowerCity.includes('tenerife'))
                                            island = 'Tenerife';
                                        else if (lowerCity.includes('fuerteventura') ||
                                            lowerCity.includes('puerto del rosario'))
                                            island = 'Fuerteventura';
                                        else if (lowerCity.includes('lanzarote') ||
                                            lowerCity.includes('arrecife')) island =
                                            'Lanzarote';
                                        else if (lowerCity.includes('la palma'))
                                            island = 'La Palma';
                                        else if (lowerCity.includes('gomera')) island =
                                            'La Gomera';
                                        else if (lowerCity.includes('hierro')) island =
                                            'El Hierro';
                                    }

                                    // Formatear texto a mostrar
                                    let displayText = `<strong>${city}</strong>`;
                                    if (province) displayText +=
                                        `, <small class="text-muted">${province}</small>`;
                                    if (island) displayText +=
                                        `, <small class="text-muted d-none d-sm-inline">${island}</small>`;
                                    if (country) displayText +=
                                        `, <small class="text-muted">${country}</small>`;

                                    div.innerHTML = displayText;

                                    div.addEventListener('click', () => {
                                        // Al hacer clic, rellenamos los campos
                                        cityInput.value = city;
                                        countryInput.value = country;

                                        const pInput = document.getElementById(
                                            'province');
                                        if (pInput) pInput.value = province;

                                        const iInput = document.getElementById(
                                            'island');
                                        if (iInput) iInput.value = island;

                                        // Ocultamos resultados
                                        resultsContainer.classList.add(
                                            'd-none');
                                        resultsContainer.innerHTML = '';
                                    });

                                    resultsContainer.appendChild(div);
                                });
                                resultsContainer.classList.remove('d-none');
                            } else {
                                resultsContainer.classList.add('d-none');
                            }
                        })
                        .catch(e => {
                            console.error('LocationIQ Error:', e);
                            // No mostrar error al usuario, solo ocultar lista
                            resultsContainer.classList.add('d-none');
                        });
                }, 300);
            });

            // Cerrar lista si se hace clic fuera
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    resultsContainer.classList.add('d-none');
                }
            });

            function addDesiredPosition() {
                if (!desiredInput || !desiredTags) return;

                const value = desiredInput.value.trim();
                if (!value) return;

                // Evitar duplicados por texto
                const lowerValue = value.toLowerCase();
                const existing = Array.from(desiredTags.querySelectorAll('input[name=\"desired_positions[]\"]'))
                    .some(input => input.value.toLowerCase() === lowerValue);
                if (existing) {
                    desiredInput.value = '';
                    desiredInput.focus();
                    return;
                }

                const wrapper = document.createElement('span');
                wrapper.className =
                    'badge bg-primary bg-opacity-10 border border-primary-subtle rounded-pill d-inline-flex align-items-center px-3 py-2';

                const text = document.createElement('span');
                text.className = 'me-2';
                text.textContent = value;

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'desired_positions[]';
                hidden.value = value;

                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn-close btn-close-white';
                closeBtn.setAttribute('aria-label', 'Eliminar');
                closeBtn.addEventListener('click', () => wrapper.remove());

                wrapper.appendChild(text);
                wrapper.appendChild(hidden);
                wrapper.appendChild(closeBtn);

                desiredTags.appendChild(wrapper);
                desiredInput.value = '';
                desiredInput.focus();
            }

            if (addDesiredBtn) {
                addDesiredBtn.addEventListener('click', addDesiredPosition);
            }

            if (desiredInput) {
                desiredInput.addEventListener('input', (e) => {
                    const term = e.target.value;
                    if (term.length >= 2) {
                        fetch(`{{ route('puestos.search.worker') }}?term=${encodeURIComponent(term)}`)
                            .then(resp => resp.ok ? resp.json() : [])
                            .then(data => {
                                if (!positionDatalist) return;
                                positionDatalist.innerHTML = '';
                                data.forEach(name => {
                                    const option = document.createElement('option');
                                    option.value = name;
                                    positionDatalist.appendChild(option);
                                });
                            })
                            .catch(() => {});
                    } else if (positionDatalist) {
                        positionDatalist.innerHTML = '';
                    }
                });
                desiredInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addDesiredPosition();
                    }
                });
            }

            // Sectores deseados
            function addDesiredSector() {
                if (!desiredSectorInput || !desiredSectorTags) return;

                const value = desiredSectorInput.value.trim();
                if (!value) return;

                const lowerValue = value.toLowerCase();
                const exists = Array.from(desiredSectorTags.querySelectorAll('input[name="desired_sectors[]"]'))
                    .some(input => input.value.toLowerCase() === lowerValue);
                if (exists) {
                    desiredSectorInput.value = '';
                    desiredSectorInput.focus();
                    return;
                }

                const wrapper = document.createElement('span');
                wrapper.className =
                    'badge bg-dark bg-opacity-10 text-dark border border-dark-subtle rounded-pill d-inline-flex align-items-center px-3 py-2';

                const text = document.createElement('span');
                text.className = 'me-2';
                text.textContent = value;

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'desired_sectors[]';
                hidden.value = value;

                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn-close btn-close-white';
                closeBtn.setAttribute('aria-label', 'Eliminar');
                closeBtn.addEventListener('click', () => wrapper.remove());

                wrapper.appendChild(text);
                wrapper.appendChild(hidden);
                wrapper.appendChild(closeBtn);

                desiredSectorTags.appendChild(wrapper);
                desiredSectorInput.value = '';
                desiredSectorInput.focus();
            }

            async function fetchSectors(term) {
                try {
                    const resp = await fetch(
                        `{{ route('sectores.search.worker') }}?term=${encodeURIComponent(term)}`);
                    if (!resp.ok) return;
                    const data = await resp.json();
                    sectorDatalist.innerHTML = '';
                    data.forEach(name => {
                        const option = document.createElement('option');
                        option.value = name;
                        sectorDatalist.appendChild(option);
                    });
                } catch (e) {
                    console.error('Error cargando sectores', e);
                }
            }

            if (addDesiredSectorBtn) {
                addDesiredSectorBtn.addEventListener('click', addDesiredSector);
            }
            if (desiredSectorInput) {
                desiredSectorInput.addEventListener('input', (e) => {
                    const term = e.target.value;
                    if (term.length >= 2) {
                        fetchSectors(term);
                    } else {
                        sectorDatalist.innerHTML = '';
                    }
                });
                desiredSectorInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addDesiredSector();
                    }
                });
            }
        });
    </script>
@endsection
