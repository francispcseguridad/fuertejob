@extends('plantilla')
@section('title', 'Inmobiliarias | FuerteJob')
@section('content')
    <section class="features07 cid-v3QghnRgfg" id="inmobiliarias-list">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-12 col-lg-10">

                    <h2>Inmobiliaria/Particular:</h2>
                    <p style="font-size: 14pt;line-height: 1.5;text-align: justify;">¿Eres una inmobiliaria o un particular
                        con una vivienda para vender o alquilar? Anúnciate en FuerteJob y publica tu inmueble en el
                        archipiélago. Plazas limitadas: dale máxima visibilidad
                    </p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#inmobiliariasContactModal">
                        Contáctanos
                    </button>

                    <div class="modal fade" id="inmobiliariasContactModal" tabindex="-1"
                        aria-labelledby="inmobiliariasContactModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="inmobiliariasContactModalLabel">
                                        Contactar para anunciar tu inmueble
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <x-commercial-contact-form origin-label="Inmobiliaria/Particular"
                                        origin-value="inmobiliaria" form-title="¿Quieres anunciar tu inmueble?"
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

                            const modalEl = document.getElementById('inmobiliariasContactModal');
                            if (!modalEl || typeof bootstrap === 'undefined') return;

                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        });
                    </script>
                    <div class="card border-0 shadow-sm p-4">

                        <div class="card-header">
                            <h2 class="card-title fw-bold">Inmobiliarias</h2>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Buscar por nombre</label>
                                <input type="text" name="search" form="inmobiliariaFilters" class="form-control"
                                    placeholder="Ej: Grupo Canarias" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Filtrar por isla</label>
                                <select name="island_id" form="inmobiliariaFilters" class="form-select">
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
                            <form id="inmobiliariaFilters" action="{{ route('public.inmobiliarias.index') }}"
                                method="GET">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-funnel-fill me-2"></i>Filtrar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gy-4">
                @forelse ($inmobiliarias as $inmobiliaria)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header p-0">
                                <img src="{{ $inmobiliaria->logo ? asset($inmobiliaria->logo) : 'assets/images/default_news.png' }}"
                                    alt="{{ $inmobiliaria->name }}" class="img-fluid w-100"
                                    style="height: 210px; object-fit: cover;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold">{{ $inmobiliaria->name }}</h5>
                                <p class="mb-1 text-muted small">
                                    <i class="bi bi-geo-alt me-2"></i>{{ $inmobiliaria->address }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-telephone me-2"></i>{{ $inmobiliaria->phone }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-envelope me-2"></i>{{ $inmobiliaria->email }}
                                </p>
                                @if ($inmobiliaria->website)
                                    <p class="mb-1">
                                        <i class="bi bi-globe me-2"></i>
                                        <a href="{{ $inmobiliaria->website }}" target="_blank" rel="noopener"
                                            class="text-decoration-none">{{ $inmobiliaria->website }}</a>
                                    </p>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-0 text-muted small">
                                <i class="bi bi-pin-map me-2"></i>{{ $inmobiliaria->island->name ?? 'Sin isla' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center mb-0">
                            No se encontraron inmobiliarias para los filtros seleccionados.
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($inmobiliarias->hasPages())
                <div class="row mt-5">
                    <div class="col-12">
                        {{ $inmobiliarias->links() }}
                    </div>
                </div>
            @endif
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
