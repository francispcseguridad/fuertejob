@extends('layouts.app')

@section('title', 'Publicar Nueva Oferta de Trabajo')

@section('content')
    {{-- jQuery UI para Autocomplete --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    {{-- Estilos personalizados para autocomplete y sistema de tags --}}
    <style>
        /* Autocomplete debe aparecer sobre otros elementos */
        .ui-autocomplete {
            z-index: 1060 !important;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .ui-menu-item {
            font-size: 0.95rem;
        }

        .ui-menu-item-wrapper {
            padding: 8px 12px;
        }

        .ui-state-active {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }

        /* Contenedor de tags */
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            min-height: 50px;
            background-color: #fff;
            margin-bottom: 10px;
        }

        .tags-container:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Estilo de cada tag */
        .tag-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .tag-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .tag-item .remove-tag {
            cursor: pointer;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: background 0.2s;
        }

        .tag-item .remove-tag:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Input dentro del contenedor de tags */
        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 150px;
            padding: 6px;
            font-size: 0.95rem;
        }

        /* Colores diferentes para cada tipo */
        .tag-skill {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .tag-tool {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .tag-language {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        /* LocationIQ Autocomplete Styles */
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

    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold text-dark">Publicar Nueva Oferta</h1>
                <p class="text-muted lead">Completa el formulario para crear una nueva vacante</p>
            </div>
            <div>
                <a href="{{ route('empresa.ofertas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Mis Ofertas
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Mensajes de Estado -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-start border-danger border-4"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                            <div>
                                <strong>¡Atención!</strong> Por favor corrige los siguientes errores:
                                <ul class="mb-0 mt-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-start border-danger border-4"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Error:</strong> {{ session('error') }}
                                @if (str_contains(session('error'), 'Saldo insuficiente'))
                                    <div class="mt-2">
                                        <a href="{{ route('empresa.bonos.catalogo') }}" class="alert-link fw-bold">
                                            <i class="bi bi-cart-plus me-1"></i>Haz clic aquí para recargar créditos
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Alerta de Costo -->
                <div class="card shadow-sm border-warning border-2 mb-4">
                    <div class="card-body bg-warning bg-opacity-10">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill text-warning fs-3 me-3"></i>
                            <div>
                                <h5 class="card-title fw-bold text-warning mb-2">
                                    <i class="bi bi-coin me-1"></i>Costo de Publicación
                                </h5>
                                <p class="mb-2">Publicar una oferta de trabajo consume <strong
                                        class="text-dark">{{ $cost }} crédito</strong> de tu saldo.</p>
                                <p class="mb-0 small">
                                    Tu saldo actual: <span
                                        class="badge bg-warning text-dark fs-6">{{ $currentBalance ?? 0 }} Créditos</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario Principal -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-file-earmark-text me-2"></i>Detalles de la Oferta
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('empresa.ofertas.store') }}" method="POST">
                            @csrf

                            <!-- Título -->
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">
                                    <i class="bi bi-briefcase me-1 text-primary"></i>Título de la Oferta
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="title"
                                    class="form-control form-control-lg @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" placeholder="Ej: Desarrollador Full Stack Senior" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                    <label for="description" class="form-label fw-semibold mb-0">
                                        <i class="bi bi-card-text me-1 text-primary"></i>Descripción Completa
                                        <span class="text-danger">*</span>
                                    </label>
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#jobOfferAiGeneratorModal">
                                        <i class="bi bi-stars me-1"></i>Generar con IA
                                    </button>
                                </div>
                                <textarea name="description" id="description" rows="6"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Describe las responsabilidades del puesto en detalle..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Requisitos -->
                            <div class="mb-4">
                                <label for="requirements" class="form-label fw-semibold">
                                    <i class="bi bi-list-check me-1 text-primary"></i>Requisitos Mínimos
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea name="requirements" id="requirements" rows="4"
                                    class="form-control @error('requirements') is-invalid @enderror" required
                                    placeholder="Ej: 3+ años de experiencia en PHP/Laravel, inglés B2, Grado en Informática">{{ old('requirements') }}</textarea>
                                @error('requirements')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Lista de habilidades y experiencia indispensable.
                                </div>
                            </div>

                            <!-- HABILIDADES REQUERIDAS -->
                            <div class="mb-4">
                                <label for="skill_input" class="form-label fw-semibold">
                                    <i class="bi bi-tools me-1 text-success"></i>Habilidades Técnicas (Skills)
                                </label>
                                <div class="tags-container" id="skills-tags-container" data-type="skill">
                                    <input type="text" class="tag-input" id="skill_input"
                                        placeholder="Escribe para buscar habilidades..." autocomplete="off">
                                </div>
                                <!-- Inputs ocultos para enviar los IDs seleccionados -->
                                <div id="skills-hidden-inputs"></div>
                                @error('skill_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Escribe y selecciona del autocompletado. Los tags
                                    aparecerán arriba.
                                </div>
                            </div>

                            <!-- HERRAMIENTAS REQUERIDAS -->
                            <div class="mb-4">
                                <label for="tool_input" class="form-label fw-semibold">
                                    <i class="bi bi-cpu me-1 text-success"></i>Herramientas y Tecnologías
                                </label>
                                <div class="tags-container" id="tools-tags-container" data-type="tool">
                                    <input type="text" class="tag-input" id="tool_input"
                                        placeholder="Escribe para buscar herramientas..." autocomplete="off">
                                </div>
                                <!-- Inputs ocultos para enviar los IDs seleccionados -->
                                <div id="tools-hidden-inputs"></div>
                                @error('tool_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Escribe y selecciona del autocompletado. Los tags
                                    aparecerán arriba.
                                </div>
                            </div>

                            <!-- IDIOMAS REQUERIDOS (NUEVO CAMPO) -->
                            <div class="mb-4">
                                <label for="language_input" class="form-label fw-semibold">
                                    <i class="bi bi-translate me-1 text-success"></i>Idiomas Requeridos
                                </label>
                                <div class="tags-container" id="languages-tags-container" data-type="language">
                                    <input type="text" class="tag-input" id="language_input"
                                        placeholder="Escribe para buscar idiomas..." autocomplete="off">
                                </div>
                                <!-- Inputs ocultos para enviar los valores seleccionados -->
                                <div id="languages-hidden-inputs"></div>
                                @error('required_languages')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Indica los idiomas que el candidato debe dominar.
                                </div>
                            </div>


                            <!-- Beneficios -->
                            <div class="mb-4">
                                <label for="benefits" class="form-label fw-semibold">
                                    <i class="bi bi-gift me-1 text-primary"></i>Beneficios y Perks
                                </label>
                                <textarea name="benefits" id="benefits" rows="4" class="form-control @error('benefits') is-invalid @enderror"
                                    placeholder="Ej: Flexibilidad horaria, seguro médico privado, bono anual por desempeño, 24 días de vacaciones">{{ old('benefits') }}</textarea>
                                @error('benefits')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Ventajas que ofrece tu empresa a los candidatos.
                                </div>
                            </div>

                            <!-- Visibilidad de la empresa -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold d-block">
                                    <i class="bi bi-eye me-1 text-primary"></i>Mostrar nombre de la empresa en la oferta
                                </label>
                                <input type="hidden" name="company_visible" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="company_visible"
                                        name="company_visible" value="1" @checked(old('company_visible', 1))>
                                    <label class="form-check-label" for="company_visible">
                                        Activar para que los candidatos vean el nombre de tu empresa.
                                    </label>
                                </div>
                                @error('company_visible')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Desactiva para publicar la oferta de forma confidencial.
                                </div>
                            </div>

                            <!-- Fila: Contrato, Modalidad, Estado, Salario -->
                            <div class="row g-3 mb-4">
                                <!-- Tipo de Contrato -->
                                <div class="col-md-3">
                                    <label for="contract_type" class="form-label fw-semibold">
                                        <i class="bi bi-file-earmark-check me-1 text-primary"></i>Contrato
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="contract_type" id="contract_type"
                                        class="form-select @error('contract_type') is-invalid @enderror" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach (['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'] as $type)
                                            <option value="{{ $type }}"
                                                @if (old('contract_type') == $type) selected @endif>
                                                {{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('contract_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Modalidad -->
                                <div class="col-md-3">
                                    <label for="modality" class="form-label fw-semibold">
                                        <i class="bi bi-globe me-1 text-primary"></i>Modalidad
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="modality" id="modality"
                                        class="form-select @error('modality') is-invalid @enderror" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach (['presencial', 'remoto', 'hibrido'] as $modality)
                                            <option value="{{ $modality }}"
                                                @if (old('modality') == $modality) selected @endif>
                                                {{ ucfirst($modality) }}</option>
                                        @endforeach
                                    </select>
                                    @error('modality')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Estado -->
                                <div class="col-md-3">
                                    <label for="status" class="form-label fw-semibold">
                                        <i class="bi bi-flag me-1 text-primary"></i>Estado
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="status" id="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="Borrador" @selected(old('status') == 'Borrador')>Borrador</option>
                                        <option value="Publicado" @selected(old('status') == 'Publicado')>Publicada</option>
                                        <option value="Finalizada" @selected(old('status') == 'Finalizada')>Finalizada</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Rango Salarial -->
                                <div class="col-md-3">
                                    <label for="salary_range" class="form-label fw-semibold">
                                        <i class="bi bi-cash-stack me-1 text-primary"></i>Rango Salarial
                                    </label>
                                    <input type="text" name="salary_range" id="salary_range"
                                        class="form-control @error('salary_range') is-invalid @enderror"
                                        value="{{ old('salary_range') }}" placeholder="Ej: 30.000€ - 40.000€">
                                    @error('salary_range')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Fila: Ubicación Completa -->
                            <div class="row g-3 mb-4">
                                <!-- Localización -->
                                <div class="col-md-12">
                                    <label for="location" class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt me-1 text-primary"></i>Ciudad/Ubicación
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="location" id="location"
                                        class="form-control @error('location') is-invalid @enderror"
                                        value="{{ old('location') }}" placeholder="Ej: Madrid, España" required
                                        autocomplete="off">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Escribe para buscar.
                                    </div>

                                    <input type="hidden" name="province" id="province" class="form-control"
                                        value="{{ old('province') }}" readonly>

                                    <input type="hidden" name="island" id="island" class="form-control"
                                        value="{{ old('island') }}" readonly>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                <!-- Guardar Borrador -->
                                <button type="submit" name="action" value="Borrador"
                                    class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="bi bi-save me-2"></i>Guardar como Borrador
                                </button>

                                <!-- Publicar -->
                                <button type="submit" name="action" value="Publicada"
                                    class="btn btn-primary btn-lg px-5 shadow">
                                    <i class="bi bi-send-fill me-2"></i>Publicar Oferta (Descontar 1 Crédito)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="card border-0 bg-light mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-question-circle me-2 text-info"></i>Consejos para una mejor oferta
                        </h6>
                        <ul class="small mb-0 text-muted">
                            <li>Usa un título claro y específico que describa el puesto.</li>
                            <li>Detalla las responsabilidades principales y requisitos necesarios.</li>
                            <li>Menciona los beneficios y oportunidades de crecimiento.</li>
                            <li>Indica el rango salarial para atraer candidatos cualificados.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('company.job_offers.partials.ai_description_generator')

    {{-- JavaScript para el sistema de tags con autocompletado --}}
    <script>
        $(document).ready(function() {
            // Configuración de URLs para autocomplete
            const autocompleteUrls = {
                skill: "{{ route('habilidades.search') }}",
                tool: "{{ route('herramientas.search') }}",
                language: "{{ route('idiomas.search') }}"
            };

            // Almacenar los elementos seleccionados
            const selectedItems = {
                skill: new Map(), // Map<id, name>
                tool: new Map(),
                language: new Map()
            };

            // Restaurar valores antiguos si existen (para validación fallida)
            @if (old('skill_ids'))
                @foreach (old('skill_ids', []) as $skillId)
                    @php
                        $skill = $allSkills->firstWhere('id', $skillId);
                    @endphp
                    @if ($skill)
                        selectedItems.skill.set({{ $skillId }}, "{{ $skill->name }}");
                    @endif
                @endforeach
            @endif

            @if (old('tool_ids'))
                @foreach (old('tool_ids', []) as $toolId)
                    @php
                        $tool = $allTools->firstWhere('id', $toolId);
                    @endphp
                    @if ($tool)
                        selectedItems.tool.set({{ $toolId }}, "{{ $tool->name }}");
                    @endif
                @endforeach
            @endif

            @if (old('required_languages'))
                @foreach (old('required_languages', []) as $index => $lang)
                    selectedItems.language.set("{{ $lang }}", "{{ $lang }}");
                @endforeach
            @endif

            // Función para crear un tag visual
            function createTag(type, id, name) {
                const tag = $('<div>', {
                    class: `tag-item tag-${type}`,
                    'data-id': id,
                    'data-type': type
                });

                tag.append($('<span>').text(name));

                const removeBtn = $('<span>', {
                    class: 'remove-tag',
                    html: '×',
                    click: function() {
                        removeTag(type, id);
                    }
                });

                tag.append(removeBtn);
                return tag;
            }

            // Función para agregar un tag
            function addTag(type, id, name) {
                // Evitar duplicados
                if (selectedItems[type].has(id)) {
                    return;
                }

                selectedItems[type].set(id, name);

                // Crear el tag visual
                const tag = createTag(type, id, name);
                const container = $(`#${type}s-tags-container`);
                const input = container.find('.tag-input');

                // Insertar el tag antes del input
                input.before(tag);

                // Limpiar el input
                input.val('');

                // Actualizar inputs ocultos
                updateHiddenInputs(type);
            }

            // Función para eliminar un tag
            function removeTag(type, id) {
                selectedItems[type].delete(id);

                // Eliminar el tag visual
                $(`#${type}s-tags-container .tag-item[data-id="${id}"]`).remove();

                // Actualizar inputs ocultos
                updateHiddenInputs(type);
            }

            // Función para actualizar los inputs ocultos
            function updateHiddenInputs(type) {
                const container = $(`#${type}s-hidden-inputs`);
                container.empty();

                const fieldName = type === 'language' ? 'required_languages[]' : `${type}_ids[]`;

                selectedItems[type].forEach((name, id) => {
                    container.append($('<input>', {
                        type: 'hidden',
                        name: fieldName,
                        value: id
                    }));
                });
            }

            // Inicializar autocomplete para skills
            $('#skill_input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: autocompleteUrls.skill,
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data.map(item => ({
                                label: item.label || item.value || item,
                                value: item.value || item,
                                id: item.id || item.value || item
                            })));
                        }
                    });
                },
                select: function(event, ui) {
                    event.preventDefault();
                    addTag('skill', ui.item.id, ui.item.value);
                    return false;
                },
                minLength: 1
            });

            // Inicializar autocomplete para tools
            $('#tool_input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: autocompleteUrls.tool,
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data.map(item => ({
                                label: item.label || item.value || item,
                                value: item.value || item,
                                id: item.id || item.value || item
                            })));
                        }
                    });
                },
                select: function(event, ui) {
                    event.preventDefault();
                    addTag('tool', ui.item.id, ui.item.value);
                    return false;
                },
                minLength: 1
            });

            // Inicializar autocomplete para languages
            $('#language_input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: autocompleteUrls.language,
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data.map(item => ({
                                label: item.label || item.value || item,
                                value: item.value || item,
                                id: item.value || item
                            })));
                        }
                    });
                },
                select: function(event, ui) {
                    event.preventDefault();
                    addTag('language', ui.item.id, ui.item.value);
                    return false;
                },
                minLength: 1
            });

            // Restaurar tags de valores antiguos
            selectedItems.skill.forEach((name, id) => {
                const tag = createTag('skill', id, name);
                $('#skills-tags-container .tag-input').before(tag);
            });
            updateHiddenInputs('skill');

            selectedItems.tool.forEach((name, id) => {
                const tag = createTag('tool', id, name);
                $('#tools-tags-container .tag-input').before(tag);
            });
            updateHiddenInputs('tool');

            selectedItems.language.forEach((name, id) => {
                const tag = createTag('language', id, name);
                $('#languages-tags-container .tag-input').before(tag);
            });
            updateHiddenInputs('language');
            updateHiddenInputs('language');

            // --- Autocomplete de ubicación (localidades + LocationIQ) ---
            const locationInput = document.getElementById('location');
            const provinceInput = document.getElementById('province');
            const islandInput = document.getElementById('island');
            const LOCATIONIQ_KEY = 'pk.d52886ad23ebf6a01e455bb91b89bcc1';

            if (locationInput) {
                let resultsContainer = document.createElement('div');
                resultsContainer.className = 'autocomplete-results d-none';

                let wrapper = document.createElement('div');
                wrapper.className = 'autocomplete-container';

                locationInput.parentNode.insertBefore(wrapper, locationInput);
                wrapper.appendChild(locationInput);
                wrapper.appendChild(resultsContainer);

                let timeout = null;
                const renderLocationSuggestions = (items) => {
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
                            locationInput.value = item.city;
                            if (provinceInput) provinceInput.value = item.province || '';
                            if (islandInput) islandInput.value = item.island || '';
                            resultsContainer.classList.add('d-none');
                            resultsContainer.innerHTML = '';
                        };

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

                locationInput.addEventListener('input', function() {
                    const query = this.value;

                    if (query.length < 3) {
                        resultsContainer.classList.add('d-none');
                        resultsContainer.innerHTML = '';
                        return;
                    }

                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const localUrl = `/api/localidades/search?q=${encodeURIComponent(query)}`;
                        const iqUrl =
                            `https://api.locationiq.com/v1/autocomplete?key=${LOCATIONIQ_KEY}&q=${encodeURIComponent(query)}&limit=5&tag=place:city,place:town,place:village,place:hamlet`;

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

                                if (formattedLocal.length > 0) {
                                    renderLocationSuggestions(formattedLocal);
                                    return;
                                }

                                return fetch(iqUrl)
                                    .then(r => r.ok ? r.json() : [])
                                    .then(iqData => {
                                        if (!Array.isArray(iqData)) {
                                            renderLocationSuggestions(formattedLocal);
                                            return;
                                        }
                                        const formattedIq = iqData.map(item => {
                                            const address = item.address || {};
                                            return {
                                                city: address.city || address
                                                    .town ||
                                                    address.village || address
                                                    .hamlet || address.name,
                                                country: address.country || '',
                                                province: address.province ||
                                                    address.state ||
                                                    address.county || '',
                                                island: address.island || '',
                                            };
                                        });
                                        renderLocationSuggestions([...formattedLocal, ...
                                            formattedIq
                                        ]);
                                    })
                                    .catch(() => renderLocationSuggestions(formattedLocal));
                            })
                            .catch(e => {
                                console.error('LocationIQ Error:', e);
                                resultsContainer.classList.add('d-none');
                            });
                    }, 300);
                });

                document.addEventListener('click', function(e) {
                    if (!wrapper.contains(e.target)) {
                        resultsContainer.classList.add('d-none');
                    }
                });
            }
        });
    </script>
@endsection
