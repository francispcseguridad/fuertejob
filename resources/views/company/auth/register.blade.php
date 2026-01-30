@extends('layouts.app')

@section('styles')
    <style>
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

        .company-highlight {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
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
                            <div class="col-lg-5 d-none d-lg-block">
                                <div class="p-5 h-100 d-flex flex-column text-white"
                                    style="background: #1f496d; border-radius: 0.25rem 0 0 0.25rem;">
                                    <div class="mt-4 text-center">
                                        <h2 class="display-6 fw-bold mb-3">Impulsa tu Atracción de Talento</h2>
                                        <p class="lead mb-4">
                                            Gestiona tus vacantes con analítica avanzada y haz match con profesionales
                                            preseleccionados en minutos.
                                        </p>
                                    </div>
                                    <div class="mt-4 company-highlight p-3 shadow-sm">
                                        <p class="small mb-2"><i class="bi bi-lightning-charge me-2"></i>Automatiza filtros
                                            por seniority, stack tecnológico y disponibilidad.</p>
                                        <p class="small mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Accede a reportes de
                                            desempeño de tus vacantes en tiempo real.</p>
                                    </div>
                                    <div class="w-100 text-start mt-auto mb-4">
                                        <h5 class="fw-bold border-bottom border-light pb-2 mb-3">
                                            <i class="bi bi-question-circle me-2"></i>FAQs para Empresas
                                        </h5>
                                        <div class="accordion accordion-flush custom-scrollbar" id="companyFaqAccordion"
                                            style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                                            <div class="text-center py-2 text-white-50 small">
                                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                                Cargando preguntas corporativas...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7 p-4 p-md-5">
                                <h4 class="card-title text-center mb-4" style="color: #1f496d;">Crea tu Perfil de Empresa
                                </h4>
                                <p class="text-muted text-center small mb-4">Completa los datos y comienza a publicar
                                    vacantes con respaldo de IA.</p>

                                @if (session('status'))
                                    <div class="alert alert-success mb-4" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                @php
                                    $fieldLabelMap = [
                                        'name' => 'Nombre completo',
                                        'email' => 'Email (usuario)',
                                        'password' => 'Contraseña',
                                        'password_confirmation' => 'Confirmar contraseña',
                                        'company_name' => 'Nombre comercial',
                                        'legal_name' => 'Razón social',
                                        'vat_id' => 'NIF / CIF / VAT ID',
                                        'fiscal_address' => 'Dirección fiscal',
                                        'description' => 'Descripción',
                                        'phone' => 'Teléfono público',
                                        'company_email' => 'Email público',
                                        'website_url' => 'Sitio web',
                                        'video_url' => 'Vídeo promocional',
                                        'logo_url' => 'Logotipo',
                                        'contact_phone' => 'Teléfono de contacto',
                                        'math_captcha' => 'Verificación de seguridad',
                                        'accept_privacy_policy' => 'Política de privacidad',
                                        'accept_terms' => 'Condiciones generales',
                                        'sectors' => 'Sectores',
                                        'error' => 'Registro',
                                    ];
                                @endphp

                                @if ($errors->any())
                                    <div class="alert alert-danger mb-4" role="alert">
                                        <h6 class="fw-bold mb-2">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor revisa los
                                            siguientes campos:
                                        </h6>
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

                                <form action="{{ route('company.register.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Datos de la Persona de Contacto
                                    </h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Nombre Completo<span
                                                    class="required-indicator">*</span></label>
                                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                                required autofocus class="form-control @error('name') is-invalid @enderror"
                                                placeholder="Ej: Javier García">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email (Usuario)<span
                                                    class="required-indicator">*</span></label>
                                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                                required class="form-control @error('email') is-invalid @enderror"
                                                placeholder="Ej: contacto@empresa.com">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">Contraseña<span
                                                    class="required-indicator">*</span></label>
                                            <input type="password" id="password" name="password" required
                                                class="form-control @error('password') is-invalid @enderror">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password_confirmation" class="form-label">Confirmar
                                                Contraseña<span class="required-indicator">*</span></label>
                                            <input type="password" id="password_confirmation" name="password_confirmation"
                                                required class="form-control">
                                        </div>
                                    </div>

                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Datos Legales de la Empresa</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="company_name" class="form-label">Nombre Comercial<span
                                                    class="required-indicator">*</span></label>
                                            <input type="text" id="company_name" name="company_name"
                                                value="{{ old('company_name') }}" required
                                                class="form-control @error('company_name') is-invalid @enderror"
                                                placeholder="Ej: InnovaTech">
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12">
                                            <label for="legal_name" class="form-label">Razón Social<span
                                                    class="required-indicator">*</span></label>
                                            <input type="text" id="legal_name" name="legal_name"
                                                value="{{ old('legal_name') }}" required
                                                class="form-control @error('legal_name') is-invalid @enderror"
                                                placeholder="Ej: INNOVATECH S.L.">
                                            @error('legal_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label for="vat_id" class="form-label">NIF / CIF / VAT ID<span
                                                    class="required-indicator">*</span></label>
                                            <input type="text" id="vat_id" name="vat_id"
                                                value="{{ old('vat_id') }}" required
                                                class="form-control @error('vat_id') is-invalid @enderror"
                                                placeholder="B12345678">
                                            @error('vat_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label for="fiscal_address" class="form-label">Dirección Fiscal
                                                Completa<span class="required-indicator">*</span></label>
                                            <textarea id="fiscal_address" name="fiscal_address" rows="2" required
                                                class="form-control @error('fiscal_address') is-invalid @enderror" placeholder="Calle Falsa 123, 28001 Madrid">{{ old('fiscal_address') }}</textarea>
                                            @error('fiscal_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="company_city" class="form-label">Ciudad<span
                                                    class="required-indicator">*</span></label>
                                            <input type="text" id="company_city" name="city"
                                                value="{{ old('city') }}" required autocomplete="off"
                                                class="form-control @error('city') is-invalid @enderror"
                                                placeholder="Escribe para buscar municipios">
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="company_country" class="form-label">País<span
                                                    class="required-indicator">*</span></label>
                                            <input type="text" id="company_country" name="country"
                                                value="{{ old('country') }}" required readonly
                                                class="form-control @error('country') is-invalid @enderror"
                                                placeholder="Selecciona una ciudad">
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <input type="hidden" id="company_province" name="province"
                                            value="{{ old('province') }}">
                                        <input type="hidden" id="company_island" name="island"
                                            value="{{ old('island') }}">
                                    </div>

                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Información Pública</h6>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <label for="description" class="form-label">Descripción de la Empresa</label>
                                            <textarea id="description" name="description" rows="3"
                                                class="form-control @error('description') is-invalid @enderror" placeholder="Breve descripción de tu actividad">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="phone" class="form-label">Teléfono Público</label>
                                            <input type="text" id="phone" name="phone"
                                                value="{{ old('phone') }}"
                                                class="form-control @error('phone') is-invalid @enderror"
                                                placeholder="912345678">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="company_email" class="form-label">Email Público</label>
                                            <input type="email" id="company_email" name="company_email"
                                                value="{{ old('company_email') }}"
                                                class="form-control @error('company_email') is-invalid @enderror"
                                                placeholder="info@empresa.com">
                                            @error('company_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="website_url" class="form-label">Sitio Web</label>
                                            <input type="url" id="website_url" name="website_url"
                                                value="{{ old('website_url') }}"
                                                class="form-control @error('website_url') is-invalid @enderror"
                                                placeholder="https://www.empresa.com">
                                            @error('website_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="video_url" class="form-label">Video Promocional (URL)</label>
                                            <input type="url" id="video_url" name="video_url"
                                                value="{{ old('video_url') }}"
                                                class="form-control @error('video_url') is-invalid @enderror"
                                                placeholder="https://youtube.com/...">
                                            @error('video_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3">
                                            <label for="logo_url" class="form-label">Logotipo</label>
                                            <input type="file" id="logo_url" name="logo_url" accept="image/*"
                                                class="form-control @error('logo_url') is-invalid @enderror">
                                            <small class="text-muted">Formatos permitidos: PNG, JPG, GIF. Máx 2MB.</small>
                                            @error('logo_url')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Datos de Contacto Adicionales
                                    </h6>
                                    <div class="bg-light border rounded p-3 mb-4">
                                        <label for="contact_phone" class="form-label">Teléfono de Contacto
                                            (Opcional)</label>
                                        <input type="text" id="contact_phone" name="contact_phone"
                                            value="{{ old('contact_phone') }}"
                                            class="form-control @error('contact_phone') is-invalid @enderror"
                                            placeholder="Número directo para candidatos">
                                        @error('contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Puedes mostrarlo en tus ofertas para agilizar la
                                            comunicación.</small>
                                    </div>

                                    <h6 class="text-secondary mt-3 mb-3 border-bottom pb-1">Verificación de Seguridad</h6>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-6">
                                            <label for="math_captcha" class="form-label">Resuelve la operación:
                                                <strong>{{ $num1 }} + {{ $num2 }}</strong><span
                                                    class="required-indicator">*</span></label>
                                            <input type="number" id="math_captcha" name="math_captcha" required
                                                class="form-control @error('math_captcha') is-invalid @enderror"
                                                placeholder="Respuesta">
                                            @error('math_captcha')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted d-block mt-2">Introduce el resultado para verificar
                                                que no eres un robot.</small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="form-check mb-2">
                                                <input
                                                    class="form-check-input @error('accept_privacy_policy') is-invalid @enderror"
                                                    type="checkbox" id="accept_privacy_policy"
                                                    name="accept_privacy_policy" value="1" required
                                                    {{ old('accept_privacy_policy') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="accept_privacy_policy">
                                                    Acepto la <a
                                                        href="https://www.fuertejob.com/info/politica-de-privacidad"
                                                        target="_blank"
                                                        class="text-decoration-underline text-info">Política de
                                                        Privacidad</a>.<span class="required-indicator">*</span>
                                                </label>
                                                @error('accept_privacy_policy')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input @error('accept_terms') is-invalid @enderror"
                                                    type="checkbox" id="accept_terms" name="accept_terms" value="1"
                                                    required {{ old('accept_terms') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="accept_terms">
                                                    Acepto las <a
                                                        href="https://www.fuertejob.com/info/condiciones-generales-de-uso-y-contratacion"
                                                        target="_blank"
                                                        class="text-decoration-underline text-info">Condiciones Generales
                                                        de Uso y
                                                        Contratación</a>.<span class="required-indicator">*</span>
                                                </label>
                                                @error('accept_terms')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4 align-items-center">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <a class="text-muted small" href="{{ route('login') }}">¿Ya tienes cuenta?
                                                Inicia sesión aquí</a>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <button type="submit" class="btn btn-lg px-4 text-white"
                                                style="background-color: #1f496d;">Registrar
                                                Empresa</button>
                                        </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqContainer = document.getElementById('companyFaqAccordion');
            const defaultFaqs = [{
                    question: '¿Cómo publico mi primera oferta?',
                    answer: 'Completa tu perfil, accede al panel de empresa y haz clic en “Publicar Oferta”. Podrás definir requisitos, modalidad y beneficios.'
                },
                {
                    question: '¿Puedo pausar una vacante?',
                    answer: 'Sí. Desde el panel de vacantes selecciona la oferta y haz clic en “Pausar”. Podrás reactivarla cuando quieras sin perder candidatos.'
                },
                {
                    question: '¿Qué incluye el análisis con IA?',
                    answer: 'Nuestro motor revisa CVs, habilidades y experiencia para mostrarte rankings y coincidencias recomendadas para cada oferta.'
                }
            ];

            const renderFaqs = (faqs) => {
                faqContainer.innerHTML = '';

                faqs.forEach((faq, index) => {
                    const headingId = `company-heading${index}`;
                    const collapseId = `company-collapse${index}`;
                    const itemHtml = `
                        <div class="accordion-item bg-transparent border-0 mb-2">
                            <h2 class="accordion-header" id="${headingId}">
                                <button class="accordion-button collapsed bg-white bg-opacity-10 text-white rounded shadow-sm fs-6 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}" style="backdrop-filter: blur(5px);">
                                    <i class="bi bi-briefcase-fill me-2 small"></i> ${faq.question}
                                </button>
                            </h2>
                            <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#companyFaqAccordion">
                                <div class="accordion-body text-white-50 small pt-2 pb-3 ps-4">
                                    ${faq.answer}
                                </div>
                            </div>
                        </div>
                    `;
                    faqContainer.insertAdjacentHTML('beforeend', itemHtml);
                });
            };

            fetch('{{ route('api.faqs.company') }}')
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        renderFaqs(data);
                    } else {
                        renderFaqs(defaultFaqs);
                    }
                })
                .catch(error => {
                    console.error('Error fetching company FAQs:', error);
                    renderFaqs(defaultFaqs);
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
