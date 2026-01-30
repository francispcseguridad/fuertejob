@extends('layouts.app')
@section('title', 'Configuración del Portal')

@section('content')
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1">Configuración del Portal</h1>
                <p class="text-muted mb-0">Actualiza la información legal, fiscal y visual que se muestra en la plataforma.
                </p>
            </div>
            <div>
                <span class="badge bg-light text-muted border rounded-pill px-3 py-2">
                    <i class="bi bi-gear-fill me-2"></i>Ajustes generales
                </span>
            </div>
        </div>

        {{-- Mensajes de sesión --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Se encontraron errores:
                </h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.configuracion.update', $settings->id) }}" method="POST" enctype="multipart/form-data"
            class="card shadow-sm border-0">
            @csrf
            @method('PUT')
            <div class="card-body p-4">
                <div class="mb-5 border-bottom pb-4">
                    <h5 class="text-uppercase text-muted small fw-bold mb-3">Información del negocio</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="site_name" class="form-label fw-semibold">Nombre público del portal</label>
                            <input type="text" class="form-control" id="site_name" name="site_name"
                                value="{{ old('site_name', $settings->site_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="legal_name" class="form-label fw-semibold">Razón social</label>
                            <input type="text" class="form-control" id="legal_name" name="legal_name"
                                value="{{ old('legal_name', $settings->legal_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="vat_id" class="form-label fw-semibold">ID Fiscal / CIF / NIT</label>
                            <input type="text" class="form-control" id="vat_id" name="vat_id"
                                value="{{ old('vat_id', $settings->vat_id) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_email" class="form-label fw-semibold">Correo electrónico de contacto</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email"
                                value="{{ old('contact_email', $settings->contact_email) }}">
                        </div>
                        <div class="col-12">
                            <label for="fiscal_address" class="form-label fw-semibold">Dirección fiscal</label>
                            <textarea class="form-control" id="fiscal_address" name="fiscal_address" rows="3">{{ old('fiscal_address', $settings->fiscal_address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-5 border-bottom pb-4">
                    <h5 class="text-uppercase text-muted small fw-bold mb-3">Ajustes de facturación</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="default_tax_rate" class="form-label fw-semibold">Tasa de impuesto predeterminada
                                (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                id="default_tax_rate" name="default_tax_rate"
                                value="{{ old('default_tax_rate', $settings->default_tax_rate) }}">
                            <div class="form-text">Utiliza decimales si es necesario (ej. 12.50).</div>
                        </div>
                        <div class="col-md-6">
                            <label for="default_irpf" class="form-label fw-semibold">Retención IRPF predeterminada
                                (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control"
                                id="default_irpf" name="default_irpf"
                                value="{{ old('default_irpf', $settings->default_irpf) }}">
                            <div class="form-text">Se aplica como retención sobre la base imponible.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="invoice_prefix" class="form-label fw-semibold">Prefijo para facturas</label>
                            <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix"
                                value="{{ old('invoice_prefix', $settings->invoice_prefix) }}">
                            <div class="form-text">Ejemplo: INV-, FJ-2024-.</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h5 class="text-uppercase text-muted small fw-bold mb-3">Identidad visual</h5>
                    <div class="row g-4 align-items-center">
                        <div class="col-md-7">
                            <label for="logo_file" class="form-label fw-semibold">Logo del portal</label>
                            <input type="file" class="form-control" id="logo_file" name="logo_file"
                                accept="image/*">
                            <div class="form-text">Formatos permitidos: JPG, PNG, GIF, SVG. Máx. 2MB.</div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            @if ($settings->logo_url)
                                <div class="d-inline-flex flex-column align-items-start align-items-md-end">
                                    <span class="text-muted small mb-2">Logo actual:</span>
                                    <img src="{{ asset($settings->logo_url) }}" alt="Logo del portal"
                                        class="img-fluid rounded border shadow-sm p-2" style="max-height: 90px;">
                                </div>
                            @else
                                <span class="text-muted small">No hay un logo cargado.</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4 border-bottom pb-4">
                        <h5 class="text-uppercase text-muted small fw-bold mb-3">Imágenes complementarias</h5>
                        <div class="row g-4 align-items-start">
                            <div class="col-md-6">
                                <label for="imagen_academias_file" class="form-label fw-semibold">Imagen para
                                    academias</label>
                                <input type="file" class="form-control" id="imagen_academias_file"
                                    name="imagen_academias_file" accept="image/*">
                                <input type="hidden" name="imagen_academias" id="imagen_academias">
                                <div id="academia_crop_container" class="d-none mt-3">
                                    <div class="img-preview-container rounded-3">
                                        <img id="imagen_academias_preview" class="img-fluid"
                                            alt="Vista previa academias">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">Se guardará como
                                        <strong>img/academia.jpg</strong>.</small>
                                    @if ($settings->imagen_academias)
                                        <img src="{{ asset($settings->imagen_academias) }}" alt="Imagen academias actual"
                                            class="img-fluid rounded shadow-sm" style="max-height: 120px;">
                                    @else
                                        <span class="text-muted small">No hay imagen cargada todavía.</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="imagen_inmobiliarias_file" class="form-label fw-semibold">Imagen para
                                    inmobiliarias</label>
                                <input type="file" class="form-control" id="imagen_inmobiliarias_file"
                                    name="imagen_inmobiliarias_file" accept="image/*">
                                <input type="hidden" name="imagen_inmobiliarias" id="imagen_inmobiliarias">
                                <div id="inmobiliaria_crop_container" class="d-none mt-3">
                                    <div class="img-preview-container rounded-3">
                                        <img id="imagen_inmobiliarias_preview" class="img-fluid"
                                            alt="Vista previa inmobiliarias">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">Se guardará como
                                        <strong>img/inmobiliaria.jpg</strong>.</small>
                                    @if ($settings->imagen_inmobiliarias)
                                        <img src="{{ asset($settings->imagen_inmobiliarias) }}"
                                            alt="Imagen inmobiliarias actual" class="img-fluid rounded shadow-sm"
                                            style="max-height: 120px;">
                                    @else
                                        <span class="text-muted small">No hay imagen cargada todavía.</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-0 text-end p-4 pt-0">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-save me-2"></i>Guardar configuración
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cropFields = [{
                    inputId: 'imagen_academias_file',
                    previewId: 'imagen_academias_preview',
                    containerId: 'academia_crop_container',
                    hiddenId: 'imagen_academias'
                },
                {
                    inputId: 'imagen_inmobiliarias_file',
                    previewId: 'imagen_inmobiliarias_preview',
                    containerId: 'inmobiliaria_crop_container',
                    hiddenId: 'imagen_inmobiliarias'
                }
            ];

            const croppers = {};

            cropFields.forEach(field => {
                const input = document.getElementById(field.inputId);
                const preview = document.getElementById(field.previewId);
                const container = document.getElementById(field.containerId);

                if (!input || !preview || !container) {
                    return;
                }

                input.addEventListener('change', function() {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.src = event.target?.result || '';
                        container.classList.remove('d-none');

                        if (croppers[field.hiddenId]) {
                            croppers[field.hiddenId].destroy();
                        }

                        croppers[field.hiddenId] = new Cropper(preview, {
                            aspectRatio: 1,
                            viewMode: 1,
                            autoCropArea: 1
                        });
                    };
                    reader.readAsDataURL(file);
                });
            });

            const form = document.querySelector('form[action*="configuracion"]');
            if (!form) {
                return;
            }

            form.addEventListener('submit', function() {
                cropFields.forEach(field => {
                    const cropper = croppers[field.hiddenId];
                    if (cropper) {
                        const canvas = cropper.getCroppedCanvas({
                            width: 600,
                            height: 600
                        });
                        const hiddenInput = document.getElementById(field.hiddenId);
                        if (hiddenInput) {
                            hiddenInput.value = canvas.toDataURL('image/jpeg', 0.9);
                        }
                    }
                });
            });
        });
    </script>
    <style>
        .img-preview-container {
            background: #f8f9fa;
            min-height: 220px;
            border: 1px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .img-preview-container img {
            max-height: 320px;
            width: auto;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
@endsection
