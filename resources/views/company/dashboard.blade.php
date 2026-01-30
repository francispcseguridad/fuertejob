@extends('layouts.app')
@section('title', 'Panel de Gestión')
@section('content')
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">Panel de Control</h1>
                <p class="text-muted lead">Bienvenido, {{ Auth::user()->name ?? 'Empresa' }}</p>
            </div>
            <div>
                <span class="badge bg-light text-dark border p-2 rounded-pill">
                    <i class="bi bi-calendar-event me-1"></i> {{ date('d/m/Y') }}
                </span>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row g-4 mb-5">
            <!-- Stat 1: Ofertas Activas -->
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-primary h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-primary fw-bold mb-1">Ofertas Activas</h6>
                            <h2 class="mb-0 fw-bold">{{ $ofertasvigentes ? $ofertasvigentes : 0 }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-briefcase fs-1 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat 2: Candidatos Nuevos -->
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-success h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-success fw-bold mb-1">Candidatos Nuevos</h6>
                            <h2 class="mb-0 fw-bold">{{ $candidatos_inscritos ? $candidatos_inscritos : 0 }}</h2>
                        </div>
                        <div class="text-gray-300">
                            <i class="bi bi-people fs-1 text-gray-300 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat 3: Recursos Disponibles -->
            <div class="col-md-4">
                <div class="card shadow-sm stat-card border-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="text-uppercase text-warning fw-bold mb-1">Recursos Disponibles</h6>
                                <div class="small text-muted">Lo que aún puedes usar</div>
                            </div>
                            <div class="text-gray-300">
                                <i class="bi bi-coin fs-1 text-gray-300 opacity-25"></i>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Ofertas que puedes publicar</span>
                                <span class="fw-bold text-dark">{{ $availableOfferCredits }}</span>
                            </li>
                            <li class="d-flex justify-content-between mb-2">
                                <span class="text-muted">CV que puedes ver</span>
                                <span class="fw-bold text-dark">{{ $availableCvViews }}</span>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="text-muted">Usuarios activos</span>
                                <span class="fw-bold text-dark">{{ $availableUserSeats }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Actions Grid -->
        <h4 class="mb-4 fw-bold text-secondary"><i class="bi bi-grid-fill me-2"></i>Gestión Corporativa</h4>
        <div class="row g-4">

            <!-- Card: Modificar Perfil -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-info mx-auto shadow-sm">
                            <i class="bi bi-building"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Mi Perfil</h5>
                        <p class="card-text text-muted small mb-4">Actualiza la información visible de tu empresa, logo y
                            datos de contacto.</p>
                        <a href="{{ route('empresa.profile.index') }}"
                            class="btn btn-outline-info btn-action w-100 stretched-link">Modificar</a>
                    </div>
                </div>
            </div>

            <!-- Card: Crear Oferta -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-gradient-primary mx-auto shadow-sm">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Publicar Oferta</h5>
                        <p class="card-text text-muted small mb-4">Crea una nueva vacante para encontrar al candidato ideal.
                        </p>
                        <a href="{{ route('empresa.ofertas.create') }}"
                            class="btn btn-outline-primary btn-action w-100 stretched-link">Crear</a>
                    </div>
                </div>
            </div>

            <!-- Card: Ver Ofertas -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-white border border-2 text-primary mx-auto shadow-sm">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Mis Ofertas</h5>
                        <p class="card-text text-muted small mb-4">Gestiona tus ofertas activas, revisa candidatos y cierra
                            vacantes.</p>
                        <a href="{{ route('empresa.ofertas.index') }}"
                            class="btn btn-outline-dark btn-action w-100 stretched-link">Ver Ofertas</a>
                    </div>
                </div>
            </div>

            <!-- Card: Finanzas / Facturas -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-secondary text-white mx-auto shadow-sm">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h5 class="card-title fw-bold mt-3">Facturación</h5>
                        <p class="card-text text-muted small mb-4">Consulta y descarga tus facturas y revisa tu historial de
                            pagos.</p>
                        <a href="{{ route('empresa.invoices.index') }}"
                            class="btn btn-outline-secondary btn-action w-100 stretched-link">Ver Facturas</a>
                    </div>
                </div>
            </div>

            <!-- Card: Comprar Bonos (Full Width or highlight) -->
            <div class="col-12 mt-4">
                <div class="card shadow border-0 bg-gradient-warning text-white overflow-hidden dashboard-card">
                    <div class="card-body p-5 position-relative">
                        <div class="row align-items-center position-relative z-10">
                            <div class="col-md-8">
                                <h2 class="fw-bold mb-2">Potencia tu reclutamiento</h2>
                                <p class="mb-4 fs-5 opacity-75">Adquiere bonos para destacar tus ofertas y acceder a la base
                                    de datos de candidatos.</p>
                                <a href="{{ route('empresa.comprar') }}"
                                    class="btn btn-light text-warning fw-bold btn-lg shadow-sm px-5 rounded-pill">
                                    Comprar Bonos
                                </a>
                            </div>
                            <div class="col-md-4 text-center d-none d-md-block">
                                <i class="bi bi-stars display-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
