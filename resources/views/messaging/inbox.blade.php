@extends('layouts.app')

@section('title', 'Bandeja de Entrada')

@section('content')
    <div class="container-fluid bg-light min-vh-100 py-4">

        <div class="container">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="display-6 fw-bold text-dark mb-1">
                        <i class="bi bi-chat-dots-fill text-primary me-2"></i>Mensajería
                    </h1>
                    <p class="text-muted lead fs-6 mb-0">Gestiona tus comunicaciones de forma profesional.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Message List Column -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden" style="min-height: 70vh;">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="fw-bold mb-0 text-secondary">Bandeja de Entrada</h5>
                        </div>

                        <div class="list-group list-group-flush h-100" id="thread-list">
                            @forelse($threads as $thread)
                                <button type="button"
                                    class="list-group-item list-group-item-action p-4 border-bottom position-relative transition-hover {{ $thread['unread_count'] > 0 ? 'bg-primary-subtle' : 'bg-white' }}"
                                    onclick="openThreadModal({{ $thread['id'] }})">

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3 w-100">
                                            <!-- Info -->
                                            <div class="text-start w-100">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <h6 class="mb-0 fw-bold text-dark fs-5">
                                                            {{ $thread['interlocutor']['name'] }}
                                                        </h6>
                                                        <span class="badge rounded-pill border fw-normal text-muted"
                                                            style="font-size: 0.7rem;">
                                                            {{ $thread['interlocutor']['role_label'] }}
                                                        </span>
                                                        @if ($thread['unread_count'] > 0)
                                                            <span
                                                                class="badge rounded-pill bg-danger animate__animated animate__pulse animate__infinite">
                                                                Nuevo
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <p class="mb-0 text-muted text-truncate small" style="max-width: 90%;">
                                                    <i class="bi bi-reply-fill me-1 opacity-50"></i>
                                                    @if (count($thread['messages']) > 0)
                                                        @php
                                                            $lastMsg = end($thread['messages']);
                                                            $prefix = $lastMsg['sender_id'] == Auth::id() ? 'Tú: ' : '';
                                                        @endphp
                                                        {{ $prefix . $lastMsg['content'] }}
                                                    @else
                                                        Conversación iniciada
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Meta -->
                                        <div class="text-end ps-3" style="min-width: 80px;">
                                            <small class="d-block text-muted fw-medium mb-1">
                                                {{ $thread['formatted_date'] }}
                                            </small>
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <div class="text-center p-5 mt-5">
                                    <div class="mb-3">
                                        <i class="bi bi-inbox fs-1 text-muted opacity-25"></i>
                                    </div>
                                    <h5 class="text-muted fw-bold">No hay conversaciones</h5>
                                    <p class="text-muted small">Tus mensajes aparecerán aquí cuando contactes con alguien.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <div class="modal fade" id="threadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                    <!-- Modal Header -->
                    <div class="modal-header bg-white border-bottom py-3 px-4 align-items-center">
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="modalThreadName">Cargando...</h5>
                            <small class="text-muted" id="modalThreadRole"></small>
                        </div>
                        <button type="button" class="btn-close shadow-none bg-light rounded-circle p-2"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body (Chat Area) -->
                    <div class="modal-body bg-light p-4" id="chatContainer" style="height: 500px; overflow-y: auto;">
                        <div id="messagesList" class="d-flex flex-column gap-3">
                            <!-- Messages will be injected here via JS -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer (Reply Input) -->
                    <div class="modal-footer bg-white border-top p-3">
                        <form id="messageForm" class="w-100">
                            <input type="hidden" id="currentThreadId">
                            <div class="input-group">
                                <input type="text" id="messageInput"
                                    class="form-control border-0 bg-light rounded-start-pill ps-4 py-3 shadow-none focus-ring"
                                    placeholder="Escribe un mensaje..." required disabled>
                                <button class="btn btn-primary rounded-end-pill px-4" type="submit" id="sendButton"
                                    disabled>
                                    <i class="bi bi-send-fill" id="sendIcon"></i>
                                    <span class="spinner-border spinner-border-sm d-none" id="sendSpinner" role="status"
                                        aria-hidden="true"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- CSS Styles -->
    <style>
        .bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .transition-hover {
            transition: all 0.2s ease-in-out;
        }

        .transition-hover:hover {
            background-color: #f8f9fa !important;
            cursor: pointer;
            transform: translateY(-1px);
        }

        #chatContainer::-webkit-scrollbar {
            width: 6px;
        }

        #chatContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #chatContainer::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        #chatContainer::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }

        .focus-ring:focus {
            box-shadow: none;
            background-color: #fff !important;
        }
    </style>

    <!-- JS Logic (Vanilla + Fetch) -->
    <script>
        const currentUserId = {{ Auth::id() }};
        let modalInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            modalInstance = new bootstrap.Modal(document.getElementById('threadModal'));

            // Focus input on modal show
            document.getElementById('threadModal').addEventListener('shown.bs.modal', () => {
                scrollToBottom();
                document.getElementById('messageInput').focus();
            });

            // Handle Form Submit
            document.getElementById('messageForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                await sendMessage();
            });
        });

        async function openThreadModal(threadId) {
            // 1. Reset UI
            document.getElementById('modalThreadName').textContent = 'Cargando...';
            document.getElementById('modalThreadRole').textContent = '';
            document.getElementById('messagesList').innerHTML =
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
            document.getElementById('currentThreadId').value = threadId;
            document.getElementById('messageInput').value = '';
            document.getElementById('messageInput').disabled = true;
            document.getElementById('sendButton').disabled = true;

            // 2. Open Modal
            modalInstance.show();

            // 3. Fetch Data
            try {
                const response = await fetch(`{{ url('/mensajes') }}/${threadId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Error loading thread');

                const thread = await response.json();

                // 4. Update UI
                document.getElementById('modalThreadName').textContent = thread.interlocutor.name;
                document.getElementById('modalThreadRole').textContent = thread.interlocutor.role_label;

                renderMessages(thread.messages);

                // Enable inputs
                document.getElementById('messageInput').disabled = false;
                document.getElementById('sendButton').disabled = false;

                // Mark as read specifically if needed, otherwise Controller handled it.

            } catch (error) {
                console.error(error);
                document.getElementById('messagesList').innerHTML =
                    '<div class="text-center text-danger py-5">Error al cargar mensajes.</div>';
            }
        }

        function renderMessages(messages) {
            const container = document.getElementById('messagesList');
            if (!messages || messages.length === 0) {
                container.innerHTML =
                    '<div class="text-center text-muted py-5"><small>Inicio de la conversación</small></div>';
                return;
            }

            let html = '';
            messages.forEach(msg => {
                const isMe = msg.sender_id == currentUserId;
                const alignValues = isMe ? 'justify-content-end' : 'justify-content-start';
                const bubbleClass = isMe ? 'bg-primary text-white rounded-3 rounded-bottom-end-0' :
                    'bg-white text-dark border rounded-3 rounded-bottom-start-0';
                const metaValues = isMe ? 'text-end' : 'text-start';
                const checkIcon = (isMe && msg.read_at) ? '<i class="bi bi-check2-all ms-1 text-primary"></i>' : '';

                // Simple time format parsing
                const time = new Date(msg.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                html += `
                    <div class="d-flex ${alignValues}">
                        <div class="d-flex flex-column" style="max-width: 75%;">
                            <div class="p-3 shadow-sm position-relative ${bubbleClass}">
                                <p class="mb-0" style="white-space: pre-wrap; font-size: 0.95rem;">${escapeHtml(msg.content)}</p>
                            </div>
                            <small class="mt-1 px-1 text-muted ${metaValues}" style="font-size: 0.75rem;">
                                ${time} ${checkIcon}
                            </small>
                        </div>
                    </div>
                 `;
            });
            container.innerHTML = html;
            scrollToBottom();
        }

        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const threadId = document.getElementById('currentThreadId').value;
            const content = input.value.trim();
            if (!content) return;

            // Loading state
            input.disabled = true;
            document.getElementById('sendButton').disabled = true;
            document.getElementById('sendIcon').classList.add('d-none');
            document.getElementById('sendSpinner').classList.remove('d-none');

            try {
                const response = await fetch(`{{ url('/mensajes') }}/${threadId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: content
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Append message
                    // Ideally we fetch full thread again or just append HTML manually
                    // We'll append manually for speed
                    const container = document.getElementById('messagesList');
                    // Remove empty state if present
                    if (container.innerHTML.includes('Inicio de la conversación')) container.innerHTML = '';

                    const time = data.formatted_time;
                    const html = `
                    <div class="d-flex justify-content-end animate__animated animate__fadeInUp">
                        <div class="d-flex flex-column" style="max-width: 75%;">
                            <div class="p-3 shadow-sm position-relative bg-primary text-white rounded-3 rounded-bottom-end-0">
                                <p class="mb-0" style="white-space: pre-wrap; font-size: 0.95rem;">${escapeHtml(data.message.content)}</p>
                            </div>
                            <small class="mt-1 px-1 text-muted text-end" style="font-size: 0.75rem;">
                                ${time}
                            </small>
                        </div>
                    </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                    input.value = '';
                    scrollToBottom();
                } else {
                    alert('Error enviando mensaje');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión');
            } finally {
                input.disabled = false;
                document.getElementById('sendButton').disabled = false;
                document.getElementById('sendIcon').classList.remove('d-none');
                document.getElementById('sendSpinner').classList.add('d-none');
                input.focus();
            }
        }

        function scrollToBottom() {
            const el = document.getElementById('chatContainer');
            el.scrollTop = el.scrollHeight;
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    </script>
@endsection
