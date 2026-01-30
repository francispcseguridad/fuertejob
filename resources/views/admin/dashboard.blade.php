@extends('layouts.app')
@section('title', 'Panel de Administración')
@section('content')
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">Panel de Administración</h1>
                <p class="text-muted lead">Bienvenido al área de gestión global del sistema.</p>
            </div>
            <div>
                <span class="badge bg-light text-dark border p-2 rounded-pill shadow-sm">
                    <i class="bi bi-calendar-event me-1"></i> {{ date('d/m/Y') }}
                </span>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row g-4 mb-5">
            <!-- Stat 1: Total Workers -->
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-primary h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-primary fw-bold mb-1">Total Solicitantes</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalWorkers }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-people-fill fs-1 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat 2: Verified Emails -->
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-success h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-success fw-bold mb-1">Correos Verificados</h6>
                            <h2 class="mb-0 fw-bold">{{ $verifiedWorkers }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-check-circle-fill fs-1 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat 3: Total CVs -->
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-warning h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-warning fw-bold mb-1">CVs en Sistema</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalCvs }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-file-earmark-person-fill fs-1 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Actions Grid -->
        <h4 class="mb-4 fw-bold text-secondary"><i class="bi bi-grid-fill me-2"></i>Gestión del Portal</h4>
        <div class="row g-4 mb-5">

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-primary mx-auto shadow-sm">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Empresas</h5>
                        <p class="card-text text-muted small mb-4">Administrar empresas registradas.
                        </p>
                        <a href="{{ route('admin.empresas.index') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Gestionar</a>
                    </div>
                </div>
            </div>
            <!-- Card: Gestionar Candidatos -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-primary mx-auto shadow-sm">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Candidatos</h5>
                        <p class="card-text text-muted small mb-4">Administrar usuarios registrados, ver perfiles y estados.
                        </p>
                        <a href="{{ route('admin.candidatos.index') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Gestionar</a>
                    </div>
                </div>
            </div>

            <!-- Card: Ofertas de Trabajo -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-warning mx-auto shadow-sm">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Ofertas de Trabajo</h5>
                        <p class="card-text text-muted small mb-4">Listar, filtrar y editar las ofertas creadas.</p>
                        <a href="{{ route('admin.ofertas.index') }}"
                            class="btn btn-outline-warning btn-action w-100 stretched-link">Ver ofertas</a>
                    </div>
                </div>
            </div>


            <!-- Card: Configuración del Portal -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-secondary text-white mx-auto shadow-sm">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Configuración</h5>
                        <p class="card-text text-muted small mb-4">Ajustes generales del sitio y variables globales.</p>
                        <a href="{{ route('admin.configuracion.index') }}"
                            class="btn btn-outline-secondary btn-action w-100 stretched-link">Editar</a>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- Analytics Section -->
        <h4 class="mb-4 fw-bold text-secondary"><i class="bi bi-wallet-fill me-2"></i>
            Gestión Análitica</h4>
        <div class="row g-4">
            <!-- Card: Estadísticas -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-success text-white mx-auto shadow-sm">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Estadísticas</h5>
                        <p class="card-text text-muted small mb-4">Ver métricas y e stadísticas del portal en tiempo real.
                        </p>
                        <a href="{{ route('admin.analytics.index') }}"
                            class="btn btn-outline-success btn-action w-100 stretched-link">Ir a Estadísticas</a>
                    </div>
                </div>
            </div>

            <!-- Card: Tipos de Estadísticas -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-info mx-auto shadow-sm">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Tipos de Estadísticas</h5>
                        <p class="card-text text-muted small mb-4">Configurar tipos de estadísticas para el portal.
                        </p>
                        <a href="{{ route('admin.analytics_models.index') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Tipos de Estadísticas</a>
                    </div>
                </div>
            </div>

        </div>
        <br>


        <!-- Economic Section -->
        <h4 class="mb-4 fw-bold text-secondary"><i class="bi bi-wallet-fill me-2"></i>
            Otros</h4>
        <div class="row g-4">
            <!-- Card: Gestionar Bonos -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-info mx-auto shadow-sm">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Catálogo de Bonos</h5>
                        <p class="card-text text-muted small mb-4">Crear y editar los paquetes de bonos disponibles para
                            empresas.</p>
                        <a href="{{ route('admin.bonos.index') }}"
                            class="btn btn-outline-success btn-action w-100 stretched-link">Administrar</a>
                    </div>
                </div>
            </div>

            <!-- Card: Chatbot IA -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-info mx-auto shadow-sm">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Chatbot IA</h5>
                        <p class="card-text text-muted small mb-4">Configurar prompts, reglas y conocimiento del asistente.
                        </p>
                        <a href="{{ route('admin.ai_prompts.index') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Configurar</a>
                    </div>
                </div>
            </div>


            <!-- Card: Localidades -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-info mx-auto shadow-sm">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Localidades</h5>
                        <p class="card-text text-muted small mb-4">Gestionar localidades y provincias del portal.</p>
                        <a href="{{ route('admin.localidades.index') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Gestionar</a>
                    </div>
                </div>
            </div>

            <!-- Card: Contactos Comerciales -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-info mx-auto shadow-sm">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Contactos Comerciales</h5>
                        <p class="card-text text-muted small mb-4">Gestionar contactos comerciales del portal (academías,
                            inmobiliarias, etc).</p>
                        <a href="{{ route('admin.contactos-comerciales.index') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Gestionar</a>
                    </div>
                </div>
            </div>


        </div>

        {{-- Creación rápida de oferta para admins --}}
        <div class="row g-4 mt-5">
            <div class="col-12">
                <div class="card shadow border-0 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-rocket-takeoff-fill me-2 text-white"></i>Crear Oferta Rápida
                        </h5>
                    </div>
                    <div class="card-body bg-light bg-opacity-25 p-4">
                        <form action="{{ route('admin.ofertas.store') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <!-- First Row: Basic Info -->
                                <div class="col-md-6">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Título de la
                                        Oferta</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white border-end-0 text-primary"><i
                                                class="bi bi-briefcase-fill"></i></span>
                                        <input type="text" name="title" class="form-control border-start-0 ps-0"
                                            placeholder="Ej: Senior Laravel Developer" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Empresa
                                        Asignada</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white border-end-0 text-primary"><i
                                                class="bi bi-building"></i></span>
                                        <select name="company_profile_id" class="form-select border-start-0 ps-0">
                                            <option value="0">--- Sin empresa (Interna) ---</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Second Row: Description & Requirements -->
                                <div class="col-md-6">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Descripción del
                                        Puesto</label>
                                    <div class="input-group">
                                        <span
                                            class="input-group-text bg-white border-end-0 text-secondary align-items-start pt-3"><i
                                                class="bi bi-card-text"></i></span>
                                        <textarea name="description" rows="4" class="form-control border-start-0 ps-0"
                                            placeholder="Detalles principales de la posición..." required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Requisitos
                                        Mínimos</label>
                                    <div class="input-group">
                                        <span
                                            class="input-group-text bg-white border-end-0 text-secondary align-items-start pt-3"><i
                                                class="bi bi-list-check"></i></span>
                                        <textarea name="requirements" rows="4" class="form-control border-start-0 ps-0"
                                            placeholder="Experiencia, estudios, idiomas..." required></textarea>
                                    </div>
                                </div>

                                <!-- Third Row: Technical Details -->
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Modalidad</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="bi bi-laptop"></i></span>
                                        <select name="modality" class="form-select border-start-0 ps-0" required>
                                            <option value="presencial">Presencial</option>
                                            <option value="remoto">Remoto</option>
                                            <option value="hibrido">Híbrido</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Contrato</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="bi bi-file-earmark-text"></i></span>
                                        <select name="contract_type" class="form-select border-start-0 ps-0" required>
                                            <option value="Indefinido">Indefinido</option>
                                            <option value="Temporal">Temporal</option>
                                            <option value="Freelance">Freelance</option>
                                            <option value="Prácticas">Prácticas</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Estado
                                        Inicial</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="bi bi-toggle-on"></i></span>
                                        <select name="status" class="form-select border-start-0 ps-0">
                                            <option value="Borrador">Borrador</option>
                                            <option value="Publicado" selected>Publicado</option>
                                            <option value="Finalizada">Finalizada</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Salario
                                        (Opcional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="bi bi-currency-euro"></i></span>
                                        <input type="text" name="salary_range"
                                            class="form-control border-start-0 ps-0" placeholder="Ej: 24k - 30k">
                                    </div>
                                </div>

                                <!-- Fourth Row: Location & Actions -->
                                <div class="col-md-12">
                                    <label class="form-label text-uppercase fw-bold text-muted small">Ubicación</label>
                                    <div class="input-group w-100">
                                        <span class="input-group-text bg-white border-end-0 text-danger"><i
                                                class="bi bi-geo-alt-fill"></i></span>
                                        <input type="text" name="location" id="admin_location"
                                            style="min-width: 95% !important;"
                                            class="form-control border-start-0 ps-0 w-100"
                                            placeholder="Escribe para buscar ciudad..." required autocomplete="off">
                                        <input type="hidden" name="province" id="admin_province">
                                        <input type="hidden" name="island" id="admin_island">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit"
                                        class="btn btn-dark w-100 py-2 fw-bold shadow-lg text-uppercase"
                                        style="letter-spacing: 1px;">
                                        <i class="bi bi-plus-lg me-2"></i>Lanzar Oferta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    @include('components.location-autocomplete-script', [
        'citySelector' => '#admin_location',
        'countrySelector' => null,
        'provinceSelector' => '#admin_province',
        'islandSelector' => '#admin_island',
    ])
@endsection
