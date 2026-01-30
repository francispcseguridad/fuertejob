@extends('layouts.app')

@section('title', 'Consulta #' . $contactMessage->id . ' - PDF')

@section('content')
    <div class="container py-5" style="max-width: 800px;">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="fw-bold mb-1">Consulta #{{ $contactMessage->id }}</h2>
                <p class="text-muted mb-3">Recibida el {{ $contactMessage->created_at->format('d/m/Y H:i') }}</p>

                <hr>
                <h5 class="fw-semibold mt-3">Datos del remitente</h5>
                <p class="mb-1"><strong>Nombre:</strong> {{ $contactMessage->first_name }} {{ $contactMessage->last_name }}
                </p>
                <p class="mb-1"><strong>Email:</strong> {{ $contactMessage->email }}</p>
                <p class="mb-1"><strong>Rol:</strong>
                    {{ $roleTypes[$contactMessage->role_type] ?? $contactMessage->role_type }}</p>
                <p class="mb-1"><strong>Tipo de consulta:</strong>
                    {{ $inquiryTypes[$contactMessage->inquiry_type] ?? $contactMessage->inquiry_type }}
                </p>
                <p class="mb-1"><strong>Estado:</strong> {{ ucfirst($contactMessage->status) }}</p>

                <hr>
                <h5 class="fw-semibold mt-3">Mensaje</h5>
                <p style="white-space: pre-line;">{{ $contactMessage->message }}</p>

                @if ($contactMessage->attachment_path)
                    <hr>
                    <h5 class="fw-semibold mt-3">Adjunto</h5>
                    <p>{{ basename($contactMessage->attachment_path) }}</p>
                @endif

                @if ($contactMessage->response_message)
                    <hr>
                    <h5 class="fw-semibold mt-3">Respuesta registrada</h5>
                    <p style="white-space: pre-line;">{{ $contactMessage->response_message }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.print();
            window.onafterprint = () => window.close();
        });
    </script>
@endsection
