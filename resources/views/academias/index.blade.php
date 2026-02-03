@extends('plantilla')
@section('title', 'Academias | FuerteJob')
@section('content')
    <section class="features07 cid-v3QghnRgfg" id="academias-list">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-12 col-lg-10">
                    <h2>Academia/Centro de formación:</h2>
                    <p style="font-size: 14pt;line-height: 1.5;text-align: justify;">¿Eres una academia o centro de
                        formación? Anúnciate en FuerteJob y ofrece tus cursos presenciales u online, dentro y fuera del
                        archipiélago. Plazas limitadas: no te quedes fuera.

                    </p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#academiasContactModal">
                        Contáctanos
                    </button>

                    <div class="modal fade" id="academiasContactModal" tabindex="-1"
                        aria-labelledby="academiasContactModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="academiasContactModalLabel">
                                        Contactar para publicitar tu academia
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <x-commercial-contact-form origin-label="Academia/Centro de formación"
                                        origin-value="academia" form-title="¿Quieres publicitar tu academia?"
                                        form-description="Déjanos tus datos y cuéntanos qué necesitas. Te responderemos lo antes posible."
                                        button-text="Enviar" :captcha-question="$commercialContactCaptchaQuestion" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const shouldOpen =
                                {{ $errors->has('name') || $errors->has('phone') || $errors->has('email') || $errors->has('detail') || $errors->has('math_captcha') || $errors->has('origin') ? 'true' : 'false' }};

                            if (!shouldOpen) return;

                            const modalEl = document.getElementById('academiasContactModal');
                            if (!modalEl || typeof bootstrap === 'undefined') return;

                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        });
                    </script>

                    <div class="card border-0 shadow-sm p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Buscar por nombre</label>
                                <input type="text" name="search" form="academiaFilters" class="form-control"
                                    placeholder="Ej: Academia Fuerteventura" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Filtrar por isla</label>
                                <select name="island_id" form="academiaFilters" class="form-select">
                                    <option value="">Todas las islas</option>

                                    @foreach ($islands as $island)
                                        <option value="{{ $island->id }}"
                                            {{ request('island_id') == $island->id ? 'selected' : '' }}>
                                            {{ $island->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <form id="academiaFilters" action="{{ route('public.academias.index') }}" method="GET">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-funnel-fill me-2"></i>Filtrar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gy-4">
                @forelse ($academias as $academia)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header p-0">
                                <img src="{{ $academia->logo ? asset($academia->logo) : 'assets/images/default_news.png' }}"
                                    alt="{{ $academia->name }}" class="img-fluid w-100"
                                    style="height: 210px; object-fit: cover;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold">{{ $academia->name }}</h5>
                                <p class="mb-1 text-muted small">
                                    <i class="bi bi-geo-alt me-2"></i>{{ $academia->address }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-telephone me-2"></i>{{ $academia->phone }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-envelope me-2"></i>{{ $academia->email }}
                                </p>
                                @if ($academia->website)
                                    <p class="mb-1">
                                        <i class="bi bi-globe me-2"></i>
                                        <a href="{{ $academia->website }}" target="_blank" rel="noopener"
                                            class="text-decoration-none">{{ $academia->website }}</a>
                                    </p>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-0 text-muted small">
                                <i class="bi bi-pin-map me-2"></i>{{ $academia->island->name ?? 'Sin isla' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center mb-0">
                            No se encontraron academias para los filtros seleccionados.
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($academias->hasPages())
                <div class="row mt-5">
                    <div class="col-12">
                        {{ $academias->links() }}
                    </div>
                </div>
            @endif

            <div class="row justify-content-center mt-5">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 rounded-4 shadow-lg" style="background: #214b6e;">
                        <div class="card-body text-white text-center py-5">
                            <p class="fs-5 mb-2">Haz brillar tu academia en todo el archipiélago</p>
                            <h3 class="fw-bold mb-3">Captamos alumnos y visibilidad para tu marca</h3>
                            <p class="mb-0 opacity-75">Conecta con miles de candidatos que navegan por FuerteJob buscando
                                formación profesional. Plazas limitadas: no te quedes fuera.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-4 mb-5">
                <div class="col-12 col-lg-8">
                    @include('components.commercial-contact-form', [
                        'originLabel' => 'Academias',
                        'originValue' => 'academias',
                        'formTitle' => '¿Quieres publicitar tu academia?',
                        'formDescription' =>
                            'Dinos qué necesitas y mostramos tu centro a miles de visitantes interesados en formación.',
                        'buttonText' => 'Solicitar publicidad',
                        'captchaQuestion' => $commercialContactCaptchaQuestion ?? null,
                    ])
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const success = {{ session('commercial_contact_success') ? 'true' : 'false' }};
            if (!success || typeof Swal === 'undefined') return;

            Swal.fire({
                icon: 'success',
                title: 'Gracias por ponerte en contacto',
                text: 'Hemos recibido tu mensaje. Te responderemos lo antes posible.',
                confirmButtonText: 'Aceptar',
            });
        });
    </script>
@endsection
