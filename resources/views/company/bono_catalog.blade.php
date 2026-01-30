@extends('layouts.app')
@section('title', 'Catálogo de Bonos')
@section('content')
    <!-- Inject Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        .bono-feature {
            color: #0d6efd;
            font-weight: 600;
            background: rgba(13, 110, 253, 0.08);
            padding: 0 0.25rem;
            border-radius: 0.25rem;
        }

        .card-bono-highlight {
            border: 2px solid #1c476b background: linear-gradient(135deg, rgba(255, 193, 7, 0.55), rgba(255, 255, 255, 0.95));
            position: relative;
            overflow: hidden;
            transform: translateY(-4px);

        }

        .card-bono-highlight::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgb(28, 71, 107), transparent 60%);
            opacity: 0.8;
            pointer-events: none;
        }

        .card-bono-highlight .card-body,
        .card-bono-highlight .card-header-bono {
            position: relative;
            z-index: 2;
        }

        @keyframes pulse-highlight {

            0%,
            100% {
                transform: translateY(-4px) scale(1);
                box-shadow: 0 18px 45px rgba(255, 193, 7, 0.35);
            }

            50% {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 22px 55px rgba(255, 193, 7, 0.55);
            }
        }

        .highlight-pill {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: rgb(29, 76, 114) color: #fff;
            font-weight: 700;
            padding: 0.35rem 1.1rem;
            border-radius: 999px;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            border: 1px solid rgb(96, 154, 201);
        }
    </style>

    <div x-data="bonoCatalog({{ json_encode($bonos) }}, '{{ route('empresa.bonos.purchase', 'BONO_ID_PLACEHOLDER') }}', {{ json_encode($companyState ?? []) }})">

        <!-- Hero Section -->
        <div class="hero-section shadow">
            <div class="container">
                <h1 class="display-4 hero-title mb-3">Impulsa tu Empresa</h1>
                <p class="lead opacity-75 mx-auto" style="max-width: 600px;">
                    Accede a herramientas exclusivas, descuentos y mejoras de visibilidad con nuestros bonos premium.
                </p>
            </div>
        </div>

        <div class="container mb-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-primary border-opacity-50 shadow-sm" x-cloak>
                        <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Saldo actual de créditos</p>


                                <div class="d-flex justify-content-center align-items-center gap-4 flex-nowrap w-100 mt-3">
                                    <span class="text-muted small text-center">
                                        Ofertas que puedes publicar<br>
                                        <strong x-text="companyBalance.availableOfferCredits"></strong>
                                    </span>
                                    <span class="text-muted small text-center">
                                        CV que puedes ver<br>
                                        <strong x-text="companyBalance.availableCvViews"></strong>
                                    </span>
                                    <span class="text-muted small text-center">
                                        Usuarios activos<br>
                                        <strong x-text="companyBalance.availableUserSeats"></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="text-primary fs-1">
                                <i class="bi bi-wallet2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container text-center my-4">
            <p class="mb-2 fw-semibold">¿Tienes dudas? Contacta con nosotros y te ayudamos a elegir el bono ideal.</p>
            <button id="openContactModal" class="btn btn-outline-primary btn-lg rounded-pill">
                Hablar con el equipo
            </button>
        </div>

        <div class="container pb-5">

            {{-- Indicador de Carga --}}
            <div x-show="loading" class="text-center py-5" x-cloak>
                <span class="loader mb-3"></span>
                <p class="text-muted fs-5">Cargando catálogo...</p>
            </div>

            {{-- Mensaje de Error --}}
            <div x-show="error" class="alert alert-danger shadow-sm rounded-3 border-0 py-4" x-cloak role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">Error al cargar datos</h5>
                        <p class="mb-0" x-text="errorDetails"></p>
                    </div>
                </div>
                <button @click="fetchBonos()" class="btn btn-outline-danger mt-3 btn-sm">Reintentar</button>
            </div>

            {{-- Catálogo de Bonos --}}
            <div x-show="!loading && !error && bonos.length > 0">
                <div x-show="bonos.filter(bono => !bono.is_extra).length > 0" class="row g-4 justify-content-center">
                    <template x-for="bono in bonos.filter(bono => !bono.is_extra)" :key="`main-${bono.id}`">
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-bono h-100" :class="{ 'card-bono-highlight': bono.destacado }">
                                <div class="card-header-bono position-relative">
                                    <span class="highlight-pill" x-show="bono.destacado" x-cloak>Recomendado</span>
                                    <!-- Badge Logic -->
                                    <span class="bono-badge"
                                        :class="{
                                            'badge-discount': bono.type === 'Discount',
                                            'badge-shipping': bono.type === 'Shipping',
                                            'badge-credit': bono.type === 'Credit',
                                            'badge-default': !['Discount', 'Shipping', 'Credit'].includes(bono.type)
                                        }"
                                        x-text="bono.type">
                                    </span>

                                    <h3 class="fw-bold mb-2 text-dark" x-text="bono.name"></h3>
                                    <span class="badge bg-info text-dark ms-2" x-show="bono.is_extra" x-cloak>Extra</span>
                                    <p class="text-primary fw-semibold" x-text="bono.value"></p>
                                </div>

                                <div class="card-body p-4 d-flex flex-column">
                                    <p class="text-muted mb-4 flex-grow-1" x-html="bono.formattedDescription"></p>


                                    <div class="text-center mb-4">
                                        <span class="bono-currency" x-text="bono.displayCurrency"></span>
                                        <span class="bono-price" x-text="bono.displayPrice"></span>
                                    </div>

                                    <button @click="handlePurchaseClick(bono)"
                                        class="btn btn-purchase shadow-sm text-uppercase">
                                        Comprar Ahora <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="bonos.filter(bono => bono.is_extra).length > 0" class="mt-5">
                    <div class="row">
                        <div class="col-12">
                            <h5 class="text-secondary fw-semibold text-uppercase mb-3">Extras</h5>
                        </div>
                    </div>
                    <div class="row g-4 justify-content-center">
                        <template x-for="bono in bonos.filter(bono => bono.is_extra)" :key="`extra-${bono.id}`">
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card card-bono h-100" :class="{ 'card-bono-highlight': bono.destacado }">
                                    <div class="card-header-bono position-relative" style="padding: 0 !important;">
                                        <span class="highlight-pill" x-show="bono.destacado" x-cloak>Recomendado</span>
                                        <!-- Badge Logic -->
                                        <span class="bono-badge"
                                            :class="{
                                                'badge-discount': bono.type === 'Discount',
                                                'badge-shipping': bono.type === 'Shipping',
                                                'badge-credit': bono.type === 'Credit',
                                                'badge-default': !['Discount', 'Shipping', 'Credit'].includes(bono.type)
                                            }"
                                            x-text="bono.type">
                                        </span>

                                        <span class="fw-bold mb-2 text-dark texto-titulopeq" x-text="bono.name">
                                        </span><br>
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <p class="text-muted mb-4 flex-grow-1" x-html="bono.formattedDescription"></p>


                                        <div class="text-center mb-4">
                                            <span class="bono-price" x-text="bono.displayPrice"
                                                style="font-size: 20px !important;"></span> €

                                        </div>

                                        <button @click="handlePurchaseClick(bono)"
                                            class="btn btn-purchase shadow-sm text-uppercase">
                                            Comprar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Mensaje de Catálogo Vacío --}}
            <div x-show="!loading && bonos.length === 0 && !error" class="text-center py-5" x-cloak>
                <div class="p-5 bg-light rounded-3 shadow-sm d-inline-block">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <h3 class="text-muted">No hay bonos disponibles</h3>
                    <p>Vuelve más tarde para ver nuevas ofertas.</p>
                </div>
            </div>

        </div>

        {{-- Toast/Notification --}}
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div x-show="purchaseMessage" class="toast show align-items-center text-white border-0"
                :class="purchaseSuccess ? 'bg-success' : 'bg-primary'" role="alert" aria-live="assertive"
                aria-atomic="true" x-transition:enter="animate__animated animate__fadeInUp"
                x-transition:leave="animate__animated animate__fadeOutDown" x-cloak>
                <div class="d-flex">
                    <div class="toast-body fs-6">
                        <i class="bi" :class="purchaseSuccess ? 'bi-check-circle-fill' : 'bi-info-circle-fill'"></i>
                        <span x-text="purchaseMessage" class="ms-2"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="purchaseMessage = ''"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">¿Necesitas ayuda?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="bonos-contact-form">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre*</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellidos</label>
                                    <input type="text" name="surname" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" name="company" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email*</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Duda o comentario*</label>
                                    <textarea name="message" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="privacy"
                                            id="privacy_accept" value="1" required>
                                        <label class="form-check-label" for="privacy_accept">
                                            Acepto la <a href="https://www.fuertejob.com/info/politica-de-privacidad"
                                                target="_blank" rel="noreferrer">política de privacidad</a>.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Enviar mensaje</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        function bonoCatalog(serverBonos, purchaseRouteTemplate, companyState = {}) {
            return {
                bonos: [],
                loading: false,
                error: false,
                errorDetails: '',
                purchaseMessage: '',
                purchaseSuccess: false,
                checkoutRouteTemplate: purchaseRouteTemplate,
                companyBalance: {
                    creditBalance: companyState.credit_balance ?? 0,
                    availableOfferCredits: companyState.available_offer_credits ?? 0,
                    availableCvViews: companyState.available_cv_views ?? 0,
                    availableUserSeats: companyState.available_user_seats ?? 0,
                },

                init() {
                    this.bonos = serverBonos.map(bono => ({
                        ...bono,
                        value: bono.is_extra ?
                            `${bono.credit_cost ?? 0} créditos extra` :
                            `${bono.credits_included ?? 0} Créditos`,
                        type: bono.is_extra ? 'Extra' : 'Bono',
                        displayCurrency: bono.is_extra ? 'Créditos' : '€',
                        displayPrice: this.formatMoney(bono.price),
                        formattedDescription: this.formatDescription(bono.description || '')
                    }));
                },
                formatMoney(value) {
                    if (value === null || value === undefined) {
                        return '0.00';
                    }
                    const numberValue = typeof value === 'number' ? value : parseFloat(value);
                    if (!Number.isFinite(numberValue)) {
                        return '0.00';
                    }
                    return numberValue.toFixed(2);
                },
                formatDescription(text) {
                    if (!text) {
                        return '';
                    }
                    let output = text;
                    const patterns = [
                        /(Duración máxima del bono:\s*\d+\s*días?)/gi,
                        /(\d+\s+accesos?\s+a\s+CV)/gi,
                        /(\d+\s+usuario(?:s)?\s+de\s+empresa)/gi,
                        /(\d+\s+anuncio(?:s)?\s+de\s+empleo)/gi,
                        /(\d+\s+días\s+de\s+visibilidad\s+por\s+anuncio)/gi
                    ];

                    patterns.forEach(pattern => {
                        output = output.replace(pattern, '<span class="bono-feature">$1</span>');
                    });

                    return output;
                },
                updateBalance(data) {
                    if (data?.available_offer_credits !== undefined) {
                        this.companyBalance.availableOfferCredits = data.available_offer_credits;
                    }
                    if (data?.credit_balance !== undefined) {
                        this.companyBalance.creditBalance = data.credit_balance;
                    }
                },
                hasEnoughCredits(cost) {
                    return (cost ?? 0) <= (this.companyBalance.availableOfferCredits ?? 0);
                },
                async handlePurchaseClick(bono) {
                    if (bono.is_extra && !this.hasEnoughCredits(bono.credit_cost ?? 0)) {
                        this.purchaseSuccess = false;
                        this.purchaseMessage = 'Saldo insuficiente para comprar este extra.';
                        setTimeout(() => {
                            this.purchaseMessage = '';
                        }, 4000);
                        return;
                    }

                    this.purchaseSuccess = false;
                    this.purchaseMessage = `Iniciando pago seguro para ${bono.name}...`;

                    const checkoutUrl = this.checkoutRouteTemplate.replace('BONO_ID_PLACEHOLDER', bono.id);

                    try {
                        const response = await fetch(checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                        });

                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }

                        const data = await response.json();

                        if (!response.ok) {
                            this.purchaseSuccess = false;
                            this.purchaseMessage = data.message || 'Error al iniciar la sesión de pago.';
                        } else if (bono.is_extra) {
                            this.purchaseSuccess = true;
                            this.purchaseMessage = data.message || 'Extra aplicado correctamente.';
                            this.updateBalance(data);
                        } else {
                            this.purchaseSuccess = false;
                            this.purchaseMessage = data.message || 'Error al iniciar la sesión de pago.';
                        }

                    } catch (e) {
                        this.purchaseSuccess = false;
                        this.purchaseMessage = 'Error de conexión con el servidor.';
                        console.error('Checkout initiation error:', e);
                    }

                    setTimeout(() => {
                        this.purchaseMessage = '';
                    }, 4000);
                }
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contactForm = document.getElementById('bonos-contact-form');
            const contactModalEl = document.getElementById('contactModal');
            const openContactButton = document.getElementById('openContactModal');
            const contactModal = contactModalEl ? new bootstrap.Modal(contactModalEl) : null;

            openContactButton?.addEventListener('click', () => {
                contactModal?.show();
            });

            if (!contactForm) return;

            contactForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(contactForm);
                if (!formData.has('privacy')) {
                    formData.set('privacy', 0);
                }

                try {
                    const response = await fetch("{{ route('empresa.bonos.contact') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                        body: formData
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'No se pudo enviar el mensaje.');
                    }

                    Swal.fire('Mensaje enviado',
                        'Gracias por escribirnos, te contestaremos lo antes posible.', 'success');
                    contactModal?.hide();
                    contactForm.reset();
                } catch (error) {
                    Swal.fire('Error', error.message || 'No se pudo enviar el mensaje.', 'error');
                }
            });
        });
    </script>
@endsection
