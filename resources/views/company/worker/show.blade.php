@extends('layouts.app')

@section('title', 'Perfil de ' . ($worker->user->name ?? 'Trabajador'))

@section('content')
    <div class="container py-4">
        @php
            $hasJobOfferContext = !empty($jobOfferId);
        @endphp
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>

        <div class="row g-4">
            {{-- Columna Izquierda: Información Detallada --}}
            <div class="col-lg-7">
                <div class="card shadow-lg border-0 rounded-4 mb-4">
                    <div class="card-body p-5">
                        {{-- Header del Perfil --}}
                        <div class="d-flex align-items-start mb-4">
                            @php
                                $initials = '';
                                if ($worker->user->name ?? false) {
                                    $parts = explode(' ', $worker->user->name);
                                    // Intenta obtener la primera letra del primer y último nombre
                                    $initials = strtoupper(
                                        substr($parts[0], 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''),
                                    );
                                } else {
                                    $initials = 'NA'; // No Available
                                }
                            @endphp
                            <img src="{{ $worker->user->profile_picture ?? 'https://placehold.co/100x100/A0BFFF/FFFFFF?text=' . $initials }}"
                                alt="Foto de {{ $worker->user->name }}"
                                onerror="this.onerror=null;this.src='https://placehold.co/100x100/A0BFFF/FFFFFF?text={{ $initials }}';"
                                class="rounded-circle me-4 border border-4 border-light shadow-sm"
                                style="width: 100px; height: 100px; object-fit: cover;">

                            <div>
                                <h1 class="h3 fw-bold text-dark mb-0">
                                    {{ $cvUnlocked ? $worker->user->name ?? 'Nombre No Disponible' : 'Destapar Curriculum' }}
                                </h1>
                                <p class="lead text-primary fw-semibold mb-2">
                                    {{ $worker->profession_title ?? 'Sin Título de Profesión' }}</p>

                                <span class="badge bg-primary-subtle text-primary border border-primary me-2">
                                    <i class="bi bi-house-door-fill me-1"></i>
                                    {{ ucfirst($worker->preferred_modality ?? 'N/A') }}
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                    <i class="bi bi-geo-alt-fill me-1"></i> {{ $worker->city ?? 'N/A' }},
                                    {{ $worker->country ?? 'N/A' }}
                                </span>
                                @if ($worker->desiredSectors->isNotEmpty())
                                    <div class="mt-3">
                                        <p class="text-muted mb-2 small fw-semibold text-uppercase">
                                            Sectores de interés
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($worker->desiredSectors as $sector)
                                                <span class="badge bg-success-subtle text-success fw-normal rounded-pill">
                                                    {{ $sector->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Biografía --}}
                        <h5 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2"><i
                                class="bi bi-file-earmark-text me-2"></i>Sobre Mí</h5>
                        <p class="text-muted">{{ $worker->bio ?? 'Biografía no proporcionada.' }}</p>

                        {{-- Contacto y Salario --}}
                        <h5 class="fw-bold text-dark mt-4 mb-3 border-bottom pb-2"><i
                                class="bi bi-info-circle-fill me-2"></i>Datos Clave</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="d-block fw-semibold text-secondary">Contacto (Email)</span>
                                <span class="text-dark fs-5">
                                    {{ $cvUnlocked ? $worker->user->email ?? 'N/A' : 'Destapar Curriculum' }}
                                </span>
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>

                        {{-- Habilidades, Herramientas e Idiomas --}}
                        <h5 class="fw-bold text-dark mt-5 mb-3 border-bottom pb-2"><i
                                class="bi bi-stars me-2"></i>Competencias</h5>

                        {{-- Habilidades Técnicas --}}
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-2"><i class="bi bi-laptop me-1 text-info"></i>Habilidades
                                Técnicas:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($worker->skills as $skill)
                                    <span
                                        class="badge bg-info-subtle text-info fw-normal rounded-pill">{{ $skill->name }}</span>
                                @empty
                                    <span class="text-muted small">No hay habilidades registradas.</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Herramientas --}}
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-2"><i
                                    class="bi bi-wrench-adjustable-fill me-1 text-warning"></i>Herramientas:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($worker->tools as $tool)
                                    <span
                                        class="badge bg-warning-subtle text-warning fw-normal rounded-pill">{{ $tool->name }}</span>
                                @empty
                                    <span class="text-muted small">No hay herramientas registradas.</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Idiomas --}}
                        <div class="mb-4">
                            <h6 class="fw-semibold text-dark mb-2"><i class="bi bi-translate me-1 text-danger"></i>Idiomas:
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($worker->languages as $language)
                                    <span
                                        class="badge bg-danger-subtle text-danger fw-normal rounded-pill">{{ $language->name }}
                                        (Nivel: {{ $language->pivot->level ?? 'N/A' }})
                                    </span>
                                @empty
                                    <span class="text-muted small">No hay idiomas registrados.</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Experiencia Laboral --}}
                        <h5 class="fw-bold text-dark mt-5 mb-3 border-bottom pb-2"><i
                                class="bi bi-briefcase-fill me-2"></i>Experiencia Laboral</h5>
                        @forelse($worker->experiences->sortByDesc('start_year') as $job)
                            <div class="mb-3 border-start border-3 ps-3 border-primary">
                                <h6 class="fw-semibold mb-0">{{ $job->title }}</h6>
                                <p class="mb-1 small text-dark">{{ $job->company_name }}</p>
                                <p class="mb-1 small text-muted">
                                    {{ $job->start_year ?? '—' }} -
                                    @if ($job->end_year)
                                        {{ $job->end_year }}
                                    @else
                                        Presente
                                    @endif
                                </p>
                                <p class="small text-secondary">{{ $job->description }}</p>
                            </div>
                        @empty
                            <p class="text-muted">No hay experiencia laboral registrada.</p>
                        @endforelse

                        {{-- Educación --}}
                        <h5 class="fw-bold text-dark mt-5 mb-3 border-bottom pb-2"><i
                                class="bi bi-mortarboard-fill me-2"></i>Educación</h5>
                        @forelse($worker->educations->sortByDesc('end_year') as $study)
                            <div class="mb-3 border-start border-3 ps-3 border-success">
                                <h6 class="fw-semibold mb-0">{{ $study->degree }}</h6>
                                <p class="mb-1 small text-dark">{{ $study->institution }}</p>
                                <p class="mb-1 small text-muted">{{ $study->start_year }} - {{ $study->end_year }}</p>
                                <p class="small text-secondary">{{ $study->description }}</p>
                            </div>
                        @empty
                            <p class="text-muted">No hay educación registrada.</p>
                        @endforelse

                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Visor de CV --}}
            <div class="col-lg-5">
                {{-- sticky-top mantiene el visor visible al hacer scroll --}}
                <div class="card shadow-lg border-0 rounded-4 sticky-top cv-viewer-card">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0"><i
                                class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>Curriculum Vitae</h5>
                        @if ($primaryCv)
                            <span class="small text-muted d-block mt-1">Archivo: {{ $primaryCv->file_name }}</span>
                        @endif
                    </div>
                    <div class="card-body p-4 pt-0 cv-viewer-body">
                        @if ($primaryCv)
                            {{-- 
                                El iframe llama a la ruta segura 'cvs.serve', que es la encargada de:
                                1. Verificar los permisos del usuario (empresa).
                                2. Servir el archivo desde el almacenamiento privado.
                                3. Usar el encabezado 'Content-Disposition: inline' para incrustar el PDF.
                            --}}
                            <iframe src="{{ route('cvs.serve', $primaryCv) }}" width="100%" height="100%"
                                style="border: 1px solid #dee2e6; border-radius: 0.5rem;" title="Visor de Curriculum Vitae">
                                <p>Tu navegador no soporta iframes. <span class="text-muted small">Esto es necesario para
                                        evitar la descarga directa.</span></p>
                            </iframe>
                        @else
                            <div class="alert alert-warning text-center mt-3" role="alert">
                                <i class="bi bi-lock-fill me-2"></i>Este CV está protegido. Desbloquéalo para verlo.
                            </div>
                            <button id="unlock-cv-button" type="button" class="btn btn-primary w-100 mt-3 rounded-pill"
                                data-unlock-url="{{ route('empresa.trabajadores.unlock', ['workerProfile' => $worker, 'jobOffer' => $jobOfferId]) }}"
                                data-job-offer-id="{{ $jobOfferId ?? '' }}"
                                data-available-views="{{ $availableCvViews ?? 0 }}"
                                data-purchase-url="{{ $purchaseUrl ?? '' }}">
                                <i class="bi bi-key me-1"></i>
                                Destapar Curriculum
                            </button>
                            @unless ($hasJobOfferContext)
                                <p class="text-muted small mt-2 mb-0">
                                    Accede al perfil desde una oferta para poder aplicar el desbloqueo.
                                </p>
                            @endunless
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unlockButton = document.getElementById('unlock-cv-button');
            if (!unlockButton) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            unlockButton.addEventListener('click', async function() {
                const unlockUrl = this.dataset.unlockUrl;
                const purchaseUrl = this.dataset.purchaseUrl ||
                    '{{ route('empresa.bonos.catalogo') }}';
                const jobOfferId = this.dataset.jobOfferId;
                let availableViews = parseInt(this.dataset.availableViews ?? '0', 10);

                if (availableViews <= 0) {
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Sin créditos disponibles',
                        html: 'No tienes créditos suficientes. ¿Deseas comprar más para desbloquear este CV?',
                        showCancelButton: true,
                        confirmButtonText: 'Comprar créditos',
                        cancelButtonText: 'Cancelar',
                    });

                    if (result.isConfirmed && purchaseUrl) {
                        window.location.href = purchaseUrl;
                    }

                    return;
                }

                const confirmation = await Swal.fire({
                    icon: 'question',
                    title: 'Confirmar uso de crédito',
                    html: `Se descontará un crédito de CV para desbloquear este perfil. Te quedan ${availableViews} crédito(s).`,
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                });

                if (!confirmation.isConfirmed) {
                    return;
                }

                if (!csrfToken) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo verificar la sesión. Recarga la página e inténtalo de nuevo.',
                    });
                    return;
                }

                try {
                    const response = await fetch(unlockUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            job_offer_id: jobOfferId,
                        }),
                    });

                    const payload = await response.json().catch(() => null);

                    if (!response.ok || payload?.status === 'error') {
                        throw new Error(payload?.message || 'No se pudo desbloquear el CV.');
                    }

                    if (typeof payload?.available_cv_views === 'number') {
                        availableViews = payload.available_cv_views;
                        this.dataset.availableViews = availableViews;
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'Desbloqueado',
                        text: payload?.message ?? 'Puedes ver el CV completo ahora.',
                    });

                    window.location.reload();
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No se pudo desbloquear el CV.',
                    });
                }
            });
        });
    </script>
@endsection
