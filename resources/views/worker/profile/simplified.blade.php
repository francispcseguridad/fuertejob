@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        :root {
            --fj-primary: #2563eb;
            --fj-secondary: #64748b;
            --fj-success: #10b981;
            --fj-warning: #f59e0b;
        }

        body {
            background-color: #f8fafc;
        }

        /* Estética de Tarjetas */
        .card-profile {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: #ffffff;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Repeater Items: Más limpios */
        .repeater-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            position: relative;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .repeater-item:hover {
            border-color: var(--fj-primary);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1);
        }

        .btn-remove {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ef4444;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 20;
            transition: transform 0.2s;
        }

        .btn-remove:hover {
            transform: scale(1.1);
            background: #dc2626;
        }

        /* Estilo de Tags Modernos */
        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            padding: 1rem;
            background: #f1f5f9;
            border-radius: 0.75rem;
            min-height: 60px;
            border: 1px dashed #cbd5e1;
        }

        .tag-item {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.3s ease;
        }

        .tag-skill,
        .tag-desired {
            background: #dbeafe;
            color: #1e40af;
        }

        .tag-tool,
        .tag-sector {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .tag-lang {
            background: #fef2f2;
            color: #214b6e;
        }

        .btn-close-tag {
            margin-left: 0.5rem;
            cursor: pointer;
            opacity: 0.6;
            font-size: 1.1rem;
        }

        .btn-close-tag:hover {
            opacity: 1;
        }

        /* Floating Sidebar */
        .sticky-summary {
            position: sticky;
            top: 2rem;
        }

        .form-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: #64748b;
            font-weight: 700;
        }

        .form-control:focus {
            border-color: var(--fj-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        <form action="{{ route('worker.profile.simplified.update') }}" method="POST" id="fullProfileForm">
            @csrf
            <div class="row g-4">
                {{-- Formulario Principal --}}
                <div class="col-lg-8">
                    <header class="mb-5">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('worker.dashboard') }}" class="btn btn-white shadow-sm rounded-circle p-2">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <div>
                                <h1 class="h3 fw-bold mb-0 text-slate-900">Configura tu Perfil</h1>
                                <p class="text-muted mb-0">Completa tu trayectoria para destacar ante las empresas.</p>
                            </div>
                        </div>
                    </header>

                    {{-- PREFERENCIAS DE PUESTO Y SECTOR --}}
                    <section class="card-profile p-4 mb-5">
                        <h2 class="section-title mb-4"><i class="bi bi-flag text-info"></i> ¿Qué buscas?</h2>
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">Sectores y Puestos de interés</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="bi bi-search text-muted"></i></span>
                                    <input type="text" id="sector-input" class="form-control border-start-0 ps-0"
                                        placeholder="Turismo, Hostelería, Recepcionista, Técnico...">
                                    <button class="btn btn-primary px-4" type="button"
                                        onclick="addTag('sector')">Añadir</button>
                                </div>
                                <div id="sector-tags" class="tag-container">
                                    @foreach ($profile->desiredSectors ?? collect() as $sector)
                                        <div class="tag-item tag-sector">
                                            {{ $sector->name }}
                                            <input type="hidden" name="desired_sectors[]" value="{{ $sector->name }}">
                                            <i class="bi bi-x-lg btn-close-tag" onclick="this.parentElement.remove()"></i>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Busca en nuestro catálogo de más de 1000 sectores o añade uno
                                    nuevo.</small>
                            </div>
                        </div>
                    </section>

                    {{-- EXPERIENCIA --}}
                    <section class="mb-5">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h2 class="section-title"><i class="bi bi-briefcase text-primary"></i> Experiencia Laboral</h2>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                                <span id="exp-count">0</span> registradas
                            </span>
                        </div>

                        <div id="experience-container">
                            @foreach ($profile->experiences as $index => $exp)
                                @include('worker.profile.partials.exp_row', [
                                    'index' => $index,
                                    'exp' => $exp,
                                ])
                            @endforeach
                        </div>

                        <button type="button"
                            class="btn btn-outline-primary w-100 py-3 rounded-4 border-2 border-dashed fw-bold"
                            onclick="addExperience()">
                            <i class="bi bi-plus-circle-dotted me-2"></i> Añadir puesto de trabajo
                        </button>
                    </section>

                    {{-- EDUCACIÓN --}}
                    <section class="mb-5">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h2 class="section-title"><i class="bi bi-mortarboard text-success"></i> Formación</h2>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                <span id="edu-count">0</span> títulos
                            </span>
                        </div>

                        <div id="education-container">
                            @foreach ($profile->educations as $index => $edu)
                                @include('worker.profile.partials.edu_row', [
                                    'index' => $index,
                                    'edu' => $edu,
                                ])
                            @endforeach
                        </div>

                        <button type="button"
                            class="btn btn-outline-success w-100 py-3 rounded-4 border-2 border-dashed fw-bold"
                            onclick="addEducation()">
                            <i class="bi bi-plus-circle-dotted me-2"></i> Añadir formación académica
                        </button>
                    </section>

                    {{-- SKILLS & TOOLS --}}
                    <section class="card-profile p-4 mb-5">
                        <h2 class="section-title mb-4"><i class="bi bi-stars text-warning"></i> Aptitudes</h2>

                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">Tus Habilidades</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-white border-end-0"><i
                                            class="bi bi-search text-muted"></i></span>
                                    <input type="text" id="skill-input" class="form-control border-start-0 ps-0"
                                        placeholder="Ej: Atención al cliente, Java, Ventas...">
                                    <button class="btn btn-primary px-4" type="button"
                                        onclick="addTag('skill')">Añadir</button>
                                </div>
                                <div id="skill-tags" class="tag-container">
                                    @foreach ($profile->skills as $skill)
                                        <div class="tag-item tag-skill">
                                            {{ $skill->name }}
                                            <input type="hidden" name="skills[]" value="{{ $skill->name }}">
                                            <i class="bi bi-x-lg btn-close-tag" onclick="this.parentElement.remove()"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Software y Herramientas</label>
                                <div class="input-group mb-3">
                                    <input type="text" id="tool-input" class="form-control"
                                        placeholder="Excel, Photoshop...">
                                    <button class="btn btn-dark" type="button" onclick="addTag('tool')">+</button>
                                </div>
                                <div id="tool-tags" class="tag-container">
                                    @foreach ($profile->tools as $tool)
                                        <div class="tag-item tag-tool">
                                            {{ $tool->name }}
                                            <input type="hidden" name="tools[]" value="{{ $tool->name }}">
                                            <i class="bi bi-x-lg btn-close-tag" onclick="this.parentElement.remove()"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Idiomas</label>
                                <div class="input-group mb-3">
                                    <input type="text" id="language-input" class="form-control"
                                        placeholder="Inglés B2, Alemán...">
                                    <button class="btn btn-dark" type="button" onclick="addTag('language')">+</button>
                                </div>
                                <div id="language-tags" class="tag-container">
                                    @foreach ($profile->languages as $lang)
                                        <div class="tag-item tag-lang">
                                            {{ $lang->name }}
                                            <input type="hidden" name="languages[][name]" value="{{ $lang->name }}">
                                            <i class="bi bi-x-lg btn-close-tag" onclick="this.parentElement.remove()"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Sidebar Resumen --}}
                <div class="col-lg-4">
                    <div class="sticky-summary">
                        <div class="card-profile p-4 text-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="width: 64px; height: 64px;">
                                <img src="{{ asset(Auth::user()->workerProfile->profile_image_url) }}" alt="Foto Worker"
                                    class="rounded-circle me-1 border" style="width: 100% !important;">
                            </div>
                            <h5 class="fw-bold mb-1">Editar Perfil</h5>
                            <p class="small text-muted mb-4">Revisa que las fechas y habilidades sean correctas antes de
                                guardar.</p>

                            <div class="bg-light rounded-3 p-3 mb-4 text-start">
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-secondary">Experiencias:</span>
                                    <span class="fw-bold" id="summary-exp">0</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-secondary">Formaciones:</span>
                                    <span class="fw-bold" id="summary-edu">0</span>
                                </div>
                            </div>

                            <button type="submit"
                                class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow-sm mb-3">
                                GUARDAR CAMBIOS
                            </button>

                            <a href="{{ route('worker.dashboard') }}"
                                class="btn btn-link btn-sm text-secondary text-decoration-none">
                                <i class="bi bi-x-circle me-1"></i> Descartar y salir
                            </a>
                        </div>

                        <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4 py-3">
                            <div class="d-flex gap-3">
                                <i class="bi bi-lightbulb-fill text-info fs-4"></i>
                                <div class="small">
                                    <strong>Consejo de FuerteJob:</strong>
                                    Los perfiles con descripción en la experiencia reciben un 40% más de interés por parte
                                    de los reclutadores locales.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Referencia al conteo inicial
        let expIndex = {{ $profile->experiences->count() }};
        let eduIndex = {{ $profile->educations->count() }};

        function updateCounters() {
            const expCount = $('#experience-container .repeater-item').length;
            const eduCount = $('#education-container .repeater-item').length;
            $('#exp-count, #summary-exp').text(expCount);
            $('#edu-count, #summary-edu').text(eduCount);
        }

        function toggleCurrent(checkbox) {
            const row = $(checkbox).closest('.repeater-item');
            const dateInput = row.find('.end-date-input');
            if (checkbox.checked) {
                dateInput.attr('disabled', true).val('').addClass('bg-light opacity-50');
            } else {
                dateInput.attr('disabled', false).removeClass('bg-light opacity-50');
            }
        }

        function removeRow(btn) {
            $(btn).closest('.repeater-item').css('transform', 'scale(0.95)').fadeOut(200, function() {
                $(this).remove();
                updateCounters();
            });
        }

        function addExperience() {
            const html = `
        <div class="repeater-item" data-index="${expIndex}" style="display:none;">
            <div class="btn-remove" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Puesto de trabajo</label>
                    <input type="text" name="experiences[${expIndex}][job_title]" class="form-control form-control-lg fs-6" required placeholder="Ej: Recepcionista">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Empresa / Negocio</label>
                    <input type="text" name="experiences[${expIndex}][company_name]" class="form-control form-control-lg fs-6" required placeholder="Ej: Hotel Fuerteventura">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Año de Inicio</label>
                    <input type="number" min="1900" max="2100" name="experiences[${expIndex}][start_year]"
                        class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Año de Fin</label>
                    <input type="number" min="1900" max="2100" name="experiences[${expIndex}][end_year]"
                        class="form-control end-date-input bg-light opacity-50" disabled>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="experiences[${expIndex}][is_current]" value="1" onchange="toggleCurrent(this)" checked>
                        <label class="form-check-label small fw-bold text-primary">Trabajo actual</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">¿Qué tareas realizabas?</label>
                    <textarea name="experiences[${expIndex}][description]" class="form-control" rows="3" placeholder="Describe tus responsabilidades principales..."></textarea>
                </div>
            </div>
        </div>`;
            $('#experience-container').append(html);
            $(`.repeater-item[data-index="${expIndex}"]`).fadeIn(300);
            expIndex++;
            updateCounters();
        }

        function addEducation() {
            const html = `
        <div class="repeater-item" data-index="${eduIndex}" style="display:none;">
            <div class="btn-remove" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título o Estudio</label>
                    <input type="text" name="education[${eduIndex}][degree]" class="form-control form-control-lg fs-6" required placeholder="Ej: Grado en Turismo">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Centro Educativo</label>
                    <input type="text" name="education[${eduIndex}][institution]" class="form-control form-control-lg fs-6" required placeholder="Ej: IES Puerto del Rosario">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Inicio</label>
                    <input type="date" name="education[${eduIndex}][start_date]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fin</label>
                    <input type="date" name="education[${eduIndex}][end_date]" class="form-control end-date-input bg-light opacity-50" disabled>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="education[${eduIndex}][is_current]" value="1" onchange="toggleCurrent(this)" checked>
                        <label class="form-check-label small fw-bold text-success">En curso</label>
                    </div>
                </div>
            </div>
        </div>`;
            $('#education-container').append(html);
            $(`.repeater-item[data-index="${eduIndex}"]`).fadeIn(300);
            eduIndex++;
            updateCounters();
        }

        function addTag(type) {
            const input = $(`#${type}-input`);
            const value = input.val().trim();
            if (!value) return;

            // Evitar duplicados
            let existing = false;
            $(`#${type}-tags .tag-item`).each(function() {
                if ($(this).text().trim().toLowerCase() === value.toLowerCase()) existing = true;
            });
            if (existing) {
                input.val('').focus();
                return;
            }

            let tagClass = 'tag-' + type;
            let fieldName;
            switch (type) {
                case 'skill':
                    fieldName = 'skills[]';
                    break;
                case 'tool':
                    fieldName = 'tools[]';
                    break;
                case 'language':
                    fieldName = 'languages[][name]';
                    break;
                case 'desired':
                    fieldName = 'desired_positions[]';
                    break;
                case 'sector':
                    fieldName = 'desired_sectors[]';
                    break;
                default:
                    fieldName = '';
            }
            if (!fieldName) return;

            const html = `
        <div class="tag-item ${tagClass}">
            ${value}
            <input type="hidden" name="${fieldName}" value="${value}">
            <i class="bi bi-x-lg btn-close-tag" onclick="this.parentElement.remove()"></i>
        </div>`;

            $(`#${type}-tags`).append(html);
            input.val('').focus();
        }

        $(document).ready(function() {
            updateCounters();

            // Autocomplete para Skills
            if ($("#skill-input").length) {
                $("#skill-input").autocomplete({
                    source: "{{ route('habilidades.search') }}",
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        addTag('skill');
                        return false;
                    }
                });
            }

            // Autocomplete para Tools
            if ($("#tool-input").length) {
                $("#tool-input").autocomplete({
                    source: "{{ route('herramientas.search') }}",
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        addTag('tool');
                        return false;
                    }
                });
            }

            // Autocomplete para Idiomas
            if ($("#language-input").length) {
                $("#language-input").autocomplete({
                    source: "{{ route('idiomas.search') }}",
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        addTag('language');
                        return false;
                    }
                });
            }

            // Autocomplete para Puestos
            if ($("#desired-input").length) {
                $("#desired-input").autocomplete({
                    source: "{{ route('puestos.search.worker') }}",
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        addTag('desired');
                        return false;
                    }
                });
            }

            // Autocomplete para Puestos deseados
            if ($("#desired-input").length) {
                $("#desired-input").autocomplete({
                    source: "{{ route('puestos.search.worker') }}",
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        addTag('desired');
                        return false;
                    }
                });
            }

            // Autocomplete para Sectores
            if ($("#sector-input").length) {
                $("#sector-input").autocomplete({
                    source: "{{ route('sectores.search.worker') }}",
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        addTag('sector');
                        return false;
                    }
                });
            }

            // Enter key handler
            $('.form-control').keypress(function(e) {
                if (e.which == 13) {
                    const id = $(this).attr('id');
                    if (id && id.endsWith('-input')) {
                        e.preventDefault();
                        addTag(id.split('-')[0]);
                    }
                }
            });

            if ($('#experience-container .repeater-item').length === 0) addExperience();
            if ($('#education-container .repeater-item').length === 0) addEducation();
        });
    </script>
@endsection
