@extends('layouts.app')

@section('title', 'Candidatos inscritos · ' . $jobOffer->title)
@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Candidatos inscritos</h1>
                <p class="text-muted mb-0">Oferta: <strong>{{ $jobOffer->title }}</strong></p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.ofertas.edit', $jobOffer) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a la oferta
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-center" method="GET">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Buscar candidatos</label>
                        <input type="text" name="search" value="{{ $term }}" class="form-control"
                            placeholder="Nombre, teléfono, ciudad, email...">
                    </div>
                    <div class="col-md-2 mt-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                    </div>
                    <div class="col-md-4 text-md-end mt-4">
                        <span class="small text-muted">Total: {{ $candidates->total() }} inscritos</span>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Candidato</th>
                            <th>Ciudad</th>
                            <th>Provincia</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($candidates as $candidate)
                            @php
                                $user = $candidate->user;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $user->name ?? ($candidate->first_name . ' ' . $candidate->last_name ?? 'Sin nombre') }}</strong>
                                    <br>
                                    <span class="text-muted small">{{ $user->email ?? 'Sin email' }}</span>
                                </td>
                                <td>{{ $candidate->city ?? '—' }}</td>
                                <td>{{ $candidate->province ?? '—' }}</td>
                                <td>{{ $candidate->phone_number ?? '—' }}</td>
                                <td>
                                    {{ $user?->email ?? '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <a href="{{ route('admin.candidatos.edit', $user) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-person-lines-fill me-1"></i>Ficha
                                        </a>
                                        @if ($user?->email)
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#sendEmailModal"
                                                data-email="{{ $user->email }}"
                                                data-name="{{ $user->name ?? ($candidate->first_name . ' ' . $candidate->last_name ?? 'Candidato') }}"
                                                data-user-id="{{ $user->id }}">
                                                <i class="bi bi-envelope me-1"></i>Email
                                            </button>
                                        @endif
                                        @if ($user)
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="startMessagingThread({{ $user->id }}, '{{ addslashes(\App\Models\JobOffer::class) }}', {{ $jobOffer->id }})">
                                                <i class="bi bi-chat-dots me-1"></i>Mensaje
                                            </button>
                                        @endif
                                        <form class="d-inline" method="POST"
                                            action="{{ route('admin.ofertas.candidatos.remover', ['jobOffer' => $jobOffer, 'workerProfile' => $candidate]) }}"
                                            onsubmit="return confirm('¿Eliminar a este candidato de la oferta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-person-x me-1"></i>Quitar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay candidatos inscritos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="sendEmailForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendEmailModalLabel">Enviar email</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Destinatario</label>
                            <input type="text" id="emailRecipient" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="emailSubject" class="form-label">Asunto</label>
                            <input type="text" name="subject" id="emailSubject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailMessage" class="form-label">Mensaje</label>
                            <textarea name="message" id="emailMessage" rows="5" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sendEmailModal = document.getElementById('sendEmailModal');
                const sendEmailForm = document.getElementById('sendEmailForm');
                const recipientField = document.getElementById('emailRecipient');
                const modal = new bootstrap.Modal(sendEmailModal);
                const baseAction = "{{ route('admin.candidatos.email.send', ['candidato' => 'CANDIDATE_ID']) }}";

                sendEmailModal.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    const email = trigger?.getAttribute('data-email') ?? '';
                    const name = trigger?.getAttribute('data-name') ?? 'Candidato';
                    const userId = trigger?.getAttribute('data-user-id');

                    recipientField.value = `${name} <${email}>`;
                    document.getElementById('emailSubject').value = '';
                    document.getElementById('emailMessage').value = '';

                    if (userId) {
                        sendEmailForm.action = baseAction.replace('CANDIDATE_ID', userId);
                    } else {
                        sendEmailForm.action = '';
                    }
                });
            });
        </script>
    @endpush

@endsection
