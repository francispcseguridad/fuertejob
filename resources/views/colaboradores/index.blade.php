@extends('plantilla')
@section('title', 'Colaboradores | FuerteJob')
@section('content')
    <section class="features07 cid-v3QghnRgfg" id="colaboradores-list">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-12 col-lg-10">

                    <h2>Colaboradores:</h2>
                    <p style="font-size: 14pt;line-height: 1.5;text-align: justify;">
                        ¿Tienes una empresa y quieres dar aún más visibilidad a tu negocio? Anúnciate con nosotros y llega a
                        más clientes en Canarias y más allá. Cupos limitados: asegura tu plaza como empresa colaboradora.
                    </p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#colaboradoresContactModal">
                        Contáctanos
                    </button>

                    <div class="modal fade" id="colaboradoresContactModal" tabindex="-1"
                        aria-labelledby="colaboradoresContactModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="colaboradoresContactModalLabel">
                                        Contactar para colaborar con FuerteJob
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <x-commercial-contact-form origin-label="Colaboradores" origin-value="colaboradores"
                                        form-title="¿Quieres colaborar con FuerteJob?"
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

                            const modalEl = document.getElementById('colaboradoresContactModal');
                            if (!modalEl || typeof bootstrap === 'undefined') return;

                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        });
                    </script>


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
