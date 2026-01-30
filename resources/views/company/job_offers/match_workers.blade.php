@extends('layouts.app')

@section('title', 'Candidatos Coincidentes: ' . $oferta->title)

@section('content')
    <div class="container py-5">
        <!-- Header Mejorado -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('empresa.dashboard') }}"
                                    class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('empresa.ofertas.index') }}"
                                    class="text-decoration-none">Mis Ofertas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Candidatos</li>
                        </ol>
                    </nav>
                    <h1 class="display-5 fw-bold text-dark mb-2">Candidatos Coincidentes </h1>
                    <p class="text-muted lead mb-0 d-flex align-items-center">
                        <i class="bi bi-briefcase-fill text-primary me-2 fs-5"></i>
                        <span class="fw-semibold">{{ $oferta->title }}</span>
                    </p>
                </div>
                <div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('empresa.ofertas.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contador de Candidatos Seleccionados -->
            <div class="alert alert-info d-flex align-items-center rounded-3 shadow-sm" role="alert">
                <i class="bi bi-star-fill flex-shrink-0 me-2 fs-4"></i>
                <div>
                    Tiene **{{ $selectedCandidatesCount ?? 0 }}** candidatos seleccionados para esta oferta.
                    <span class="small opacity-75">Puede contactarlos directamente.</span>
                </div>
            </div>
        </div>

        <!-- Información de la Oferta - Diseño Premium -->
        <div class="card shadow border-0 mb-5 overflow-hidden">
            <div class="card-body p-0">
                <div class="bg-gradient-primary text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-box bg-white bg-opacity-25 me-3"
                                    style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                                    <i class="bi bi-search fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1">Búsqueda de Candidatos</h5>
                                    <p class="mb-0 opacity-90">
                                        Hemos encontrado <span
                                            class="badge bg-white text-primary fw-bold fs-6 mx-1">{{ $candidates->count() }}</span>
                                        candidatos potenciales que coinciden con los requisitos de esta oferta.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center d-none d-md-block">
                            <i class="bi bi-people-fill display-3 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($candidates->isEmpty())
            <!-- Estado Vacío Mejorado -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 px-4">
                    <div class="mb-4">
                        <div class="icon-box bg-light mx-auto mb-3"
                            style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <i class="bi bi-search display-3 text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">No se encontraron candidatos coincidentes</h3>
                    <p class="text-muted lead mb-4 mx-auto" style="max-width: 600px;">
                        Intenta refinar los criterios de tu oferta o espera a que nuevos candidatos se registren en nuestra
                        plataforma.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('empresa.ofertas.edit', $oferta) }}" class="btn btn-primary px-4 rounded-pill">
                            <i class="bi bi-pencil-square me-2"></i>Editar Oferta
                        </a>
                        <a href="{{ route('empresa.ofertas.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                            <i class="bi bi-list-ul me-2"></i>Ver Todas las Ofertas
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Lista de Candidatos Premium -->
            <div class="row g-4">
                @foreach ($candidates as $candidate)
                    {{-- Comprobamos si el candidato ya está seleccionado --}}
                    @php
                        $isSelected = in_array($candidate->id, $selectedCandidateIds ?? []);
                    @endphp

                    <div class="col-12">
                        <div class="card shadow-sm border-0 h-100 candidate-card">
                            <div class="card-body p-4">
                                <div class="row align-items-start">
                                    <!-- Avatar Mejorado -->
                                    <div class="col-auto">
                                        <div class="position-relative">
                                            @if ($candidate->profile_photo_path)
                                                <img src="{{ asset('storage/' . $candidate->profile_photo_path) }}"
                                                    alt="{{ $candidate->full_name }}"
                                                    class="rounded-circle border border-3 border-primary shadow-sm"
                                                    style="width: 90px; height: 90px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center border border-3 border-white shadow"
                                                    style="width: 90px; height: 90px; font-size: 2rem; font-weight: bold;">
                                                    {{ strtoupper(substr($candidate->user->first_name ?? 'C', 0, 1) . substr($candidate->user->last_name ?? 'A', 0, 1)) }}
                                                </div>
                                            @endif
                                            <!-- Badge de Estado Online -->
                                            <span
                                                class="position-absolute bottom-0 end-0 bg-success border border-3 border-white rounded-circle"
                                                style="width: 24px; height: 24px;" title="Activo recientemente">
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Información Principal Mejorada -->
                                    <div class="col">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h4 class="fw-bold text-dark mb-2">
                                                    {{ $candidate->user->full_name ?? 'Candidato Desconocido' }}</h4>
                                                <p class="text-primary fw-semibold mb-0 d-flex align-items-center">
                                                    <i class="bi bi-person-badge me-2"></i>
                                                    {{ $candidate->headline ?? 'Profesional en búsqueda activa' }}
                                                </p>
                                            </div>
                                            {{-- Mostrar el badge de seleccionado --}}
                                            @if ($isSelected)
                                                <span
                                                    class="badge bg-success rounded-pill px-3 py-2 shadow-sm selected-badge">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Seleccionado
                                                </span>
                                            @else
                                                <span class="badge bg-info rounded-pill px-3 py-2 shadow-sm">
                                                    <i class="bi bi-search me-1"></i>Potencial
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Detalles del Candidato - Grid Mejorado (Manteniendo la estructura) -->
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-4">
                                                <div class="info-box p-3 bg-light rounded-3 h-100">
                                                    <div class="d-flex align-items-start">
                                                        <div
                                                            class="icon-badge bg-danger bg-opacity-10 text-danger rounded-circle p-2 me-3">
                                                            <i class="bi bi-geo-alt-fill"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="text-muted small mb-1">Ubicación</div>
                                                            <div class="text-dark fw-semibold">
                                                                {{ $candidate->current_location ?? 'No especificada' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-box p-3 bg-light rounded-3 h-100">
                                                    <div class="d-flex align-items-start">
                                                        <div
                                                            class="icon-badge bg-success bg-opacity-10 text-success rounded-circle p-2 me-3">
                                                            <i class="bi bi-calendar-check"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="text-muted small mb-1">Disponibilidad</div>
                                                            <div class="text-dark fw-semibold">
                                                                {{ $candidate->availability_status ?? 'Tiempo completo' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-box p-3 bg-light rounded-3 h-100">
                                                    <div class="d-flex align-items-start">
                                                        <div
                                                            class="icon-badge bg-info bg-opacity-10 text-info rounded-circle p-2 me-3">
                                                            <i class="bi bi-clock-history"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="text-muted small mb-1">Última actualización</div>
                                                            <div class="text-dark fw-semibold">
                                                                {{ $candidate->updated_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Habilidades Mejoradas -->
                                        @if ($candidate->skills()->count())
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bi bi-star-fill text-warning me-2"></i>
                                                    <span class="text-muted small fw-semibold">Habilidades
                                                        coincidentes</span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    {{-- Asumiendo que ahora $candidate->skills() es la relación --}}
                                                    @foreach ($candidate->skills as $skill)
                                                        <span
                                                            class="badge bg-white border border-2 text-dark px-3 py-2 rounded-pill shadow-sm">
                                                            {{ $skill->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Acciones Mejoradas - CON BOTÓN DE SELECCIÓN -->
                                        <div class="d-flex gap-2 pt-3 border-top">
                                            {{-- Botón de Seleccionar/Deseleccionar --}}
                                            <form method="POST"
                                                action="{{ route('empresa.candidatos.toggle_selection') }}"
                                                class="flex-grow-1">
                                                @csrf
                                                <input type="hidden" name="worker_profile_id"
                                                    value="{{ $candidate->id }}">
                                                <input type="hidden" name="job_offer_id" value="{{ $oferta->id }}">

                                                <button type="submit"
                                                    class="btn {{ $isSelected ? 'btn-outline-danger' : 'btn-primary' }} flex-grow-1 rounded-pill"
                                                    title="{{ $isSelected ? 'Deseleccionar este candidato' : 'Seleccionar este candidato para guardarlo' }}">
                                                    <i class="bi {{ $isSelected ? 'bi-x-lg' : 'bi-check-lg' }} me-2"></i>
                                                    {{ $isSelected ? 'Deseleccionar' : 'Seleccionar Candidato' }}
                                                </button>
                                            </form>

                                            {{-- Botón Ver Perfil Completo --}}
                                            <a href="#" class="btn btn-outline-secondary flex-grow-1 rounded-pill"
                                                title="Ver perfil completo del candidato">
                                                <i class="bi bi-eye me-2"></i>Ver Perfil
                                            </a>

                                            {{-- Botón Invitar a Aplicar (Mantenido) --}}
                                            <button type="button" class="btn btn-success flex-grow-1 rounded-pill"
                                                title="Invitar al candidato a aplicar">
                                                <i class="bi bi-envelope-check me-2"></i>Invitar a Aplicar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Mejorado -->
                            <div class="card-footer bg-gradient-light border-0 py-3 px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-percent text-primary me-2"></i>
                                        <span class="text-muted small me-2">Coincidencia:</span>
                                        <span class="badge bg-primary fw-bold px-3 py-2 rounded-pill match-score-badge">
                                            {{ round($candidate->match_score * 100) }}%
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-patch-check-fill text-primary me-2"></i>
                                        <span class="fw-semibold">Perfil verificado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            @if (method_exists($candidates, 'links'))
                <div class="mt-5">
                    {{ $candidates->links() }}
                </div>
            @endif

            <!-- Información Adicional Mejorada -->
            <div class="card border-0 shadow-sm mt-5">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center mb-3 mb-md-0">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning mx-auto"
                                style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                                <i class="bi bi-lightbulb-fill fs-3"></i>
                            </div>
                        </div>
                        <div class="col-md-11">
                            <h6 class="fw-bold mb-3 text-dark">
                                Consejos para contactar candidatos de manera efectiva
                            </h6>
                            <ul class="row g-2 mb-0 small text-muted">
                                <li class="col-md-6">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Revisa el perfil completo antes de invitar a aplicar
                                </li>
                                <li class="col-md-6">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Personaliza tu mensaje mencionando aspectos específicos
                                </li>
                                <li class="col-md-6">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Responde rápidamente para mantener el interés
                                </li>
                                <li class="col-md-6">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    Guarda candidatos interesantes para futuras oportunidades
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>


@endsection
