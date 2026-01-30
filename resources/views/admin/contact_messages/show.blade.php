@extends('layouts.app')
@section('title', 'Consulta #' . $contactMessage->id)

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">Consulta #{{ $contactMessage->id }}</h1>
                <p class="text-muted mb-0">Recibida el {{ $contactMessage->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.contact_messages.index') }}" class="btn btn-outline-secondary me-2">Volver</a>
                <a href="{{ route('admin.contact_messages.print', $contactMessage) }}" target="_blank"
                    class="btn btn-outline-primary">Imprimir</a>
            </div>
        </div>

        @if (session('success_response'))
            <div class="alert alert-success">
                {{ session('success_response') }}
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Datos del remitente</h5>
                        <p class="mb-1"><strong>Nombre:</strong> {{ $contactMessage->first_name }}
                            {{ $contactMessage->last_name }}
                            @if ($contactMessage->user_id)
                                <span class="badge bg-info ms-2">ID {{ $contactMessage->user_id }}</span>
                            @endif
                        </p>
                        <p class="mb-1"><strong>Email:</strong> {{ $contactMessage->email }}</p>
                        <p class="mb-1"><strong>Rol:</strong>
                            {{ $roleTypes[$contactMessage->role_type] ?? $contactMessage->role_type }}</p>
                        <p class="mb-1"><strong>Consulta:</strong>
                            {{ $inquiryTypes[$contactMessage->inquiry_type] ?? $contactMessage->inquiry_type }}
                        </p>
                        <p class="mb-0"><strong>Estado:</strong>
                            <span
                                class="badge bg-{{ $contactMessage->status === 'responded' ? 'success' : ($contactMessage->status === 'closed' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($contactMessage->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                @if ($contactMessage->attachment_path)
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Adjunto enviado</h5>
                            <a href="{{ asset('storage/' . $contactMessage->attachment_path) }}" target="_blank"
                                class="btn btn-outline-info btn-sm">
                                <i class="bi bi-paperclip me-1"></i> Abrir imagen
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="fw-semibold">Descripción recibida</h5>
                        <p class="text-muted mb-0" style="white-space: pre-line;">{{ $contactMessage->message }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Responder</h5>
                <form action="{{ route('admin.contact_messages.respond', $contactMessage) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="response_message">Mensaje de respuesta</label>
                        <textarea name="response_message" id="response_message" rows="5"
                            class="form-control @error('response_message') is-invalid @enderror" required>{{ old('response_message', $contactMessage->response_message) }}</textarea>
                        @error('response_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="status">Actualizar estado</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="responded" @selected(old('status', $contactMessage->status) === 'responded')>Respondido
                            </option>
                            <option value="closed" @selected(old('status', $contactMessage->status) === 'closed')>Cerrado</option>
                            <option value="new" @selected(old('status', $contactMessage->status) === 'new')>Pendiente</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">Enviar respuesta</button>
                        <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
