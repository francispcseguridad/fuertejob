@extends('layouts.app')

@section('title', 'Detalle de Oferta: ' . $jobOffer->title)

@php
    $showCompany = $jobOffer->company_visible ?? true;
    $companyName = $showCompany ? $jobOffer->companyProfile->company_name ?? 'Empresa' : 'Empresa Confidencial';
@endphp

@section('meta')
    <meta property="og:title" content="Oferta de Trabajo: {{ $jobOffer->title }} | {{ $companyName }} | FuerteJob">
    <meta property="og:description" content="{{ Str::limit(strip_tags($jobOffer->description), 160) }}">
    <meta property="og:image" content="{{ asset('img/logofacebook.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image" content="{{ asset('img/logowhatsapp.png') }}">
    <meta property="og:image:width" content="630">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ route('public.jobs.show', $jobOffer->id) }}">
    <meta property="og:type" content="article">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Oferta de Trabajo: {{ $jobOffer->title }} | FuerteJob">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($jobOffer->description), 160) }}">
    <meta name="twitter:image" content="{{ asset('img/logofacebook.png') }}">
@endsection

@section('content')
    <div class="container py-4">
        @php
            $showCompany = $jobOffer->company_visible ?? true;
            $companyName = $showCompany ? $jobOffer->companyProfile->company_name ?? 'Empresa' : 'Empresa Confidencial';
        @endphp
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold text-dark">{{ $jobOffer->title }}</h1>
                <p class="text-muted lead mb-0">
                    <i class="fas fa-building me-2"></i>{{ $companyName }}
                </p>
                <div class="mt-2">
                    <span class="badge bg-light text-dark border me-2"><i
                            class="fas fa-map-marker-alt text-danger me-1"></i>
                        {{ $jobOffer->location }}</span>
                    <span class="badge bg-light text-dark border me-2">
                        @if ($jobOffer->modality === 'remoto')
                            <i class="fas fa-wifi text-primary me-1"></i> Remoto
                        @elseif($jobOffer->modality === 'hibrido')
                            <i class="fas fa-home text-info me-1"></i> Híbrido
                        @else
                            <i class="fas fa-building text-secondary me-1"></i> Presencial
                        @endif
                    </span>
                    <span class="badge bg-light text-dark border"><i class="fas fa-money-bill-wave text-success me-1"></i>
                        {{ $jobOffer->salary_range ?? 'Salario no disponible' }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('worker.jobs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al listado
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Columna Principal --}}
            <div class="col-lg-8">
                {{-- Descripción --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-align-left me-2"></i>Descripción del Puesto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="card-text" style="white-space: pre-line;">{{ $jobOffer->description }}</div>
                    </div>
                </div>

                {{-- Requisitos --}}
                @if ($jobOffer->requirements)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list-check me-2"></i>Requisitos</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-text" style="white-space: pre-line;">{{ $jobOffer->requirements }}</div>
                        </div>
                    </div>
                @endif

                {{-- Beneficios --}}
                @if ($jobOffer->benefits)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-gift me-2"></i>Beneficios</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-text" style="white-space: pre-line;">{{ $jobOffer->benefits }}</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Columna Lateral --}}
            <div class="col-lg-4">
                {{-- Tarjeta de Acción (Inscribirse) --}}
                <div class="card shadow border-0 mb-4">
                    <div class="card-body p-4 text-center">
                        @if ($hasApplied)
                            <div class="alert alert-success border-0 shadow-sm mb-3">
                                <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                                <h5 class="fw-bold">¡Ya estás inscrito!</h5>
                                <p class="mb-0 small">Te inscribiste el {{ now()->locale('es')->diffForHumans() }}
                                    (simulado).</p>
                                {{-- Idealmente mostrar la fecha real de la inscripción si se pasa desde el controlador --}}
                            </div>
                            <button class="btn btn-secondary w-100 disabled" disabled>Ya Inscrito</button>
                        @else
                            <h5 class="fw-bold mb-3">¿Te interesa esta vacante?</h5>
                            <button type="button" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm"
                                id="btn-apply-initial">
                                <i class="fas fa-paper-plane me-2"></i>Inscribirme Ahora
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Skills y Tecnologías --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-tools me-2 text-secondary"></i>Stack Tecnológico</h6>
                    </div>
                    <div class="card-body">
                        @if ($jobOffer->skills->count() > 0)
                            <p class="small text-muted text-uppercase fw-bold mb-2">Habilidades</p>
                            <div class="mb-3">
                                @foreach ($jobOffer->skills as $skill)
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mb-1">{{ $skill->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if ($jobOffer->tools->count() > 0)
                            <p class="small text-muted text-uppercase fw-bold mb-2">Herramientas</p>
                            <div class="mb-3">
                                @foreach ($jobOffer->tools as $tool)
                                    <span
                                        class="badge bg-info-subtle text-info border border-info-subtle rounded-pill mb-1">{{ $tool->name }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if (isset($jobOffer->required_languages) && !empty($jobOffer->required_languages))
                            <p class="small text-muted text-uppercase fw-bold mb-2">Idiomas</p>
                            <div>
                                @foreach ($jobOffer->required_languages as $lang)
                                    <span
                                        class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill mb-1">{{ $lang }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Info Adicional --}}
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body small text-muted">
                        <p class="mb-1"><strong>Contrato:</strong> {{ $jobOffer->contract_type }}</p>
                        <p class="mb-1"><strong>Publicada:</strong>
                            {{ $jobOffer->created_at->locale('es')->isoFormat('LL') }}</p>
                    </div>
                </div>

                {{-- Compartir oferta --}}
                @include('components.share_buttons', [
                    'title' => 'Comparte esta oferta',
                    'text' => 'FuerteJob - ' . $jobOffer->title,
                ])
            </div>
        </div>
    </div>

    {{-- MODAL DE INSCRIPCIÓN --}}
    <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="applicationModalLabel"><i
                            class="fas fa-file-signature me-2"></i>Completar
                        Inscripción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="applicationForm">
                        @csrf
                        <input type="hidden" name="job_offer_id" value="{{ $jobOffer->id }}">

                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-info-circle me-1"></i> Estás a punto de inscribirte como
                            <strong>{{ Auth::user()->name }}</strong>.
                        </div>

                        <div class="mb-3">
                            <label for="time_to_hire_days" class="form-label fw-bold small text-uppercase">Disponibilidad
                                (Días)</label>
                            <input type="number" class="form-control" id="time_to_hire_days" name="time_to_hire_days"
                                placeholder="Ej. 15, 30, 0 (Inmediata)" min="0">
                            <div class="form-text">Días necesarios para incorporarte (preaviso).</div>
                        </div>

                        <div class="mb-3">
                            <label for="initial_assessment" class="form-label fw-bold small text-uppercase">Mensaje /
                                Breve Presentación</label>
                            <textarea class="form-control" id="initial_assessment" name="initial_assessment" rows="3"
                                placeholder="¿Por qué eres el candidato ideal? (Opcional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary fw-bold px-4" id="btn-submit-application">
                        Confirmar e Inscribirme
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnApplyInitial = document.getElementById('btn-apply-initial');
            const applicationModal = new bootstrap.Modal(document.getElementById('applicationModal'));
            const btnSubmitApplication = document.getElementById('btn-submit-application');

            if (btnApplyInitial) {
                btnApplyInitial.addEventListener('click', function() {
                    // 1. SweetAlert Confirm
                    Swal.fire({
                        title: '¿Deseas inscribirte?',
                        text: "Te inscribirás en el proceso de selección para esta oferta.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // 2. Open Modal if confirmed
                            applicationModal.show();
                        }
                    });
                });
            }

            // 3. Handle AJAX Submission
            btnSubmitApplication.addEventListener('click', function() {
                const form = document.getElementById('applicationForm');
                const formData = new FormData(form);
                const submitBtn = this;

                // Disable button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';

                fetch("{{ route('worker.jobs.apply') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            applicationModal.hide();
                            Swal.fire({
                                title: '¡Inscripción Exitosa!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Excelente'
                            }).then(() => {
                                window.location.reload(); // Reload to update status
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message ||
                                    'Ocurrió un error al procesar la solicitud.',
                                icon: 'error'
                            });
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Confirmar e Inscribirme';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servidor.',
                            icon: 'error'
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Confirmar e Inscribirme';
                    });
            });
        });
    </script>
@endsection
