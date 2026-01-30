@extends('layouts.app')
@section('title', 'Ofertas pendientes de publicación')
@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold text-dark">Ofertas pendientes de publicación</h1>
                <p class="text-muted mb-0">Revisa, aprueba o rechaza las solicitudes de publicación de las empresas.</p>
            </div>
            <a href="{{ route('admin.ofertas.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver al listado
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Ubicación</th>
                            <th>Solicitada</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($offers as $offer)
                            <tr>
                                <td>{{ $offer->id }}</td>
                                <td class="fw-semibold text-dark">{{ $offer->title }}</td>
                                <td>{{ $offer->companyProfile->company_name ?? 'Sin empresa' }}</td>
                                <td>{{ $offer->location }}</td>
                                <td>{{ optional($offer->pending_review_at)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.ofertas.edit', $offer) }}"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                    <form method="POST" class="d-inline"
                                        action="{{ route('admin.ofertas.pendientes.aceptar', $offer) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check2 me-1"></i>Aceptar
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectReasonModal" data-offer-id="{{ $offer->id }}"
                                        data-offer-title="{{ $offer->title }}">
                                        <i class="bi bi-x-circle me-1"></i>Rechazar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay solicitudes pendientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                {{ $offers->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST"
                    data-action-template="{{ route('admin.ofertas.pendientes.rechazar', ['jobOffer' => '__ID__']) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectReasonModalLabel">Rechazar oferta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Indica el motivo del rechazo para notificar a la empresa.</p>
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar oferta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rejectModal = document.getElementById('rejectReasonModal');
            const rejectForm = rejectModal?.querySelector('form');
            if (!rejectModal || !rejectForm) {
                return;
            }

            rejectModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const offerId = button?.getAttribute('data-offer-id');
                const offerTitle = button?.getAttribute('data-offer-title');
                const modalTitle = rejectModal.querySelector('.modal-title');

                if (modalTitle && offerTitle) {
                    modalTitle.textContent = `Rechazar oferta: ${offerTitle}`;
                }

                const actionTemplate = rejectForm.dataset.actionTemplate;
                if (offerId && actionTemplate) {
                    rejectForm.action = actionTemplate.replace('__ID__', offerId);
                }
            });
        });
    </script>
@endsection
