@extends('layouts.app')
@section('title', 'Editar Candidato | ' . $worker->name)

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.9);
            --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .edit-container {
            background-color: #f4f7f6;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .profile-header-card {
            background: #fff;
            color: black;
            border-radius: 20px;
            border: none;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .nav-pills-custom .nav-link {
            color: #6c757d;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.3s;
            border: 1px solid transparent;
            margin-bottom: 0.5rem;
        }

        .nav-pills-custom .nav-link.active {
            background-color: white !important;
            color: #4e73df !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #e3e6f0;
        }

        .nav-pills-custom .nav-link i {
            margin-right: 10px;
        }

        .content-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        }

        .avatar-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .avatar-main {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 30px;
            border: 4px solid rgba(255, 255, 255, 0.2);
        }

        .action-btn-pill {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .action-btn-pill:hover {
            transform: translateY(-2px);
        }

        .form-label-custom {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #9fa6b2;
            margin-bottom: 0.5rem;
        }

        .cv-viewer-container {
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #e3e6f0;
            background: #f8f9fc;
        }

        .competence-badge {
            background: #f0f2f5;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>

    <div class="edit-container py-4">
        <div class="container">

            {{-- Superior: Breadcrumb y Volver --}}
            <nav aria-label="breadcrumb" class="mb-4 d-flex justify-content-between align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.candidatos.index') }}">Candidatos</a></li>
                    <li class="breadcrumb-item active">{{ $worker->name }}</li>
                </ol>
                <a href="{{ route('admin.candidatos.index') }}"
                    class="btn btn-light btn-sm rounded-pill px-3 shadow-sm border">
                    <i class="bi bi-arrow-left me-1"></i> Panel principal
                </a>
            </nav>

            {{-- Perfil Header --}}
            <div class="card profile-header-card shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-md-auto text-center mb-4 mb-md-0">
                            <div class="avatar-wrapper shadow-lg">
                                @if ($worker->workerProfile && $worker->workerProfile->profile_image_url)
                                    <img src="{{ asset($worker->workerProfile->profile_image_url) }}" class="avatar-main">
                                @else
                                    <div class="avatar-main bg-white d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person-fill text-black" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <h2 class="h1 fw-bold mb-0 text-black">{{ $worker->name }}</h2>
                                <span
                                    class="badge {{ $worker->email_verified_at ? 'bg-success' : 'bg-warning' }} rounded-pill px-3 py-2">
                                    {{ $worker->email_verified_at ? 'Verificado' : 'Pendiente' }}
                                </span>
                            </div>
                            <p class="mb-3 opacity-75 lead"><i class="bi bi-envelope-at me-2"></i>{{ $worker->email }}</p>

                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-white action-btn-pill shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#emailModal">
                                    <i class="bi bi-send-fill me-2 text-primary"></i>Enviar Email
                                </button>

                                @php
                                    $phone = optional($worker->workerProfile)->phone_number;
                                    $whatsappUrl = $phone
                                        ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone)
                                        : '#';
                                @endphp

                                <a href="{{ $whatsappUrl }}" target="_blank"
                                    class="btn btn-success action-btn-pill shadow-sm {{ !$phone ? 'disabled opacity-50' : '' }}">
                                    <i class="bi bi-whatsapp me-2"></i>WhatsApp
                                </a>

                                <button id="deleteWorkerButton" class="btn btn-danger action-btn-pill shadow-sm">
                                    <i class="bi bi-trash3 me-2"></i>Borrar Perfil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Navegación Lateral --}}
                <div class="col-lg-3">
                    <div class="nav flex-column nav-pills nav-pills-custom" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical">
                        <button class="nav-link active" id="pill-info-tab" data-bs-toggle="pill" data-bs-target="#pill-info"
                            type="button" role="tab">
                            <i class="bi bi-person-circle"></i> Datos Personales
                        </button>
                        <button class="nav-link" id="pill-cv-tab" data-bs-toggle="pill" data-bs-target="#pill-cv"
                            type="button" role="tab">
                            <i class="bi bi-file-earmark-pdf"></i> Curriculum y CV
                        </button>
                        <button class="nav-link" id="pill-skills-tab" data-bs-toggle="pill" data-bs-target="#pill-skills"
                            type="button" role="tab">
                            <i class="bi bi-stars"></i> Competencias y Formación
                        </button>
                        <button class="nav-link" id="pill-security-tab" data-bs-toggle="pill"
                            data-bs-target="#pill-security" type="button" role="tab">
                            <i class="bi bi-shield-lock"></i> Seguridad
                        </button>
                    </div>
                </div>

                {{-- Contenido Principal --}}
                <div class="col-lg-9">
                    <div class="tab-content" id="v-pills-tabContent">

                        {{-- Tab 1: Datos Personales --}}
                        <div class="tab-pane fade show active" id="pill-info" role="tabpanel">
                            <div class="card content-card p-4">
                                <h4 class="fw-bold mb-4 text-dark"><i
                                        class="bi bi-person-lines-fill me-2 text-primary"></i>Información del Perfil</h4>

                                <form action="{{ route('admin.candidatos.update', $worker) }}" method="POST"
                                    class="mb-5">
                                    @csrf @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Nombre Completo</label>
                                            <input type="text" name="name"
                                                class="form-control form-control-lg bg-light"
                                                value="{{ old('name', $worker->name) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Email Principal</label>
                                            <input type="email" name="email"
                                                class="form-control form-control-lg bg-light"
                                                value="{{ old('email', $worker->email) }}" required>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit"
                                                class="btn btn-primary rounded-pill px-5 fw-bold">Guardar Cambios</button>
                                        </div>
                                    </div>
                                </form>

                                <hr class="my-4 text-muted opacity-25">

                                @php $profile = $worker->workerProfile; @endphp
                                @if ($profile)
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label-custom">Teléfono</label>
                                            <p class="fw-semibold text-dark">{{ $profile->phone_number ?? '—' }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-custom">Ubicación</label>
                                            <p class="fw-semibold text-dark">{{ $profile->city }},
                                                {{ $profile->country }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-custom">Disponibilidad</label>
                                            <span
                                                class="badge {{ $profile->is_available ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} rounded-pill">
                                                {{ $profile->is_available ? 'Inmediata' : 'No disponible' }}
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label-custom">Resumen Profesional</label>
                                            <div class="p-3 bg-light rounded-4 text-secondary small"
                                                style="line-height: 1.6;">
                                                {{ $profile->professional_summary ?? 'Sin descripción cargada.' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-5 bg-light rounded-4">
                                        <i class="bi bi-person-plus fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">El candidato aún no ha completado su perfil detallado.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tab 2: Curriculum --}}
                        <div class="tab-pane fade" id="pill-cv" role="tabpanel">
                            <div class="card content-card p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="fw-bold mb-0 text-dark"><i
                                            class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>Visor de Curriculum
                                    </h4>
                                    @if (isset($primaryCv) && $primaryCv)
                                        <button id="admin-reanalyze-btn"
                                            class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                                            <i class="bi bi-robot me-1"></i> Re-analizar con IA
                                        </button>
                                    @endif
                                </div>

                                @if (isset($primaryCv) && $primaryCv)
                                    <div id="admin-reanalyze-status" class="mb-3 small fw-bold"></div>
                                    <div class="cv-viewer-container shadow-inner">
                                        <iframe src="{{ route('cvs.serve', $primaryCv) }}"
                                            style="width:100%; height:700px; border:none;"></iframe>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <a href="{{ route('cvs.serve', $primaryCv) }}" target="_blank"
                                            class="btn btn-outline-secondary btn-sm rounded-pill">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana completa
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <img src="https://illustrations.popsy.co/gray/not-found.svg" style="width: 200px;"
                                            class="mb-3">
                                        <h5 class="text-muted">No hay documento PDF disponible</h5>
                                        <p class="small text-muted">El candidato no ha subido su CV todavía.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Tab 3: Competencias --}}
                        <div class="tab-pane fade" id="pill-skills" role="tabpanel">
                            <div class="card content-card p-4 mb-4">
                                <h5 class="fw-bold mb-4"><i class="bi bi-stars text-warning me-2"></i>Habilidades y
                                    Herramientas</h5>

                                @if ($profile)
                                    <div class="mb-4">
                                        <label class="form-label-custom d-block mb-3">Hard Skills (Técnicas)</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse($profile->skills as $skill)
                                                <span class="competence-badge">{{ $skill->name }}</span>
                                            @empty
                                                <span class="text-muted small italic">No hay registros</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label-custom d-block mb-3">Herramientas & Software</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse($profile->tools as $tool)
                                                <span
                                                    class="competence-badge border-warning-subtle">{{ $tool->name }}</span>
                                            @empty
                                                <span class="text-muted small italic">No hay registros</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="card content-card p-4">
                                <h5 class="fw-bold mb-4"><i class="bi bi-briefcase-fill text-primary me-2"></i>Trayectoria
                                    Laboral</h5>
                                @if ($profile && $profile->experiences->isNotEmpty())
                                    @foreach ($profile->experiences as $exp)
                                        <div class="d-flex gap-3 mb-4 border-start border-3 border-primary ps-3">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $exp->position }}</h6>
                                                <p class="text-primary small mb-1 fw-semibold">{{ $exp->job_title }}</p>
                                                <small class="text-muted d-block mb-2">
                                                    {{ \Carbon\Carbon::parse($exp->start_date)->isoFormat('MMM YYYY') }} -
                                                    {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->isoFormat('MMM YYYY') : 'Actualidad' }}
                                                </small>
                                                <p class="small text-secondary mb-0">{{ $exp->description }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted text-center py-3">Sin experiencia registrada.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Tab 4: Seguridad --}}
                        <div class="tab-pane fade" id="pill-security" role="tabpanel">
                            <div class="card content-card p-4">
                                <h4 class="fw-bold mb-4 text-warning"><i class="bi bi-key-fill me-2"></i>Control de
                                    Seguridad</h4>
                                <p class="text-muted mb-4 small">Utiliza este formulario para forzar el restablecimiento de
                                    la contraseña del candidato.</p>

                                <form
                                    action="{{ route('admin.candidatos.password.update', ['solicitante' => $worker->id]) }}"
                                    method="POST">
                                    @csrf @method('PUT')
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Nueva Contraseña</label>
                                            <input type="password" name="password" class="form-control bg-light"
                                                placeholder="Min. 8 caracteres">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom">Confirmar Contraseña</label>
                                            <input type="password" name="password_confirmation"
                                                class="form-control bg-light">
                                        </div>
                                        <div class="col-12">
                                            <button type="submit"
                                                class="btn btn-warning text-white fw-bold rounded-pill px-4">
                                                Actualizar Seguridad
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Email (Idéntico a tu lógica actual pero estilizado) --}}
    <div class="modal fade" id="emailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-light" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-envelope-paper me-2 text-primary"></i>Nuevo Mensaje
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="emailForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label-custom">Asunto del Correo</label>
                            <input type="text" class="form-control border-0 bg-light rounded-3" id="emailSubject"
                                name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Mensaje para {{ $worker->name }}</label>
                            <textarea class="form-control border-0 bg-light rounded-3" id="emailMessage" name="message" rows="6" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-toggle="modal">Cancelar</button>
                    <button type="button" id="sendEmailBtn" class="btn btn-primary rounded-pill px-4">
                        Enviar Ahora <i class="bi bi-send ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts: He mantenido tu lógica exacta para Delete y Re-analyze --}}
    <form id="delete-form" action="{{ route('admin.candidatos.destroy', $worker) }}" method="POST"
        style="display: none;">
        @csrf @method('DELETE')
    </form>

    <script>
        // Tu lógica de SweetAlert para borrar
        document.getElementById('deleteWorkerButton').addEventListener('click', function() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará permanentemente al candidato.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-pill px-4 mx-2',
                    cancelButton: 'btn btn-light rounded-pill px-4 mx-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('delete-form').submit();
            });
        });

        // Lógica IA
        document.addEventListener('DOMContentLoaded', () => {
            const reanalyzeBtn = document.getElementById('admin-reanalyze-btn');
            const statusBox = document.getElementById('admin-reanalyze-status');
            if (!reanalyzeBtn) return;

            reanalyzeBtn.addEventListener('click', async () => {
                reanalyzeBtn.disabled = true;
                reanalyzeBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>IA Analizando...';
                statusBox.textContent = 'Procesando CV... un momento.';
                statusBox.className = 'text-primary mb-3 small fw-bold';

                try {
                    const response = await fetch(
                        "{{ route('admin.candidatos.cv.reanalyze', $worker) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                    const data = await response.json();
                    if (response.ok) {
                        statusBox.textContent = '¡Análisis completado! Recargando...';
                        statusBox.className = 'text-success mb-3 small fw-bold';
                        setTimeout(() => window.location.reload(), 2000);
                    }
                } catch (e) {
                    statusBox.textContent = 'Error en el análisis.';
                    reanalyzeBtn.disabled = false;
                }
            });
        });

        // Lógica Email
        document.getElementById('sendEmailBtn').addEventListener('click', async function() {
            const btn = this;
            const subject = document.getElementById('emailSubject').value;
            const message = document.getElementById('emailMessage').value;
            if (!subject || !message) return Swal.fire('Error', 'Completa los campos', 'error');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

            try {
                const response = await fetch("{{ route('admin.candidatos.email.send', $worker->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        subject,
                        message
                    })
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire('Éxito', 'Correo enviado', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
                }
            } catch (e) {
                Swal.fire('Error', 'No se pudo enviar', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Enviar Ahora <i class="bi bi-send ms-1"></i>';
            }
        });
    </script>

@endsection
