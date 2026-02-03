@extends('layouts.app')

@section('content')
    {{-- Contenedor principal centrado y con sombra --}}
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg border-0 rounded-4">

                    {{-- CABECERA: BIENVENIDA Y RESUMEN PROFESIONAL --}}
                    <div class="card-header p-4 text-white rounded-top-4" style="background-color: #1d486c;">
                        <div class="d-flex justify-content-between align-items-center">

                            {{-- Título y Cargo --}}
                            <div>
                                <h1 class="mb-1 fw-bold">
                                    ¡Bienvenido, {{ $profile->first_name }}!
                                </h1>
                                <p class="lead mb-0 opacity-75">
                                    @if ($profile->professional_summary)
                                        {{ Str::limit($profile->professional_summary, 80) }}
                                    @else
                                        Gestor de Perfil de Trabajador
                                    @endif
                                </p>
                            </div>

                            {{-- Foto de Perfil Redonda --}}
                            <img src="{{ asset($profile->profile_image_url) }}" alt="Foto de Perfil"
                                class="rounded-circle border border-4 border-white shadow-sm"
                                style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                    </div>


                    <div class="card-body p-4 p-md-5">
                        {{-- Mensajes de Sesión --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <p class="text-muted mb-4">
                            Este es tu centro de control profesional. Utiliza los accesos directos para mantener tu perfil
                            siempre atractivo y actualizado.
                        </p>

                        {{-- Lógica de Verificación del CV --}}
                        @php
                            // Asumimos que tienes una relación 'cv' en WorkerProfile que retorna el modelo CV.
                            // Si tienes una relación 'cvs' (HasMany) que retorna una colección, usa: $cvLoaded = $profile->cvs->isNotEmpty();
                            // Aquí asumimos una relación HasOne/BelongsTo llamada 'cv':
                            $cvLoaded = $profile->cv()->exists();
                        @endphp

                        {{-- ESTADÍSTICAS BÁSICAS --}}
                        <h4 class="mb-4 text-secondary fw-semibold">Tu Rendimiento</h4>
                        <div class="row g-3 mb-5">
                            {{-- Experiencias --}}
                            <div class="col-md-4">
                                <div class="p-4 border rounded-3 text-center shadow-sm bg-light">
                                    <i class="fas fa-briefcase fa-2x text-info mb-2"></i>
                                    <h6 class="text-muted text-uppercase mb-1">Experiencias</h6>
                                    <p class="display-6 fw-bold mb-0 text-info">
                                        {{ $profile->experiences->count() }}</p>
                                </div>
                            </div>
                            {{-- Educación --}}
                            <div class="col-md-4">
                                <div class="p-4 border rounded-3 text-center shadow-sm bg-light">
                                    <i class="fas fa-graduation-cap fa-2x text-success mb-2"></i>
                                    <h6 class="text-muted text-uppercase mb-1">Educación</h6>
                                    <p class="display-6 fw-bold mb-0 text-success">
                                        {{ $profile->educations->count() }}</p>
                                </div>
                            </div>
                            {{-- CV Cargado (Corregido) --}}
                            <div class="col-md-4">
                                <div class="p-4 border rounded-3 text-center shadow-sm bg-light">
                                    <i
                                        class="fas fa-file-pdf fa-2x mb-2 {{ $cvLoaded ? 'text-danger' : 'text-warning' }}"></i>
                                    <h6 class="text-muted text-uppercase mb-1">CV Base</h6>
                                    <p class="display-6 fw-bold mb-0 {{ $cvLoaded ? 'text-success' : 'text-danger' }}">
                                        {{ $cvLoaded ? 'Sí' : 'No' }}</p>
                                </div>
                            </div>
                        </div>


                        {{-- SECCIÓN DE RE-ANÁLISIS DEL CV Y MENSAJE DE ESTADO --}}
                        @if ($cvLoaded)
                            <div id="reanalyze-status-card"
                                class="card bg-warning-subtle border-warning-subtle shadow-sm mb-5">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-robot fa-2x text-warning me-2"></i>
                                        <span class="fw-semibold text-dark">
                                            Tu CV ya está en nuestra base de datos.
                                            <p id="reanalyze-message" class="mt-1 text-xs fw-normal text-muted mb-0">
                                                Último estado: Perfil cargado.
                                            </p>
                                        </span>
                                    </div>
                                    <button type="button" id="reanalyze-button" class="btn btn-warning fw-bold"
                                        data-bs-toggle="modal" data-bs-target="#reanalyzeModal">
                                        Re-analizar con Gemini <i class="fas fa-redo ms-1"></i>
                                    </button>

                                    {{-- Botón para extraer foto --}}
                                    <a href="{{ route('worker.profile.edit') }}" class="btn btn-outline-dark fw-bold">
                                        <i class="fas fa-camera me-1"></i> Editar Perfil
                                    </a>

                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger mb-5" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                **Atención:** Aún no has subido tu CV. Por favor, sube tu CV para que
                                Gemini pueda completar tu perfil.
                            </div>
                        @endif


                        {{-- ACCIONES PRINCIPALES (GRID 2x3) --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-primary text-white shadow-lg border-0 rounded-4 overflow-hidden">
                                    <div class="row g-0">
                                        <div class="col-md-8 p-4">
                                            <h3 class="fw-bold mb-2">¡Simplifica tu Perfil! </h3>
                                            <p class="mb-4 opacity-90">Ahora puedes actualizar tu experiencia, formación,
                                                habilidades e idiomas desde una sola pantalla, sin complicaciones.</p>
                                            <a href="{{ route('worker.profile.simplified') }}"
                                                class="btn btn-light btn-lg fw-bold px-4 rounded-pill shadow">
                                                <i class="bi bi-pencil-square me-2"></i> Actualizar Perfil Completo
                                            </a>
                                        </div>
                                        <div
                                            class="col-md-4 d-none d-md-flex align-items-center justify-content-center bg-white bg-opacity-10">
                                            <i class="bi bi-person-badge display-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h4 class="mt-5 mb-4 text-secondary fw-semibold">Gestión Detallada</h4>
                        <hr class="mb-4">

                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            {{-- Fila 1 --}}
                            <div class="col">
                                <a href="{{ route('worker.profile.edit') }}"
                                    class="btn btn-outline-primary w-100 h-100 p-4 shadow-sm d-flex flex-column align-items-center justify-content-center border-2"
                                    style="min-height: 150px;">
                                    <i class="fas fa-user-edit fa-3x mb-2"></i>
                                    <span class="fw-bold fs-6 mt-2">Editar Datos Personales</span>
                                </a>
                            </div>
                            <div class="col">
                                <a href="{{ route('worker.experiencias.index') }}"
                                    class="btn btn-outline-primary w-100 h-100 p-4 shadow-sm d-flex flex-column align-items-center justify-content-center border-2"
                                    style="min-height: 150px;">
                                    <i class="fas fa-list-alt fa-3x mb-2"></i>
                                    <span class="fw-bold fs-6 mt-2">Gestionar Experiencias</span>
                                </a>
                            </div>
                            <div class="col">
                                <a href="{{ route('worker.educacion.index') }}"
                                    class="btn btn-outline-primary w-100 h-100 p-4 shadow-sm d-flex flex-column align-items-center justify-content-center border-2"
                                    style="min-height: 150px;">
                                    <i class="fas fa-book-reader fa-3x mb-2"></i>
                                    <span class="fw-bold fs-6 mt-2">Gestionar Formación</span>
                                </a>
                            </div>

                            {{-- Fila 2 (Nuevos campos) --}}
                            <div class="col">
                                {{-- Asumiendo una ruta para gestionar Habilidades --}}
                                <a href="{{ route('worker.habilidades.index') }}"
                                    class="btn btn-outline-primary w-100 h-100 p-4 shadow-sm d-flex flex-column align-items-center justify-content-center border-2"
                                    style="min-height: 150px;">
                                    <i class="fas fa-cogs fa-3x mb-2"></i>
                                    <span class="fw-bold fs-6 mt-2">Gestionar Habilidades</span>
                                </a>
                            </div>
                            <div class="col">
                                {{-- Asumiendo una ruta para gestionar Herramientas/Software --}}
                                <a href="{{ route('worker.herramientas.index') }}"
                                    class="btn btn-outline-primary w-100 h-100 p-4 shadow-sm d-flex flex-column align-items-center justify-content-center border-2"
                                    style="min-height: 150px;">
                                    <i class="fas fa-wrench fa-3x mb-2"></i>
                                    <span class="fw-bold fs-6 mt-2">Gestionar Herramientas</span>
                                </a>
                            </div>
                            <div class="col">
                                {{-- Asumiendo una ruta para gestionar Idiomas --}}
                                <a href="{{ route('worker.idiomas.index') }}"
                                    class="btn btn-outline-primary w-100 h-100 p-4 shadow-sm d-flex flex-column align-items-center justify-content-center border-2                                    style="min-height:
                                    150px;">
                                    <i class="fas fa-language fa-3x mb-2"></i>
                                    <span class="fw-bold fs-6 mt-2">Gestionar Idiomas</span>
                                </a>
                            </div>
                        </div>

                        {{-- Sección de Candidaturas --}}
                        <h4 class="mt-5 mb-4 text-secondary fw-semibold">Tus Candidaturas</h4>
                        <hr class="mb-4">

                        @if ($candidaturas->isEmpty())
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                Aún no te has inscrito a ninguna oferta de empleo.
                                <a href="{{ route('worker.jobs.index') }}" class="alert-link">Explora las ofertas
                                    disponibles</a>.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Oferta</th>
                                            <th scope="col">Empresa</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Prioridad</th>
                                            <th scope="col">Fecha de Inscripción</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($candidaturas as $candidatura)
                                            <tr>
                                                {{-- Título de la Oferta --}}
                                                <td>
                                                    <div class="fw-semibold text-dark">
                                                        {{ $candidatura->jobOffer->title }}
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ $candidatura->jobOffer->location }}
                                                    </small>
                                                </td>

                                                {{-- Empresa --}}
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if ($candidatura->jobOffer->companyProfile->logo_url)
                                                            <img src="{{ asset($candidatura->jobOffer->companyProfile->logo_url) }}"
                                                                alt="Logo {{ $candidatura->jobOffer->companyProfile->company_name }}"
                                                                class="rounded me-2"
                                                                style="width: 32px; height: 32px; object-fit: cover;">
                                                        @endif
                                                        <span class="fw-medium">
                                                            {{ $candidatura->jobOffer->companyProfile->company_name }}
                                                        </span>
                                                    </div>
                                                </td>

                                                {{-- Estado --}}
                                                <td>
                                                    @php
                                                        $statusBadgeClass = match ($candidatura->current_status) {
                                                            'preseleccionado' => 'bg-info',
                                                            'entrevista_programada' => 'bg-primary',
                                                            'en_proceso' => 'bg-warning',
                                                            'finalista' => 'bg-success',
                                                            'contratado' => 'bg-success',
                                                            'descartado' => 'bg-danger',
                                                            'rechazado' => 'bg-danger',
                                                            default => 'bg-secondary',
                                                        };

                                                        $statusLabel = match ($candidatura->current_status) {
                                                            'preseleccionado' => 'Preseleccionado',
                                                            'entrevista_programada' => 'Entrevista Programada',
                                                            'en_proceso' => 'En Proceso',
                                                            'finalista' => 'Finalista',
                                                            'contratado' => 'Contratado',
                                                            'descartado' => 'Descartado',
                                                            'rechazado' => 'Rechazado',
                                                            default => ucfirst(
                                                                str_replace('_', ' ', $candidatura->current_status),
                                                            ),
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusBadgeClass }} text-white">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </td>

                                                {{-- Prioridad --}}
                                                <td>
                                                    @php
                                                        $priorityBadgeClass = match ($candidatura->priority) {
                                                            'alta' => 'bg-danger',
                                                            'media' => 'bg-warning',
                                                            'baja' => 'bg-secondary',
                                                            default => 'bg-light text-dark',
                                                        };

                                                        $priorityLabel = match ($candidatura->priority) {
                                                            'alta' => 'Alta',
                                                            'media' => 'Media',
                                                            'baja' => 'Baja',
                                                            default => 'N/A',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $priorityBadgeClass }}">
                                                        {{ $priorityLabel }}
                                                    </span>
                                                </td>

                                                {{-- Fecha de Inscripción --}}
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $candidatura->selection_date->format('d/m/Y') }}
                                                    </small>
                                                </td>

                                                {{-- Acciones --}}
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('worker.jobs.show', $candidatura->jobOffer->id) }}"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Ver detalles de la oferta" aria-label="Ver detalles">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-cancel-application"
                                                            data-offer-id="{{ $candidatura->jobOffer->id }}"
                                                            title="Anular inscripción" aria-label="Anular inscripción">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Resumen de candidaturas --}}
                            <div class="mt-3 text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Mostrando {{ $candidaturas->count() }} candidatura(s) activa(s).
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE RE-ANÁLISIS --}}
    <div class="modal fade" id="reanalyzeModal" tabindex="-1" aria-labelledby="reanalyzeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="reanalyzeModalLabel"><i class="fas fa-redo me-2"></i> Confirmar
                        Re-análisis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres volver a enviar tu CV a **Gemini** para su análisis?</p>
                    <p class="text-muted small">Esto sobrescribirá cualquier dato de perfil que Gemini haya generado
                        previamente a partir de ese CV (Experiencia, Educación, Habilidades, etc.).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    {{-- Formulario de acción --}}
                    <button type="button" id="confirmReanalyzeBtn" class="btn btn-warning fw-bold">
                        <i class="fas fa-robot me-1"></i> Sí, Re-analizar CV
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Asegúrate de tener jQuery si usas los métodos de Bootstrap para el modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmBtn = document.getElementById('confirmReanalyzeBtn');
            const reanalyzeButton = document.getElementById('reanalyze-button');
            const messageBox = document.getElementById('reanalyze-message');
            const reanalyzeModal = new bootstrap.Modal(document.getElementById('reanalyzeModal'));
            const cancelButtons = document.querySelectorAll('.btn-cancel-application');
            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]').content;

            // Escucha el click en el botón de confirmación dentro del modal
            confirmBtn.addEventListener('click', async function(e) {
                e.preventDefault();

                // Desactivar botones y cerrar modal
                confirmBtn.disabled = true;
                reanalyzeButton.disabled = true;
                reanalyzeModal.hide(); // Cierra el modal

                // Mostrar mensaje de carga
                messageBox.textContent =
                    'Procesando CV... Esto puede tardar 5-15 segundos. Por favor, no cierres la página.';
                messageBox.className = 'mt-1 text-sm fw-normal text-primary mb-0';

                // Obtener el token CSRF (asumiendo que está en una meta tag, estándar de Laravel)
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    const response = await fetch("{{ route('worker.cv.reanalyze') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken // Importante para la seguridad en Laravel
                        },
                        // No se necesita body, el CV se busca en el servidor usando el ID del usuario autenticado
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Éxito
                        messageBox.textContent = data.message + ' (Fuente: ' + (data.source ||
                            'Desconocida') + '). Recargando perfil...';
                        messageBox.className = 'mt-1 text-sm fw-normal text-success mb-0';

                        // Recargar la página después de un breve retraso para que el usuario vea el mensaje
                        setTimeout(() => window.location.reload(), 3000);
                    } else {
                        // Error del servidor (ej. 500, o 404 si no encuentra el CV)
                        messageBox.textContent = data.message ||
                            'Error desconocido al reanalizar. Revisa los logs.';
                        messageBox.className = 'mt-1 text-sm fw-normal text-danger mb-0';
                    }

                } catch (error) {
                    // Error de red
                    messageBox.textContent =
                        'Error de conexión. Asegúrate de tener conexión a internet.';
                    messageBox.className = 'mt-1 text-sm fw-normal text-danger mb-0';
                    console.error('Fetch Error:', error);
                } finally {
                    // Si hubo un error que no recarga, reactivamos los botones.
                    if (confirmBtn.disabled) {
                        confirmBtn.disabled = false;
                        reanalyzeButton.disabled = false;
                    }
                }
            });

            // Anular inscripción desde el dashboard
            cancelButtons.forEach((btn) => {
                btn.addEventListener('click', function() {
                    const offerId = this.dataset.offerId;

                    Swal.fire({
                        title: '¿Anular inscripción?',
                        text: 'Se eliminará tu candidatura en esta oferta.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, anular',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        fetch("{{ route('worker.jobs.cancel') }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    job_offer_id: offerId,
                                })
                            })
                            .then(response => response.json().then(data => ({
                                ok: response.ok,
                                body: data
                            })))
                            .then(({
                                ok,
                                body
                            }) => {
                                if (ok && body.success) {
                                    Swal.fire('Inscripción anulada', body.message,
                                            'success')
                                        .then(() => window.location.reload());
                                } else {
                                    Swal.fire('No se pudo anular', body.message ||
                                        'Intenta nuevamente.', 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Error de conexión',
                                    'No se pudo completar la anulación.', 'error');
                            });
                    });
                });
            });
        });
    </script>
@endsection
