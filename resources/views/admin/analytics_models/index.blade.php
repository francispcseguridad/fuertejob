@extends('layouts.app')

@section('title', 'Modelos de Analytics')

@section('content')
    <div class="container py-4">
        <!-- Header Section -->
        <div class="card bg-dark text-white border-0 shadow-lg mb-5 overflow-hidden">
            <div class="card-body p-5 position-relative">
                <div class="position-absolute top-0 end-0 p-4 opacity-25">
                    <i class="bi bi-graph-up-arrow display-1"></i>
                </div>
                <div class="row align-items-center position-relative">
                    <div class="col-lg-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}"
                                        class="text-white opacity-75 text-decoration-none">Analíticas</a></li>
                                <li class="breadcrumb-item active text-white fw-bold" aria-current="page">Modelos</li>
                            </ol>
                        </nav>
                        <h1 class="display-5 fw-bold mb-3">Modelos de Analytics</h1>
                        <p class="lead mb-0 opacity-75">
                            Configura los niveles de acceso y vincula funciones de métricas con tu catálogo de bonos.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <a href="{{ route('admin.analytics.index') }}" class="btn btn-light btn-modern px-4 py-2">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Panel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert shadow-sm rounded-3 mb-4 d-flex align-items-center border-0"
                style="background-color: #214b6e; color: white;">
                <i class="bi bi-check-circle-fill me-3 fs-3"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            @foreach ($models as $model)
                @php
                    $brandColor = '#214b6e';
                    $levelIcon = match ($model->level) {
                        'basic' => 'bi-lightning-charge',
                        'medium' => 'bi-rocket-takeoff',
                        'advanced' => 'bi-gem',
                        default => 'bi-gear',
                    };
                @endphp
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom-0 p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle text-white me-3 shadow-sm"
                                        style="background-color: {{ $brandColor }};">
                                        <i class="bi {{ $levelIcon }}"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <h4 class="mb-0 fw-bold me-2 text-dark">{{ $model->name }}</h4>
                                            <span class="badge rounded-pill border text-uppercase px-3 py-2 small fw-bold"
                                                style="background-color: rgba(33, 75, 110, 0.1); color: #214b6e; border-color: #214b6e !important;">
                                                Nivel {{ $model->level }}
                                            </span>
                                        </div>
                                        <p class="text-muted mb-0 mt-1 small">{{ $model->description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase small fw-bold text-muted"
                                                style="width: 40%;">Función Analítica</th>
                                            <th class="py-3 text-uppercase small fw-bold text-muted" style="width: 30%;">
                                                Bonos Vinculados</th>
                                            <th class="pe-4 py-3 text-uppercase small fw-bold text-muted text-end">Gestión
                                                de Acceso</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse ($model->functions as $function)
                                            <tr>
                                                <td class="ps-4 py-4">
                                                    <div class="d-flex align-items-start">
                                                        <div class="me-3 mt-1" style="color: #214b6e;">
                                                            <i class="bi bi-check2-circle fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark fs-5 mb-1">{{ $function->name }}
                                                            </div>
                                                            <p class="mb-1 text-muted small lh-sm">
                                                                {{ $function->description }}</p>
                                                            @if ($function->details)
                                                                <div class="bg-light p-2 rounded-2 small border-start border-4 mt-2 text-secondary"
                                                                    style="border-left-color: #214b6e !important;">
                                                                    <i class="bi bi-info-circle me-1"></i>
                                                                    {{ $function->details }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4">
                                                    @if ($function->bonoCatalogs->isEmpty())
                                                        <span class="badge bg-light text-muted fw-normal border px-3 py-2">
                                                            <i class="bi bi-link-45deg me-1"></i>Sin vínculos
                                                        </span>
                                                    @else
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach ($function->bonoCatalogs as $bono)
                                                                <span
                                                                    class="badge bg-white border small px-2 py-1 shadow-sm"
                                                                    style="color: #214b6e; border-color: #e9ecef !important;">
                                                                    <i
                                                                        class="bi bi-tag-fill me-1 small"></i>{{ $bono->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="pe-4 py-4 text-end">
                                                    <form
                                                        action="{{ route('admin.analytics_models.functions.link_bonus', $function) }}"
                                                        method="POST" class="d-inline-block ajax-form">
                                                        @csrf
                                                        <div
                                                            class="input-group input-group-sm mb-0 shadow-sm rounded-3 overflow-hidden border">
                                                            <select name="bono_catalog_ids[]"
                                                                class="form-select border-0 px-3" multiple
                                                                style="min-width: 200px; max-width: 250px; font-size: 0.85rem;"
                                                                data-bs-toggle="tooltip"
                                                                title="Ctrl + Clic para selección múltiple">
                                                                @foreach ($bonos as $bono)
                                                                    <option value="{{ $bono->id }}"
                                                                        @selected($function->bonoCatalogs->pluck('id')->contains($bono->id))>
                                                                        {{ $bono->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit"
                                                                class="btn px-3 border-0 transition-all hover-lift"
                                                                style="background-color: #214b6e; color: white;">
                                                                <i class="bi bi-save2 me-1"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-muted">
                                                    <i class="bi bi-folder-x fs-1 opacity-25 d-block mb-3"></i>
                                                    No hay funciones definidas para este modelo.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .icon-circle {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            filter: brightness(110%);
        }

        .form-select:focus {
            box-shadow: none;
            border: none;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .1) !important;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 0.5rem;
        }

        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-top: -8px;
            margin-left: -8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Toast Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Handle AJAX Forms
            const ajaxForms = document.querySelectorAll('.ajax-form');
            ajaxForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalContent = submitBtn.innerHTML;
                    const formData = new FormData(this);

                    // Visual Feedback
                    submitBtn.classList.add('btn-loading');

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Toast.fire({
                                    icon: 'success',
                                    title: data.message ||
                                        'Vínculos actualizados correctamente',
                                    background: '#214b6e',
                                    color: '#fff',
                                    iconColor: '#fff'
                                });
                            } else {
                                throw new Error(data.message || 'Error al guardar');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Toast.fire({
                                icon: 'error',
                                title: 'Hubo un problema al guardar los cambios',
                                background: '#dc3545',
                                color: '#fff'
                            });
                        })
                        .finally(() => {
                            submitBtn.classList.remove('btn-loading');
                        });
                });
            });
        });
    </script>
@endsection
