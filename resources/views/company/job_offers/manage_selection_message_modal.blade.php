    {{-- MODAL PARA ENVIAR MENSAJE --}}
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold" id="messageModalLabel">
                        <i class="bi bi-envelope-fill me-2"></i>Enviar Mensaje a<br> {{ $worker->user->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="message-form">
                        @csrf
                        <input type="hidden" name="selection_id" value="{{ $selection->id }}">

                        <div class="mb-3">
                            <label for="message_content" class="form-label fw-semibold text-secondary">Mensaje</label>
                            <textarea class="form-control" id="message_content" name="message" rows="5" required
                                placeholder="Escribe tu mensaje aquí..."></textarea>
                            <div class="invalid-feedback">El mensaje no puede estar vacío.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-secondary rounded-pill"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="send-message-btn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"
                            id="send-message-spinner"></span>
                        Enviar Mensaje
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (existing code) ...

            // =========================================================
            // MÓDULO 4: ENVIAR MENSAJE (MODAL)
            // =========================================================
            (function() {
                const modalEl = document.getElementById('messageModal');
                const form = document.getElementById('message-form');
                const btn = document.getElementById('send-message-btn');

                if (!modalEl || !form || !btn) return;

                const spinner = document.getElementById('send-message-spinner');
                const modal = new bootstrap.Modal(modalEl);
                let isProcessing = false;

                // Limpiar form al abrir
                modalEl.addEventListener('show.bs.modal', function() {
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                });

                async function sendMessage(e) {
                    e.preventDefault();
                    if (isProcessing) return;

                    const messageInput = document.getElementById('message_content');
                    const message = messageInput.value.trim();

                    if (!message) {
                        messageInput.classList.add('is-invalid');
                        return;
                    } else {
                        messageInput.classList.remove('is-invalid');
                    }

                    isProcessing = true;
                    btn.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');

                    try {
                        const formData = {
                            selection_id: form.querySelector('[name="selection_id"]').value,
                            message: message,
                            _token: document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        };

                        const response = await fetch('{{ route('messaging.contact.selection') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': formData._token
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (!response.ok) throw data;

                        if (data.success) {
                            // Mostrar alerta global usando la función definida en el scope global (si es accesible)
                            // O hacer un simple alert/toast
                            // Asumimos que showAlert está disponible globalmente o definimos fallback
                            const alertContainer = document.getElementById('ajax-alert-container');
                            if (alertContainer) {
                                alertContainer.innerHTML = `
                                    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                                        <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                `;
                            } else {
                                alert(data.message);
                            }

                            modal.hide();
                        } else {
                            throw data;
                        }

                    } catch (error) {
                        console.error('Error sending message:', error);
                        alert(error.message || 'Error al enviar el mensaje via AJAX.');
                    } finally {
                        isProcessing = false;
                        btn.disabled = false;
                        if (spinner) spinner.classList.add('d-none');
                    }
                }

                btn.addEventListener('click', sendMessage);
            })();
        });
    </script>
