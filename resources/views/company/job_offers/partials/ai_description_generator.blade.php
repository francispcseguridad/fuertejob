<div class="modal fade" id="jobOfferAiGeneratorModal" tabindex="-1" aria-labelledby="jobOfferAiGeneratorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="jobOfferAiGeneratorModalLabel">
                    <i class="bi bi-stars text-primary me-2"></i>Generador inteligente de ofertas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Proporciona los datos clave del perfil objetivo. El asistente usará el título, requisitos,
                    beneficios,
                    modalidad, contrato y ubicación que ya tengas en el formulario para redactar una descripción
                    completa.
                </p>
                <form id="jobOfferAiForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="aiProfileLevel" class="form-label fw-semibold">Nivel del perfil objetivo</label>
                            <select id="aiProfileLevel" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="junior">Junior</option>
                                <option value="mid">Mid</option>
                                <option value="senior">Senior</option>
                                <option value="lead">Lead</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="aiProfileOrientation" class="form-label fw-semibold">Orientación</label>
                            <select id="aiProfileOrientation" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="tecnico">Técnico</option>
                                <option value="funcional">Funcional</option>
                                <option value="estrategico">Estratégico</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="aiProfileSpecialization" class="form-label fw-semibold">Especialización</label>
                            <input type="text" id="aiProfileSpecialization" class="form-control" maxlength="255"
                                placeholder="Ej: Backend en PHP/Laravel" required>
                        </div>
                        <div class="col-md-6">
                            <label for="aiProfileExperience" class="form-label fw-semibold">Experiencia
                                aproximada</label>
                            <input type="text" id="aiProfileExperience" class="form-control" maxlength="255" required
                                placeholder="Ej: 5+ años liderando equipos tech">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="aiJobTitlePreview" class="form-label fw-semibold">Título actual de la oferta</label>
                        <input type="text" id="aiJobTitlePreview" class="form-control" readonly>
                        <div class="form-text">Se sincroniza automáticamente con el campo "Título de la oferta".</div>
                    </div>

                    <div class="mt-3">
                        <label for="aiAdditionalContext" class="form-label fw-semibold">Notas adicionales para el
                            asistente</label>
                        <textarea id="aiAdditionalContext" class="form-control" rows="3" maxlength="4000"
                            placeholder="Añade propósito del rol, retos, cultura del equipo, etc. (opcional)"></textarea>
                        <div class="form-text">Los requisitos y beneficios se toman directamente de los otros campos del
                            formulario.</div>
                    </div>

                    <div id="jobOfferAiResult" class="alert d-none mt-3" role="alert"></div>

                    <div id="jobOfferAiPreviewWrapper" class="d-none mt-3">
                        <label for="jobOfferAiPreview" class="form-label fw-semibold">Vista previa generada</label>
                        <textarea id="jobOfferAiPreview" class="form-control" rows="12"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Puedes editar el texto antes de insertarlo en la oferta.</small>
                            <button type="button" id="jobOfferAiApplyBtn" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-down-circle me-1"></i>Usar en la oferta
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link text-decoration-none" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="jobOfferAiSubmitBtn" form="jobOfferAiForm">
                    <span class="label-text">
                        <i class="bi bi-magic me-2"></i>Generar descripción
                    </span>
                    <span class="spinner-border spinner-border-sm align-middle d-none ms-2" role="status"
                        aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('jobOfferAiGeneratorModal');
        if (!modalEl) {
            return;
        }

        const form = document.getElementById('jobOfferAiForm');
        const resultAlert = document.getElementById('jobOfferAiResult');
        const previewWrapper = document.getElementById('jobOfferAiPreviewWrapper');
        const previewField = document.getElementById('jobOfferAiPreview');
        const applyButton = document.getElementById('jobOfferAiApplyBtn');
        const submitButton = document.getElementById('jobOfferAiSubmitBtn');
        const spinner = submitButton.querySelector('.spinner-border');
        const endpoint = "{{ route('empresa.ofertas.generate-description') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const fieldRefs = {
            level: document.getElementById('aiProfileLevel'),
            orientation: document.getElementById('aiProfileOrientation'),
            specialization: document.getElementById('aiProfileSpecialization'),
            experience: document.getElementById('aiProfileExperience'),
            additionalContext: document.getElementById('aiAdditionalContext'),
            titlePreview: document.getElementById('aiJobTitlePreview'),
        };

        modalEl.addEventListener('show.bs.modal', () => {
            const titleField = document.getElementById('title');
            const requirementsField = document.getElementById('requirements');
            const benefitsField = document.getElementById('benefits');

            fieldRefs.titlePreview.value = titleField ? titleField.value : '';

            if (!fieldRefs.specialization.value && titleField && titleField.value) {
                fieldRefs.specialization.value = titleField.value;
            }

            if (!fieldRefs.additionalContext.value && requirementsField && requirementsField.value) {
                fieldRefs.additionalContext.value = requirementsField.value;
            }

            if (!fieldRefs.additionalContext.value && benefitsField && benefitsField.value) {
                fieldRefs.additionalContext.value = benefitsField.value;
            }
        });

        const setLoading = (isLoading) => {
            submitButton.disabled = isLoading;
            if (isLoading) {
                spinner.classList.remove('d-none');
            } else {
                spinner.classList.add('d-none');
            }
        };

        const showAlert = (message, type = 'info') => {
            resultAlert.textContent = message;
            resultAlert.className = `alert alert-${type} mt-3`;
            if (message) {
                resultAlert.classList.remove('d-none');
            } else {
                resultAlert.classList.add('d-none');
            }
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const titleField = document.getElementById('title');
            const requirementsField = document.getElementById('requirements');
            const benefitsField = document.getElementById('benefits');
            const modalityField = document.getElementById('modality');
            const contractField = document.getElementById('contract_type');
            const locationField = document.getElementById('location');

            const payload = {
                title: (titleField?.value || '').trim(),
                level: fieldRefs.level.value,
                orientation: fieldRefs.orientation.value,
                specialization: fieldRefs.specialization.value.trim(),
                experience: fieldRefs.experience.value.trim(),
                additional_context: fieldRefs.additionalContext.value.trim(),
                requirements: (requirementsField?.value || '').trim(),
                benefits: (benefitsField?.value || '').trim(),
                modality: modalityField?.value || null,
                contract_type: contractField?.value || null,
                location: (locationField?.value || '').trim(),
            };

            if (!payload.title) {
                showAlert('Completa el título de la oferta antes de usar el generador.', 'warning');
                return;
            }

            setLoading(true);
            showAlert('Generando propuesta con IA...', 'info');
            previewWrapper.classList.add('d-none');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (!response.ok) {
                    const errorMessage = data?.message ?? 'No se pudo generar la descripción.';
                    if (response.status === 422 && data?.errors) {
                        const validationMessages = Object.values(data.errors).flat().join(' ');
                        showAlert(validationMessages || errorMessage, 'danger');
                    } else {
                        showAlert(errorMessage, 'danger');
                    }
                    return;
                }

                previewField.value = data.description ?? '';
                previewWrapper.classList.remove('d-none');
                showAlert(
                    'Descripción generada. Revisa el texto y presióna "Usar en la oferta" para insertarlo.',
                    'success');
            } catch (error) {
                console.error('AI Generator error', error);
                showAlert('Ocurrió un error inesperado al contactar con el generador.', 'danger');
            } finally {
                setLoading(false);
            }
        });

        applyButton.addEventListener('click', () => {
            const descriptionField = document.getElementById('description');
            if (!descriptionField) {
                showAlert('No se encontró el campo de descripción en el formulario.', 'danger');
                return;
            }

            const textToApply = previewField.value.trim();
            if (!textToApply) {
                showAlert('Aún no hay texto generado para insertar.', 'warning');
                return;
            }

            descriptionField.value = textToApply;
            descriptionField.dispatchEvent(new Event('input'));
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            modalInstance?.hide();

            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show mt-3';
            toast.innerHTML = `<i class="bi bi-check-circle me-2"></i>Texto insertado en la oferta correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>`;

            const targetCard = descriptionField.closest('.card-body') || document.querySelector(
                '.container');
            targetCard?.prepend(toast);
        });
    });
</script>
