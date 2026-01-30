@extends('layouts.app')

@section('title', $jobOffer->title . ' - FuerteJob')

@php
    $showCompany = $jobOffer->company_visible ?? true;
    $companyName = $showCompany ? $jobOffer->companyProfile->company_name ?? 'Empresa' : 'Empresa Confidencial';

    // og:image must always be the FuerteJob logo
    $socialImage = asset('img/logofacebook.png');
@endphp

@section('meta')
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('public.jobs.show', $jobOffer->id) }}">
    <meta property="og:title"
        content="Oferta de Trabajo: {{ $jobOffer->title }} - {{ $jobOffer->islandRelation->name ?? ($jobOffer->island ?? 'Canarias') }} | FuerteJob">
    <meta property="og:description"
        content="Entra en FuerteJob.com para obtener más información sobre esta oferta o encontrar más ofertas de empleos.">
    <meta property="og:image" content="{{ asset('img/logofacebook.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image" content="{{ asset('img/logowhatsapp.png') }}">
    <meta property="og:image:width" content="630">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="FuerteJob">
    <meta property="og:locale" content="es_ES">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ route('public.jobs.show', $jobOffer->id) }}">
    <meta name="twitter:title"
        content="Oferta de Trabajo: {{ $jobOffer->title }} - {{ $jobOffer->islandRelation->name ?? ($jobOffer->island ?? 'Canarias') }} | FuerteJob">
    <meta name="twitter:description"
        content="Entra en FuerteJob.com para obtener más información sobre esta oferta o encontrar más ofertas de empleos.">
    <meta name="twitter:image" content="{{ asset('img/logofacebook.png') }}">

    {{-- Primordial for Google --}}
    <meta name="description" content="{{ Str::limit(strip_tags($jobOffer->description), 160) }}">
    <link rel="canonical" href="{{ route('public.jobs.show', $jobOffer->id) }}">
@endsection

@section('content')
    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('public.jobs.index') }}">Empleos</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $jobOffer->title }}</li>
            </ol>
        </nav>

        <div class="row">
            {{-- Columna Principal --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            @if ($showCompany && $jobOffer->companyProfile && $jobOffer->companyProfile->logo_url)
                                <img src="{{ asset($jobOffer->companyProfile->logo_url) }}" alt="{{ $companyName }}"
                                    class="me-3 rounded shadow-sm"
                                    style="width: 80px; height: 80px; object-fit: contain; background: white; border: 1px solid #eee;">
                            @else
                                <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center"
                                    style="width: 80px; height: 80px; border: 1px solid #eee;">
                                    <i class="bi bi-building fs-2 text-muted"></i>
                                </div>
                            @endif
                            <div>
                                <h1 class="h2 fw-bold mb-1">{{ $jobOffer->title }}</h1>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-building me-1"></i> {{ $companyName }}
                                </p>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt text-danger me-1"></i>
                                {{ $jobOffer->location }}</span>
                            <span class="badge bg-light text-dark border">
                                @if ($jobOffer->modality === 'remoto')
                                    <i class="bi bi-wifi text-primary me-1"></i> Remoto
                                @elseif($jobOffer->modality === 'hibrido')
                                    <i class="bi bi-house text-info me-1"></i> Híbrido
                                @else
                                    <i class="bi bi-building text-secondary me-1"></i> Presencial
                                @endif
                            </span>
                            <span class="badge bg-light text-dark border"><i class="bi bi-cash-stack text-success me-1"></i>
                                {{ $jobOffer->salary_range ?? 'Salario no disponible' }}</span>
                            <span class="badge bg-light text-dark border"><i class="bi bi-briefcase text-warning me-1"></i>
                                {{ $jobOffer->contract_type }}</span>
                        </div>

                        <hr class="mb-4">

                        <h5 class="fw-bold mb-3"><i class="bi bi-text-left me-2"></i>Descripción del Puesto</h5>
                        <div class="card-text mb-5" style="white-space: pre-line; line-height: 1.6;">
                            {{ $jobOffer->description }}</div>

                        @if ($jobOffer->requirements)
                            <h5 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Requisitos</h5>
                            <div class="card-text mb-5" style="white-space: pre-line; line-height: 1.6;">
                                {{ $jobOffer->requirements }}</div>
                        @endif

                        @if ($jobOffer->benefits)
                            <h5 class="fw-bold mb-3"><i class="bi bi-gift me-2"></i>Beneficios</h5>
                            <div class="card-text mb-4" style="white-space: pre-line; line-height: 1.6;">
                                {{ $jobOffer->benefits }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Columna Lateral --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">¿Te interesa?</h5>
                        <p class="text-muted small mb-4">Inicia sesión como candidato para inscribirte en esta oferta.</p>

                        @auth
                            @if (Auth::user()->role === 'trabajador')
                                <a href="{{ route('worker.jobs.show', $jobOffer->id) }}"
                                    class="btn btn-primary btn-lg w-100 fw-bold mb-3 shadow-sm">
                                    <i class="bi bi-send me-2"></i>Ver e Inscribirme
                                </a>
                            @else
                                <div class="alert alert-warning small mb-3">
                                    Solo los candidatos pueden inscribirse en ofertas.
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login', ['redirect' => route('public.jobs.show', $jobOffer->id)]) }}"
                                class="btn btn-primary btn-lg w-100 fw-bold mb-3 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Inicia Sesión
                            </a>
                            <a href="{{ route('worker.register.form') }}" class="btn btn-outline-primary w-100 fw-bold">
                                Crea tu cuenta gratis
                            </a>
                        @endauth

                        <hr class="my-4">

                        @include('components.share_buttons', [
                            'url' => route('public.jobs.show', $jobOffer->id),
                            'title' => $jobOffer->title,
                            'text' => 'Mira esta oferta de empleo en FuerteJob: ' . $jobOffer->title,
                        ])
                    </div>
                </div>

                @if ($jobOffer->skills->count() > 0 || $jobOffer->tools->count() > 0)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-warning"></i>Habilidades
                                Requeridas
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach ($jobOffer->skills as $skill)
                                <span class="badge bg-light text-dark border mb-1">{{ $skill->name }}</span>
                            @endforeach
                            @foreach ($jobOffer->tools as $tool)
                                <span class="badge bg-light text-dark border mb-1">{{ $tool->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
