@extends('layouts.app')

@php
    $oferta = $jobOffer;
@endphp

@section('title', 'Editar Oferta: ' . $oferta->title)

@php
    $showCompany = $oferta->company_visible ?? true;
    $companyName = $showCompany ? $oferta->companyProfile->company_name ?? 'Empresa' : 'Empresa Confidencial';
@endphp

@section('meta')
    <meta property="og:title"
        content="Oferta de Trabajo: {{ $oferta->title }} - {{ $oferta->islandRelation->name ?? ($oferta->island ?? 'Canarias') }} | {{ $companyName }} | FuerteJob">
    <meta property="og:description"
        content="Entra en FuerteJob.com para obtener más información sobre esta oferta o encontrar más ofertas de empleos.">
    <meta property="og:image" content="{{ asset('img/logofacebook.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image" content="{{ asset('img/logowhatsapp.png') }}">
    <meta property="og:image:width" content="630">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ route('public.jobs.show', $oferta->id) }}">
    <meta property="og:type" content="article">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title"
        content="Oferta de Trabajo: {{ $oferta->title }} - {{ $oferta->islandRelation->name ?? ($oferta->island ?? 'Canarias') }} | FuerteJob">
    <meta name="twitter:description"
        content="Entra en FuerteJob.com para obtener más información sobre esta oferta o encontrar más ofertas de empleos.">
    <meta name="twitter:image" content="{{ asset('img/logofacebook.png') }}">
@endsection

