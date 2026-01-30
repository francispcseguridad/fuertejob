@extends('layouts.app')
@section('title', 'Editar Empresa | ' . $company->name)

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --admin-bg: #f0f2f5;
            --card-radius: 20px;
            --accent-color: #4e73df;
        }

        .main-wrapper {
            background-color: var(--admin-bg);
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        /* Header & Navigation */
        .glass-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: var(--card-radius);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Avatar & Profile */
        .company-avatar-box {
            width: 110px;
            height: 110px;
            background: white;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin: 0 auto 1.5rem;
            border: 1px solid #edf2f7;
        }

        /* Metrics Cards */
        .resource-card {
            background: white;
            border: none;
            border-radius: 18px;
            transition: transform 0.2s;
            border-bottom: 4px solid transparent;
        }

        .resource-card:hover {
            transform: translateY(-3px);
        }

        .res-offers {
            border-color: #4e73df;
        }

        .res-cvs {
            border-color: #36b9cc;
        }

        .res-seats {
            border-color: #f6ad55;
        }

        /* Forms & Inputs */
        .form-card {
            background: white;
            border-radius: var(--card-radius);
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .label-caps {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #a0aec0;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
        }

        /* Table Styling */
        .table-custom thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            border: none;
        }

        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
        }
    </style>

    <div class="main-wrapper py-4">
        <div class="container">

            @php
                $profile = $company->companyProfile;
                $jobOffers = optional($profile)->jobOffers ?? collect();
                $resourceBalance = optional($profile)->resourceBalance;
                $availableOfferCredits = (int) ($resourceBalance->available_offer_credits ?? 0);
                $availableCvViews = (int) ($resourceBalance->available_cv_views ?? 0);
                $availableUserSeats = (int) ($resourceBalance->available_user_seats ?? 0);
                $bonoPurchases = optional($profile)->purchases ?? collect();
                $contactPhone = optional($profile)->contact_phone ?? optional($profile)->phone;
                $whatsappUrl = $contactPhone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $contactPhone) : '#';
            @endphp

            {{-- Superior: Barra de Navegación --}}
            <div class="glass-header p-3 mb-5 d-flex justify-content-between align-items-center shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.empresas.index') }}"
                        class="btn btn-white btn-sm rounded-3 border shadow-sm px-3">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold">Gestión de Empresa</h5>
                        <small class="text-muted">ID: #{{ $company->id }} ·
                            {{ $company->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button class="btn btn-white btn-sm rounded-pill border shadow-sm px-3" data-bs-toggle="modal"
                        data-bs-target="#companyEmailModal">
                        <i class="bi bi-envelope-paper me-1 text-primary"></i>Enviar Email
                    </button>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                        class="btn btn-success btn-sm rounded-pill px-3 shadow-sm {{ $contactPhone ? '' : 'disabled opacity-50' }}">
                        <i class="bi bi-whatsapp me-1"></i>WhatsApp
                    </a>
                    <form action="{{ route('admin.empresas.destroy', $company->id) }}" method="POST" id="deleteForm">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete()"
                            class="btn btn-outline-danger btn-sm rounded-pill px-3">
                            <i class="bi bi-trash me-2"></i>Eliminar Empresa
                        </button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="row g-4">
                {{-- Columna Lateral: Perfil y Balance --}}
                <div class="col-xl-4">

                    {{-- Perfil Visual --}}
                    <div class="card form-card mb-4 overflow-hidden text-center p-4">
                        <div class="company-avatar-box">
                            <i class="bi bi-buildings-fill text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">{{ optional($profile)->company_name ?? $company->name }}</h4>
                        <p class="text-muted small mb-3"><i class="bi bi-envelope me-1"></i> {{ $company->email }}</p>
                        <div class="d-flex justify-content-center">
                            <span
                                class="status-badge {{ $company->email_verified_at ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                {{ $company->email_verified_at ? 'Verificado' : 'No Verificado' }}
                            </span>
                        </div>
                    </div>

                    {{-- Balance de Recursos (Kpis) --}}
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="label-caps ms-1"><i class="bi bi-pie-chart me-2"></i>Balance actual</h6>
                        </div>
                        <div class="col-4">
                            <div class="card resource-card res-offers shadow-sm p-3 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">Créditos</small>
                                <span class="h4 fw-bold mb-0 text-dark">{{ $availableOfferCredits }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card resource-card res-cvs shadow-sm p-3 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">CVs</small>
                                <span class="h4 fw-bold mb-0 text-dark">{{ $availableCvViews }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card resource-card res-seats shadow-sm p-3 text-center">
                                <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">Asientos</small>
                                <span class="h4 fw-bold mb-0 text-dark">{{ $availableUserSeats }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Detalles rápidos --}}
                    <div class="card form-card p-4">
                        <h6 class="label-caps mb-4">Información de contacto</h6>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Representante</label>
                            <span class="fw-bold text-dark">{{ optional($profile)->contact ?? 'Sin asignar' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Teléfono Principal</label>
                            <span class="fw-bold text-dark">{{ optional($profile)->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-0">
                            <label class="small text-muted d-block">Sitio Web</label>
                            <a href="{{ optional($profile)->website_url }}" target="_blank"
                                class="text-primary fw-bold text-decoration-none small">
                                {{ optional($profile)->website_url ?? 'No definido' }} <i
                                    class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha: Edición e Historial --}}
                <div class="col-xl-8">

                    {{-- Formulario Principal --}}
                    <div class="card form-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Configuración de
                                Datos</h5>
                            <span class="badge bg-light text-dark rounded-pill border px-3">Perfil Empresa</span>
                        </div>

                        <form action="{{ route('admin.empresas.update', $company->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="label-caps">Nombre de Usuario (Login)</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $company->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">Email corporativo</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $company->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">Nombre comercial</label>
                                    <input type="text" name="company_name" class="form-control"
                                        value="{{ old('company_name', optional($profile)->company_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">Estado de cuenta</label>
                                    <select name="activo" class="form-select">
                                        <option value="1" @selected(old('activo', optional($profile)->activo) == 1)>Cuenta Activa</option>
                                        <option value="2" @selected(old('activo', optional($profile)->activo) == 2)>Baja / Inactiva</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">Persona de contacto</label>
                                    <input type="text" name="contact" class="form-control"
                                        value="{{ old('contact', optional($profile)->contact) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">Ciudad</label>
                                    <input type="text" name="city" id="admin_company_city" class="form-control"
                                        value="{{ old('city', optional($profile)->city) }}" required>
                                    @error('city')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">País</label>
                                    <input type="text" name="country" id="admin_company_country" class="form-control"
                                        value="{{ old('country', optional($profile)->country) }}" required readonly>
                                    @error('country')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <input type="hidden" id="admin_company_province">
                                <input type="hidden" id="admin_company_island">
                                <div class="col-md-6">
                                    <label class="label-caps">VAT / CIF</label>
                                    <input type="text" name="vat_id" class="form-control"
                                        value="{{ old('vat_id', optional($profile)->vat_id) }}">
                                </div>
                                <div class="col-12">
                                    <label class="label-caps">Dirección Fiscal</label>
                                    <textarea name="fiscal_address" class="form-control" rows="2">{{ old('fiscal_address', optional($profile)->fiscal_address) }}</textarea>
                                </div>
                                <div class="col-12">
                                    @include('components.password_requirements')
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                        Guardar Cambios <i class="bi bi-save ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Historial de Adquisiciones --}}
                    <div class="card form-card overflow-hidden mb-4">
                        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0"><i class="bi bi-receipt text-primary me-2"></i>Historial de Bonos
                            </h5>
                            <button class="btn btn-light btn-sm rounded-pill fw-bold border">Ver todos</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Fecha</th>
                                        <th class="text-center">Créditos</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bonoPurchases as $purchase)
                                        @php
                                            $bono = $purchase->bonoCatalog;
                                            $isExtra = $bono?->is_extra ?? false;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    {{ optional($bono)->name ?? 'Bono Manual' }}</div>
                                                <span class="text-muted"
                                                    style="font-size: 0.7rem;">{{ $isExtra ? 'Servicio Extra' : 'Suscripción Plan' }}</span>
                                            </td>
                                            <td class="text-center small text-muted">
                                                {{ optional($purchase->purchase_date)->format('d M, Y') ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="fw-bold text-primary">{{ $isExtra ? optional($bono)->credit_cost : optional($bono)->offer_credits }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="status-badge {{ $purchase->payment_status === 'COMPLETADO' ? 'bg-success-subtle text-success' : 'bg-light text-muted' }}">
                                                    {{ $purchase->payment_status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">Sin movimientos
                                                comerciales registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Cambio de Password --}}
                    <div class="card form-card p-4 border-start border-4 border-warning">
                        <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2 text-warning"></i>Credenciales de Acceso
                        </h5>
                        <form action="{{ route('admin.empresas.password.update', $company->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="label-caps">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Mín. 8 caracteres" id="company_reset_password" required>
                                        <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                            data-target="company_reset_password" aria-label="Mostrar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="label-caps">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" class="form-control"
                                            id="company_reset_password_confirmation" required>
                                        <button type="button" class="btn btn-outline-secondary btn-password-toggle"
                                            data-target="company_reset_password_confirmation"
                                            aria-label="Mostrar contraseña">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-warning text-white rounded-pill px-4 fw-bold">
                                        Resetear Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="companyEmailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-light" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-envelope-paper me-2 text-primary"></i>Enviar mensaje
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="companyEmailForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Asunto</label>
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light"
                                id="companyEmailSubject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">Mensaje para
                                {{ $company->name }}</label>
                            <textarea class="form-control form-control-sm rounded-3 bg-light" id="companyEmailMessage" name="message"
                                rows="6" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" id="sendCompanyEmailBtn" class="btn btn-primary rounded-pill px-4">
                        Enviar Ahora <i class="bi bi-send ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete() {
            Swal.fire({
                title: '¿Eliminar empresa?',
                text: "Esta acción es irreversible y borrará todo el historial asociado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar permanentemente',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            })
        }

        document.addEventListener('DOMContentLoaded', function() {
            const sendCompanyEmailBtn = document.getElementById('sendCompanyEmailBtn');
            const companyEmailForm = document.getElementById('companyEmailForm');
            const companyEmailSubject = document.getElementById('companyEmailSubject');
            const companyEmailMessage = document.getElementById('companyEmailMessage');

            if (!sendCompanyEmailBtn || !companyEmailForm || !companyEmailSubject || !companyEmailMessage) {
                return;
            }

            sendCompanyEmailBtn.addEventListener('click', async function() {
                const btn = this;
                const subject = companyEmailSubject.value.trim();
                const message = companyEmailMessage.value.trim();

                if (!subject || !message) {
                    return Swal.fire('Error', 'Completa los campos obligatorios.', 'error');
                }

                btn.disabled = true;
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';

                try {
                    const response = await fetch(
                        "{{ route('admin.empresas.email.send', $company->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                subject,
                                message
                            }),
                        });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'No fue posible enviar el correo.');
                    }

                    Swal.fire('Éxito', 'Correo enviado correctamente.', 'success');
                    companyEmailForm.reset();

                    const modalEl = document.getElementById('companyEmailModal');
                    if (modalEl && window.bootstrap) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        modalInstance?.hide();
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo enviar el correo.', 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'Enviar Ahora <i class="bi bi-send ms-1"></i>';
                }
            });
        });
    </script>
    @include('components.location-autocomplete-script', [
        'citySelector' => '#admin_company_city',
        'countrySelector' => '#admin_company_country',
        'provinceSelector' => '#admin_company_province',
        'islandSelector' => '#admin_company_island',
    ])
@endsection
