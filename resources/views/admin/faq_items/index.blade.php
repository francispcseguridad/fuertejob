@extends('layouts.app')
@section('title', 'FAQ & Wiki')

@section('content')
    @php
        $audienceLabels = [
            \App\Models\FaqItem::AUDIENCE_WORKER => 'Candidatos',
            \App\Models\FaqItem::AUDIENCE_COMPANY => 'Empresas',
            \App\Models\FaqItem::AUDIENCE_GENERAL => 'General',
        ];
    @endphp

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1">Centro de Ayuda</h1>
                <p class="text-muted mb-0">Gestiona las preguntas frecuentes y artículos que verán los usuarios.</p>
            </div>
            <button class="btn btn-primary rounded-pill" onclick="openCreateFaq()">
                <i class="bi bi-plus-circle me-2"></i>Nueva entrada
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-primary mb-1"><i class="bi bi-question-circle me-2"></i>Artículos publicados</h5>
                    <small class="text-muted">Total: {{ $faqItems->total() }}</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4">Público</th>
                                <th>Pregunta / Título</th>
                                <th>Estado</th>
                                <th>Orden</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($faqItems as $item)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-secondary border">{{ $audienceLabels[$item->target_audience] ?? ucfirst($item->target_audience) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->question }}</div>
                                        <small class="text-muted">Actualizado {{ $item->updated_at?->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if ($item->is_published)
                                            <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Publicado</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-clock me-1"></i>Borrador</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->sort_order ?? '-' }}</td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-primary" onclick="openEditFaq(this)"
                                                data-update-url="{{ route('admin.faq_items.update', $item) }}"
                                                data-audience="{{ $item->target_audience }}"
                                                data-question="{{ e($item->question) }}"
                                                data-answer="{{ e($item->answer) }}"
                                                data-sort-order="{{ $item->sort_order }}"
                                                data-published="{{ $item->is_published ? 1 : 0 }}">
                                                <i class="bi bi-pencil-square me-1"></i>Editar
                                            </button>
                                            <form action="{{ route('admin.faq_items.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('¿Eliminar la pregunta "{{ $item->question }}"?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>Borrar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-text display-6 mb-3 d-block opacity-50"></i>
                                        No hay artículos registrados aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($faqItems->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $faqItems->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="faqModalLabel">Nueva entrada</h5>
                        <small class="text-muted" id="faqModalSubtitle">Define el contenido visible para los usuarios.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="faqForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="faqMethodField">
                    <div class="modal-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="faqAudience" class="form-label fw-semibold">Público objetivo</label>
                                <select id="faqAudience" name="target_audience" class="form-select" required>
                                    @foreach ($audienceLabels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="faqOrder" class="form-label fw-semibold">Orden de aparición</label>
                                <input type="number" min="0" class="form-control" id="faqOrder" name="sort_order"
                                    placeholder="0">
                            </div>
                            <div class="col-12">
                                <label for="faqQuestion" class="form-label fw-semibold">Pregunta / Título</label>
                                <input type="text" class="form-control" id="faqQuestion" name="question" required>
                            </div>
                            <div class="col-12">
                                <label for="faqAnswer" class="form-label fw-semibold">Respuesta / Contenido</label>
                                <textarea id="faqAnswer" class="form-control" name="answer" rows="6" required></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="faqPublished" name="is_published" value="1">
                                    <label class="form-check-label" for="faqPublished">Marcar como publicado</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        const faqCreateUrl = "{{ route('admin.faq_items.store') }}";
        const faqModalEl = document.getElementById('faqModal');
        const faqForm = document.getElementById('faqForm');
        const faqMethodField = document.getElementById('faqMethodField');
        const faqAudienceInput = document.getElementById('faqAudience');
        const faqOrderInput = document.getElementById('faqOrder');
        const faqQuestionInput = document.getElementById('faqQuestion');
        const faqAnswerInput = document.getElementById('faqAnswer');
        const faqPublishedInput = document.getElementById('faqPublished');
        const faqModalLabel = document.getElementById('faqModalLabel');
        const faqModalSubtitle = document.getElementById('faqModalSubtitle');
        const faqModal = new bootstrap.Modal(faqModalEl);

        function resetMethodField() {
            faqMethodField.value = '';
            faqMethodField.disabled = true;
        }

        window.openCreateFaq = function() {
            faqForm.reset();
            faqForm.action = faqCreateUrl;
            faqModalLabel.textContent = 'Nueva entrada FAQ / Wiki';
            faqModalSubtitle.textContent = 'Comparte respuestas para candidatos y empresas.';
            faqPublishedInput.checked = true;
            resetMethodField();
            faqModal.show();
        }

        window.openEditFaq = function(button) {
            const data = button.dataset;
            faqForm.action = data.updateUrl;
            faqModalLabel.textContent = 'Editar artículo';
            faqModalSubtitle.textContent = 'Ajusta el contenido existente y su visibilidad.';

            faqAudienceInput.value = data.audience;
            faqOrderInput.value = data.sortOrder || '';
            faqQuestionInput.value = data.question || '';
            faqAnswerInput.value = data.answer || '';
            faqPublishedInput.checked = data.published === '1';

            faqMethodField.disabled = false;
            faqMethodField.value = 'PUT';

            faqModal.show();
        }

        faqModalEl.addEventListener('hidden.bs.modal', () => {
            faqForm.reset();
            faqPublishedInput.checked = true;
            resetMethodField();
        });
    </script>
@endsection
