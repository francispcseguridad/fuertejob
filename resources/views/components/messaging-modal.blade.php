<div class="modal fade" id="messagingModal" tabindex="-1" aria-labelledby="messagingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messagingModalLabel">Mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="messagingThreadContent" style="max-height: 60vh; overflow-y:auto;">
                    <div class="text-center text-muted py-5">
                        Cargando conversación...
                    </div>
                </div>
                <form id="messagingModalForm" class="mt-4">
                    @csrf
                    <input type="hidden" name="thread_id" id="messagingThreadId">
                    <div class="mb-3">
                        <label for="messagingContent" class="form-label">Escribe tu mensaje</label>
                        <textarea name="content" id="messagingContent" rows="3" class="form-control" required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        let messagingModal;

        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('messagingModal');
            messagingModal = new bootstrap.Modal(modalEl);

            document.getElementById('messagingModalForm').addEventListener('submit', async function(event) {
                event.preventDefault();
                const threadId = document.getElementById('messagingThreadId').value;
                const content = document.getElementById('messagingContent').value.trim();
                if (!content) {
                    return;
                }

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/mensajes/${threadId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content
                    })
                });

                const payload = await response.json();
                if (!response.ok) {
                    alert('No se pudo enviar el mensaje.');
                    return;
                }

                document.getElementById('messagingContent').value = '';
                await loadMessagingThread(threadId);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Mensaje enviado',
                        text: payload?.message ?? 'Tu mensaje se ha enviado correctamente.',
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false,
                    });
                }
            });
        });

        window.loadMessagingThread = async function loadMessagingThread(threadId) {
            const response = await fetch(`/mensajes/${threadId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                document.getElementById('messagingThreadContent').innerHTML =
                    '<div class="text-danger text-center py-5">No fue posible cargar la conversación.</div>';
                return;
            }

            const thread = await response.json();
            document.getElementById('messagingModalLabel').textContent =
                `Mensaje con ${thread.interlocutor?.name ?? 'contacto'}`;
            document.getElementById('messagingThreadId').value = thread.id;

            const container = document.getElementById('messagingThreadContent');
            container.innerHTML = '';

            const currentUserId = @json(auth()->id());

            thread.messages.forEach(message => {
                const isMine = message.sender_id === currentUserId;
                const row = document.createElement('div');
                row.className = `mb-3 d-flex ${isMine ? 'justify-content-end' : 'justify-content-start'}`;

                const bubble = document.createElement('div');
                bubble.className = `p-3 rounded ${isMine ? 'bg-primary text-white' : 'bg-light border'}`;
                bubble.style.maxWidth = '65%';

                const header = document.createElement('div');
                header.className = 'small text-uppercase mb-1 fw-semibold';
                header.textContent = isMine ? 'Tú' : (message.sender?.name ?? 'Interlocutor');

                const body = document.createElement('div');
                body.innerHTML = message.content.replace(/\n/g, '<br>');

                const footer = document.createElement('div');
                footer.className = 'small text-muted mt-2';
                footer.textContent = new Date(message.created_at).toLocaleString();

                bubble.appendChild(header);
                bubble.appendChild(body);
                bubble.appendChild(footer);
                row.appendChild(bubble);
                container.appendChild(row);
            });

            container.scrollTop = container.scrollHeight;
        }

        window.openMessagingModal = async function openMessagingModal(threadId) {
            await loadMessagingThread(threadId);
            messagingModal.show();
        }

        window.startMessagingThread = async function startMessagingThread(recipientId, resourceType, resourceId) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('{{ route('messaging.start_ajax') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    recipient_id: recipientId,
                    resource_type: resourceType,
                    resource_id: resourceId
                })
            });

            if (!response.ok) {
                alert('No se pudo iniciar la conversación.');
                return;
            }

            const data = await response.json();
            await openMessagingModal(data.thread_id);
        }
    </script>
@endpush
