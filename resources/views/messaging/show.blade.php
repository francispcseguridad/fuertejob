@extends('layouts.app')

@section('title', 'Mensajes con ' . ($thread->interlocutor['name'] ?? 'Contacto'))

@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1">Conversación con {{ $thread->interlocutor['name'] ?? 'usuario' }}</h1>
                <p class="text-muted small mb-0">
                    {{ $thread->interlocutor['role_label'] ?? 'Contacto' }} · Último mensaje
                    {{ optional($thread->last_message_at)->diffForHumans() }}
                </p>
            </div>
            <a href="{{ route('messaging.inbox') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a bandeja
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-4" style="max-height: 60vh; overflow-y: auto;">
                    @foreach ($thread->messages as $message)
                        @php
                            $isMine = $message->sender_id === auth()->id();
                        @endphp
                        <div class="mb-3 d-flex {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="p-3 rounded {{ $isMine ? 'bg-primary text-white' : 'bg-light border' }}"
                                style="max-width: 65%;">
                                <div class="small text-uppercase mb-1 fw-semibold">
                                    {{ $isMine ? 'Tú' : $message->sender->name ?? 'Remitente' }}
                                </div>
                                <div>{!! nl2br(e($message->content)) !!}</div>
                                <div class="small text-muted mt-2">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('messaging.send', $thread) }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-3">
                        <label for="content" class="form-label">Escribe tu mensaje</label>
                        <textarea name="content" id="content" rows="3" class="form-control @error('content') is-invalid @enderror"
                            placeholder="Escribe aquí..." required></textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
@endsection
