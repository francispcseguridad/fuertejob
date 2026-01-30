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

        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.4) rgba(255, 255, 255, 0.1);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.4);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.6);
        }

        .required-indicator {
            color: #dc3545;
            margin-left: 0.25rem;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- COLUMNA IZQUIERDA: IMAGEN INSPIRADORA -->
                            <div class="col-lg-5 d-none d-lg-block">
                                <div class="p-5 h-100 d-flex flex-column justify-content-start align-items-center text-white"
                                    style="background: #1f496d; border-radius: 0.25rem 0 0 0.25rem;">
                                    <div class="mt-5 mb-4 text-center">
                                        <h2 class="display-6 fw-bold mb-3">¡Da el salto a tu nuevo reto!</h2>
                                        <p class="lead mb-4">
                                            Regístrate en nuestro portal de talento. Utilizamos Inteligencia Artificial
                                            (Gemini)
                                            para analizar tu CV y encontrar el ajuste perfecto.
                                        </p>
                                    </div>

                                    <!-- SECTION FAQ -->
                                    <div class="w-100 text-start mt-auto mb-4">
                                        <h5 class="fw-bold border-bottom border-light pb-2 mb-3">
                                            <i class="bi bi-question-circle me-2"></i>Preguntas Frecuentes
                                        </h5>
                                        <div class="accordion accordion-flush custom-scrollbar" id="faqAccordion"
                                            style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                                            <!-- Las preguntas se cargarán aquí vía AJAX -->
                                            <div class="text-center py-2 text-white-50 small">
                                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                                Cargando dudas...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMNA DERECHA: FORMULARIO DE REGISTRO -->
                            <div class="col-lg-7 p-4 p-md-5">
                                <h4 class="card-title text-center mb-4" style="color: #1f496d;">Crea tu Cuenta de Candidat@
                                </h4>

                                {{-- 1. BLOQUE DE ERRORES GLOBALES Y TÉCNICOS --}}
                                @php
                                    $fieldLabelMap = [
                                        'first_name' => 'Nombre',
                                        'last_name' => 'Apellido',
                                        'phone_number' => 'Teléfono',
                                        'email' => 'Correo electrónico',
                                        'password' => 'Contraseña',
                                        'password_confirmation' => 'Confirmar contraseña',
                                        'cv_file' => 'CV',
                                        'city' => 'Ciudad',
                                        'country' => 'País',
                                        'preferred_modality' => 'Modalidad preferida',
                                        'min_expected_salary' => 'Salario esperado',
                                        'data_veracity' => 'Compromiso de veracidad',
                                        'accept_privacy_policy' => 'Política de privacidad',
                                        'accept_terms' => 'Condiciones generales',
                                        'math_captcha' => 'Verificación de seguridad',
                                        'registro_fallido' => 'Registro',
                                        'error' => 'Registro',
                                    ];
                                @endphp

                                @if ($errors->any())
                                    <div class="alert alert-danger mb-4" role="alert">
                                        <h6 class="alert-heading fw-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor, corrige los
                                            siguientes errores:
                                        </h6>
                                        <hr>
                                        <ul class="mb-0 small">
                                            @foreach ($errors->getMessages() as $field => $messages)
                                                @foreach ($messages as $message)
                                                    <li>
                                                        <strong>{{ $fieldLabelMap[$field] ?? ucwords(str_replace('_', ' ', $field)) }}:</strong>
                                                        {{ $message }}
                                                    </li>
                                                @endforeach
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($errors->has('registro_fallido'))
                                    <div class="alert alert-danger mb-4 small" role="alert">
                                        <p class="mb-1 fw-bold">Hubo un fallo en la transacción de registro.</p>
                                        <p class="mb-0">Por favor, inténtalo de nuevo. Detalle:
                                            {{ $errors->first('registro_fallido') }}</p>
                                    </div>
                                @endif

                                <form id="registrationForm" method="POST" action="{{ route('worker.register.submit') }}"
                                    enctype="multipart/form-data">
                                    @csrf

                                    {{-- DATOS PERSONALES --}}
                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Datos Básicos</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">Nombre<span
                                                    class="required-indicator">*</span></label>
                                            <input id="first_name" type="text"
                                                class="form-control @error('first_name') is-invalid @enderror"
                                                name="first_name" value="{{ old('first_name') }}" required
                                                autocomplete="first_name">
                                            @error('first_name')
                                                <span class="invalid-feedback" role="alert"><strong>❌
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Apellido<span
                                                    class="required-indicator">*</span></label>
                                            <input id="last_name" type="text"
                                                class="form-control @error('last_name') is-invalid @enderror"
                                                name="last_name" value="{{ old('last_name') }}" required
                                                autocomplete="last_name">
                                            @error('last_name')
                                                <span class="invalid-feedback" role="alert"><strong>❌
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- DATOS DE CONTACTO Y CUENTA --}}
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="phone_number" class="form-label">Teléfono (Opcional)</label>
                                            <input id="phone_number" type="text"
                                                class="form-control @error('phone_number') is-invalid @enderror"
                                                name="phone_number" value="{{ old('phone_number') }}"
                                                autocomplete="phone_number">
                                            @error('phone_number')
                                                <span class="invalid-feedback" role="alert"><strong>❌
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Correo Electrónico<span
                                                    class="required-indicator">*</span></label>
                                            <input id="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror" name="email"
                                                value="{{ old('email') }}" required autocomplete="email">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert"><strong>❌
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- CONTRASEÑA --}}
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Contraseña<span
                                                    class="required-indicator">*</span></label>
                                            <div class="input-group">
                                                <input id="password" type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" required autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                                    data-target="password" aria-label="Mostrar contraseña">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert"><strong>❌
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password-confirm" class="form-label">Confirmar Contraseña<span
                                                    class="required-indicator">*</span></label>
                                            <div class="input-group">
                                                <input id="password-confirm" type="password" class="form-control"
                                                    name="password_confirmation" required autocomplete="new-password">
                                                <button type="button"
                                                    class="btn btn-outline-secondary btn-password-toggle"
                                                    data-target="password-confirm" aria-label="Mostrar contraseña">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- CV FILE UPLOAD --}}
                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Tu Currículum Vitae</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <label for="cv_file" class="form-label">📄 Sube tu CV (PDF/DOCX)<span
                                                    class="required-indicator">*</span></label>
                                            <p class="form-text text-muted mb-2 small">Usaremos IA para analizar tu
                                                experiencia.</p>
                                            <input id="cv_file" type="file"
                                                class="form-control @error('cv_file') is-invalid @enderror" name="cv_file"
                                                required>
                                            @error('cv_file')
                                                <span class="invalid-feedback" role="alert"><strong>❌ Error en el Archivo:
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- UBICACIÓN --}}
                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Ubicación</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="city" class="form-label">Ciudad<span
                                                    class="required-indicator">*</span></label>
                                            <input id="city" type="text" placeholder="Escribe para buscar..."
                                                class="form-control @error('city') is-invalid @enderror" name="city"
                                                value="{{ old('city') }}" required autocomplete="off">
                                            @error('city')
                                                <span class="invalid-feedback" role="alert"><strong>❌
                                                        {{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <input id="country" type="hidden"
                                            class="form-control @error('country') is-invalid @enderror" name="country"
                                            value="{{ old('country') }}" required autocomplete="country-name">
                                        <input id="province" type="hidden" name="province"
                                            value="{{ old('province') }}">
                                        <input id="island" type="hidden" name="island"
                                            value="{{ old('island') }}">
                                    </div>



                                    {{-- COMPROMISO LEGAL --}}
                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Compromiso Legal</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-check mb-2">
                                                <input
                                                    class="form-check-input @error('accept_privacy_policy') is-invalid @enderror"
                                                    type="checkbox" name="accept_privacy_policy"
                                                    id="accept_privacy_policy" value="1" required
                                                    {{ old('accept_privacy_policy') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="accept_privacy_policy">
                                                    Acepto la <a
                                                        href="https://www.fuertejob.com/info/politica-de-privacidad"
                                                        target="_blank"
                                                        class="text-decoration-underline text-primary">Política de
                                                        Privacidad</a>.<span class="required-indicator">*</span>
                                                </label>
                                                @error('accept_privacy_policy')
                                                    <div class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </div>
                                                @enderror
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input @error('accept_terms') is-invalid @enderror"
                                                    type="checkbox" name="accept_terms" id="accept_terms" value="1"
                                                    required {{ old('accept_terms') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="accept_terms">
                                                    Acepto las <a
                                                        href="https://www.fuertejob.com/info/condiciones-generales-de-uso-y-contratacion"
                                                        target="_blank"
                                                        class="text-decoration-underline text-primary">Condiciones
                                                        Generales de Uso y Contratación</a>.<span
                                                        class="required-indicator">*</span>
                                                </label>
                                                @error('accept_terms')
                                                    <div class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="form-check">
                                                <input
                                                    class="form-check-input @error('data_veracity') is-invalid @enderror"
                                                    type="checkbox" name="data_veracity" id="data_veracity"
                                                    value="1" required {{ old('data_veracity') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="data_veracity">
                                                    Confirmo que mis datos y CV son veraces y están actualizados.<span
                                                        class="required-indicator">*</span>
                                                </label>
                                                @error('data_veracity')
                                                    <div class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- VERIFICACIÓN DE SEGURIDAD --}}
                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Verificación de Seguridad</h6>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-6">
                                            <label for="math_captcha" class="form-label">Resuelve la operación:
                                                <strong>{{ $num1 ?? 0 }} + {{ $num2 ?? 0 }}</strong><span
                                                    class="required-indicator">*</span></label>
                                            <input type="number" id="math_captcha" name="math_captcha"
                                                value="{{ old('math_captcha') }}"
                                                class="form-control @error('math_captcha') is-invalid @enderror"
                                                placeholder="Respuesta" required>
                                            @error('math_captcha')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted d-block mt-2">Introduce el resultado para verificar
                                                que no eres un robot.</small>
                                        </div>
                                    </div>

                                    {{-- BOTÓN DE REGISTRO --}}
                                    <div class="row mt-4">
                                        <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-lg w-100 text-white"
                                                style="background-color: #1f496d;">Registrarme y
                                                Analizar CV</button>
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a class="text-muted small" href="{{ route('login') }}">¿Ya tienes cuenta? Inicia
                                            sesión aquí</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registrationForm = document.getElementById('registrationForm');

            if (registrationForm) {
                registrationForm.addEventListener('submit', function(event) {
                    if (!this.checkValidity()) {
                        return;
                    }

                    if (!citySelectedFromAutocomplete) {
                        event.preventDefault();
                        showCitySelectionError();
                        cityInput.focus();
                        return;
                    }

                    Swal.fire({
                        title: '¡Analizando tu Perfil!',
                        html: `
                        <div class="text-start fs-6">
                            <p>Estamos subiendo tu CV y procesándolo con nuestra <strong>Inteligencia Artificial</strong>.</p>
                            <p class="mb-0 text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i> Por favor, NO cierres ni recargues esta página.</p>
                            <p class="small text-muted mt-2">Este proceso puede tardar entre 1 y 2 minutos mientras extraemos tus habilidades.</p>
                        </div>
                    `,
                        icon: 'info',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
            }

            const cityInput = document.getElementById('city');
            const countryInput = document.getElementById('country');
            const provinceInput = document.getElementById('province');
            const islandInput = document.getElementById('island');
            const citySelectionMessage =
                'Selecciona una ciudad de las sugerencias para completar país y provincia.';

            let resultsContainer = document.createElement('div');
            resultsContainer.className = 'autocomplete-results d-none';

            let wrapper = document.createElement('div');
            wrapper.className = 'autocomplete-container';

            cityInput.parentNode.insertBefore(wrapper, cityInput);
            wrapper.appendChild(cityInput);
            wrapper.appendChild(resultsContainer);

            let timeout = null;
            let citySelectedFromAutocomplete = Boolean(countryInput.value);

            const renderSuggestions = (items) => {
                resultsContainer.innerHTML = '';
                const unique = new Set();
                items.forEach(item => {
                    if (!item.city) return;
                    const key =
                        `${item.city}|${item.province || ''}|${item.island || ''}|${item.country || ''}`;
                    if (unique.has(key)) return;
                    unique.add(key);

                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    let displayText = `<strong>${item.city}</strong>`;
                    if (item.province) displayText +=
                        `, <small class="text-muted">${item.province}</small>`;
                    if (item.island) displayText +=
                        `, <small class="text-muted d-none d-sm-inline">${item.island}</small>`;
                    if (item.country) displayText +=
                        `, <small class="text-muted">${item.country}</small>`;
                    div.innerHTML = displayText;

                    const applySelection = () => {
                        cityInput.value = item.city;
                        countryInput.value = item.country || '';
                        provinceInput.value = item.province || '';
                        islandInput.value = item.island || '';
                        citySelectedFromAutocomplete = true;
                        cityInput.setCustomValidity('');
                        resultsContainer.classList.add('d-none');
                        resultsContainer.innerHTML = '';
                    };

                    // mousedown evita que el blur del input cierre antes de seleccionar
                    div.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        applySelection();
                    });

                    div.addEventListener('click', applySelection);

                    resultsContainer.appendChild(div);
                });

                if (unique.size > 0) {
                    resultsContainer.classList.remove('d-none');
                } else {
                    resultsContainer.classList.add('d-none');
                }
            };

            cityInput.addEventListener('input', function() {
                citySelectedFromAutocomplete = false;
                countryInput.value = '';
                provinceInput.value = '';
                islandInput.value = '';
                cityInput.setCustomValidity('');

                const query = this.value;

                if (query.length < 3) {
                    resultsContainer.classList.add('d-none');
                    resultsContainer.innerHTML = '';
                    return;
                }

                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const localUrl = `/api/localidades/search?q=${encodeURIComponent(query)}`;

                    fetch(localUrl)
                        .then(r => r.ok ? r.json() : [])
                        .then(localData => {
                            const formattedLocal = Array.isArray(localData) ? localData.map(
                                item => ({
                                    city: item.city,
                                    province: item.province,
                                    island: item.island,
                                    country: item.country || 'España',
                                })) : [];

                            renderSuggestions(formattedLocal);
                        })
                        .catch(e => {
                            console.error('Autocomplete Error:', e);
                            resultsContainer.classList.add('d-none');
                        });
                }, 300);
            });

            const showCitySelectionError = () => {
                cityInput.setCustomValidity(citySelectionMessage);
                cityInput.reportValidity();
            };

            cityInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && !citySelectedFromAutocomplete) {
                    event.preventDefault();
                    showCitySelectionError();
                }
            });

            cityInput.addEventListener('blur', function() {
                if (cityInput.value.trim().length > 0 && !citySelectedFromAutocomplete) {
                    showCitySelectionError();
                }
            });

            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    resultsContainer.classList.add('d-none');
                }
            });

            fetch('{{ route('api.faqs.worker') }}')
                .then(response => response.json())
                .then(data => {
                    const faqContainer = document.getElementById('faqAccordion');
                    faqContainer.innerHTML = '';

                    if (data.length === 0) {
                        faqContainer.innerHTML =
                            '<p class="text-center text-white-50 small">No hay preguntas frecuentes disponibles.</p>';
                        return;
                    }

                    data.forEach((faq, index) => {
                        const headingId = `flush-heading${index}`;
                        const collapseId = `flush-collapse${index}`;

                        const itemHtml = `
                            <div class="accordion-item bg-transparent border-0 mb-2">
                                <h2 class="accordion-header" id="${headingId}">
                                    <button class="accordion-button collapsed bg-white bg-opacity-10 text-white rounded shadow-sm fs-6 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}" style="backdrop-filter: blur(5px);">
                                        <i class="bi bi-chevron-right me-2 small"></i> ${faq.question}
                                    </button>
                                </h2>
                                <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-white-50 small pt-2 pb-3 ps-4">
                                        ${faq.answer}
                                    </div>
                                </div>
                            </div>
                        `;
                        faqContainer.insertAdjacentHTML('beforeend', itemHtml);
                    });
                })
                .catch(error => {
                    console.error('Error fetching FAQs:', error);
                    const faqContainer = document.getElementById('faqAccordion');
                    faqContainer.innerHTML =
                        '<p class="text-center text-white-50 small">No se pudieron cargar las preguntas.</p>';
                });
        });
    </script>
@endsection