@section('content')
    @php
        $oferta = $jobOffer;
    @endphp
    {{-- jQuery UI para Autocomplete --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
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
                <h1 class="display-5 fw-bold text-dark">Editar Oferta</h1>
                <p class="text-muted lead">{{ $oferta->title }}</p>
            </div>
            <div>
                <a href="{{ route('admin.ofertas.index') }}" class="btn btn-outline-secondary">
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
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show border-start border-warning border-4"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>Aviso:</strong> {{ session('warning') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Estado Actual y Costo (Usando Accessor para mostrar el estado en español) -->
                <div class="card shadow-sm border-info border-2 mb-4">
                    <div class="card-body bg-success">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill text-white fs-3 me-3"></i>
                            <div class="flex-grow-1">
                                <h5 class="card-title fw-bold text-white mb-2">
                                    <i class="bi bi-flag me-1"></i>Estado Actual
                                </h5>
                                <p class="mb-2 text-white">
                                    Estado de la oferta:
                                    <span
                                        class="badge                                        
                                        @if ($oferta->status === 'Publicado') bg-success
                                        @elseif($oferta->status === 'Borrador') bg-secondary
                                        @else bg-warning @endif fs-6">
                                        {{-- Asumiendo que $oferta->status_display existe y funciona como Accessor --}}
                                        {{ $oferta->status_display }}
                                    </span>
                                </p>
                                {{-- Usamos el valor en inglés 'Publicado' para la lógica interna --}}
                                @if ($oferta->status !== 'Publicado')
                                    <p class="mb-0 small">
                                        <i class="bi bi-coin me-1"></i>
                                        Publicar consumirá <strong>{{ $cost }} crédito</strong>
                                        (Tu saldo actual: <span class="badge bg-warning text-dark">{{ $currentBalance }}
                                            créditos</span>).
                                        Si ya está Publicado, solo se actualizará el contenido sin coste adicional.
                                    </p>
                                @else
                                    <p class="mb-0 small text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Esta oferta ya está Publicado. Las actualizaciones no consumirán créditos.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Búsqueda de Candidatos -->
                {{-- Mostramos esta opción si la oferta no está en borrador vacío --}}
                @if ($oferta->status !== 'Borrador')
                    <div class="card shadow-lg border-primary border-2 mb-4">
                        <div class="card-body bg-primary bg-opacity-10 p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="me-3">
                                    <h5 class="card-title fw-bold text-white mb-1">
                                        <i class="bi bi-people-fill me-1"></i>Gestionar Inscritos
                                    </h5>
                                    <p class="mb-0 small text-white">
                                        Revisa y gestiona las personas inscritas en esta oferta.
                                    </p>
                                </div>
                                <a href="{{ route('admin.ofertas.candidatos', $jobOffer) }}"
                                    class="btn btn-primary btn-lg shadow">
                                    <i class="bi bi-people me-2"></i>Inscritos
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Formulario Principal -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>Editar Detalles de la Oferta
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="jobOfferForm" action="{{ route('admin.ofertas.update', $jobOffer) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Título -->
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">
                                    <i class="bi bi-briefcase me-1 text-primary"></i>Título de la Oferta
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="title"
                                    class="form-control form-control-lg @error('title') is-invalid @enderror"
                                    value="{{ old('title', $oferta->title) }}"
                                    placeholder="Ej: Desarrollador Full Stack Senior" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sector de actividad -->
                            <div class="mb-4">
                                <label for="job_sector_name" class="form-label fw-semibold">
                                    <i class="bi bi-building me-1 text-primary"></i>Sector de actividad
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="hidden" name="job_sector_id" id="job_sector_id"
                                    value="{{ old('job_sector_id', $oferta->job_sector_id) }}">
                                <input type="text" id="job_sector_name"
                                    class="form-control form-control-lg @error('job_sector_id') is-invalid @enderror"
                                    value="{{ old('job_sector_name', optional($allSectors->firstWhere('id', old('job_sector_id', $oferta->job_sector_id)))->name) }}"
                                    placeholder="Empieza a escribir para buscar..." autocomplete="off" required>
                                @error('job_sector_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Este sector se usará para el matching con candidatos interesados.
                                </div>
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
                                    placeholder="Describe las responsabilidades del puesto en detalle..." required>{{ old('description', $oferta->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Requisitos (NUEVO CAMPO) -->
                            <div class="mb-4">
                                <label for="requirements" class="form-label fw-semibold">
                                    <i class="bi bi-list-check me-1 text-primary"></i>Requisitos Mínimos
                                </label>
                                <textarea name="requirements" id="requirements" rows="4"
                                    class="form-control @error('requirements') is-invalid @enderror"
                                    placeholder="Ej: 3+ años de experiencia, conocimiento de Laravel, inglés B2">{{ old('requirements', $oferta->requirements) }}</textarea>
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

                            <!-- IDIOMAS REQUERIDOS -->
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

                            <!-- Beneficios (NUEVO CAMPO) -->
                            <div class="mb-4">
                                <label for="benefits" class="form-label fw-semibold">
                                    <i class="bi bi-gift me-1 text-primary"></i>Beneficios y Perks
                                </label>
                                <textarea name="benefits" id="benefits" rows="4" class="form-control @error('benefits') is-invalid @enderror"
                                    placeholder="Ej: Flexibilidad horaria, seguro médico privado, bono anual por desempeño">{{ old('benefits', $oferta->benefits) }}</textarea>
                                @error('benefits')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="bi bi-lightbulb me-1"></i>Ventajas que ofrece tu empresa.
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
                                        name="company_visible" value="1" @checked(old('company_visible', $oferta->company_visible ?? 1))>
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
                                                @if (old('contract_type', $oferta->contract_type) == $type) selected @endif>
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
                                                @if (old('modality', $oferta->modality) == $modality) selected @endif>
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
                                        <option value="Borrador" @selected(old('status', $oferta->status) == 'Borrador')>Borrador</option>
                                        <option value="Publicado" @selected(old('status', $oferta->status) == 'Publicado')>Publicado</option>
                                        <option value="Finalizada" @selected(old('status', $oferta->status) == 'Finalizada')>Finalizada</option>
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
                                        value="{{ old('salary_range', $oferta->salary_range) }}"
                                        placeholder="Ej: 30.000€ - 40.000€">
                                    @error('salary_range')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fila de Ubicación Completa -->
                                <div class="col-md-12">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label for="location" class="form-label fw-semibold">
                                                <i class="bi bi-geo-alt me-1 text-primary"></i>Ciudad/Ubicación
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="location" id="location"
                                                class="form-control @error('location') is-invalid @enderror"
                                                value="{{ old('location', $oferta->location) }}"
                                                placeholder="Ej: Madrid, España" required autocomplete="off">
                                            @error('location')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                Escribe para buscar.
                                            </div>

                                            <input type="hidden" name="province" id="province" class="form-control"
                                                value="{{ old('province', $oferta->province) }}" readonly>

                                            <input type="hidden" name="island" id="island" class="form-control"
                                                value="{{ old('island', $oferta->island) }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón Actualizar -->
                            <div class="d-flex justify-content-end pt-4 border-top">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                    <i class="bi bi-pencil-square me-2"></i>Actualizar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <form id="adminApproveForm" action="{{ route('admin.ofertas.aprobar', $jobOffer) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-check-circle me-2"></i>Aprobar
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger btn-lg px-4" data-bs-toggle="modal"
                        data-bs-target="#rejectOfferModal">
                        <i class="bi bi-x-circle me-2"></i>Rechazar
                    </button>
                </div>

                <!-- Información Adicional -->
                <div class="card border-0 bg-light mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-clock-history me-2 text-info"></i>Historial de la Oferta
                        </h6>
                        <div class="row small text-muted">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>Creada:</strong> {{ $oferta->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>Última actualización:</strong> {{ $oferta->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Compartir oferta (solo si está Publicado) --}}
    @if (in_array(strtolower($oferta->status), ['Publicado', 'publicado', 'published']))
        @include('components.share_buttons', [
            'title' => 'Comparte esta oferta',
            'text' => 'FuerteJob - ' . $oferta->title,
            'url' => route('worker.jobs.show', $oferta->id),
        ])
    @endif

    @include('company.job_offers.partials.ai_description_generator')

    {{-- Modal Rechazar oferta --}}
    <div class="modal fade" id="rejectOfferModal" tabindex="-1" aria-labelledby="rejectOfferModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="adminRejectForm" action="{{ route('admin.ofertas.rechazar', $jobOffer) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectOfferModalLabel">
                            <i class="bi bi-x-circle me-2 text-danger"></i>Rechazar oferta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Indica el motivo del rechazo para notificar a la empresa.</p>
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label fw-semibold">Motivo</label>
                            <textarea name="reason" id="rejectReason" rows="3" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar y devolver crédito</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

            // Cargar valores existentes de la oferta
            @if ($oferta->skills && $oferta->skills->count() > 0)
                @foreach ($oferta->skills as $skill)
                    selectedItems.skill.set({{ $skill->id }}, "{{ $skill->name }}");
                @endforeach
            @endif

            @if ($oferta->tools && $oferta->tools->count() > 0)
                @foreach ($oferta->tools as $tool)
                    selectedItems.tool.set({{ $tool->id }}, "{{ $tool->name }}");
                @endforeach
            @endif

            @if ($oferta->required_languages && is_array($oferta->required_languages))
                @foreach ($oferta->required_languages as $lang)
                    selectedItems.language.set("{{ $lang }}", "{{ $lang }}");
                @endforeach
            @endif

            // Restaurar valores antiguos si existen (para validación fallida)
            @if (old('skill_ids') && isset($allSkills))
                @foreach (old('skill_ids', []) as $skillId)
                    @php
                        $skill = $allSkills->firstWhere('id', $skillId);
                    @endphp
                    @if ($skill)
                        selectedItems.skill.set({{ $skillId }}, "{{ $skill->name }}");
                    @endif
                @endforeach
            @endif

            @if (old('tool_ids') && isset($allTools))
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
            }).on('keydown', function(event) {
                if (event.keyCode === 13) { // Enter key
                    event.preventDefault();
                    const value = $(this).val().trim();
                    if (value) {
                        // Crear nuevo elemento con el nombre como ID temporal
                        addTag('skill', 'new_' + value, value);
                    }
                    return false;
                }
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
            }).on('keydown', function(event) {
                if (event.keyCode === 13) { // Enter key
                    event.preventDefault();
                    const value = $(this).val().trim();
                    if (value) {
                        // Crear nuevo elemento con el nombre como ID temporal
                        addTag('tool', 'new_' + value, value);
                    }
                    return false;
                }
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
            }).on('keydown', function(event) {
                if (event.keyCode === 13) { // Enter key
                    event.preventDefault();
                    const value = $(this).val().trim();
                    if (value) {
                        // Para idiomas, el ID es el mismo que el valor
                        addTag('language', value, value);
                    }
                    return false;
                }
            });

            // Restaurar tags de valores existentes y antiguos
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

            // --- Autocomplete Sector ---
            const sectorInput = document.getElementById('job_sector_name');
            const sectorHidden = document.getElementById('job_sector_id');
            const sectors = @json($allSectors->map(fn($s) => ['id' => $s->id, 'name' => $s->name]));

            if (sectorInput) {
                const sectorMap = {};
                sectors.forEach(s => sectorMap[s.name.toLowerCase()] = s.id);

                $(sectorInput).autocomplete({
                    source: sectors.map(s => ({
                        label: s.name,
                        value: s.name,
                        id: s.id
                    })),
                    minLength: 1,
                    select: function(event, ui) {
                        sectorInput.value = ui.item.value;
                        sectorHidden.value = ui.item.id;
                        return false;
                    },
                    focus: function(event, ui) {
                        sectorInput.value = ui.item.value;
                        return false;
                    }
                });

                sectorInput.addEventListener('blur', () => {
                    const text = sectorInput.value.trim().toLowerCase();
                    if (sectorMap[text]) {
                        sectorHidden.value = sectorMap[text];
                    } else {
                        sectorHidden.value = '';
                    }
                });
            }

            // --- LocationIQ Autocomplete Logic ---
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const approveForm = document.getElementById('adminApproveForm');
            if (!approveForm) {
                return;
            }

            approveForm.addEventListener('submit', function(event) {
                if (!confirm('¿Deseas aprobar y publicar esta oferta de inmediato?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
@endsection
