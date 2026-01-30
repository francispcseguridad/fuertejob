@extends('layouts.app')
@section('title', 'Detalle Contacto Comercial')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap">
            <div>
                <h1 class="h4 mb-1">Contacto comercial #{{ $contact->id }}</h1>
                <p class="text-muted mb-0">
                    {{ $contact->origin }} ·
                    {{ optional($contact->created_at)->format('d/m/Y H:i') ?? 'Fecha no disponible' }}
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.contactos-comerciales.index') }}" class="btn btn-outline-secondary">Volver</a>
                <form action="{{ route('admin.contactos-comerciales.update', $contact) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_read" value="{{ $contact->is_read ? 0 : 1 }}">
                    <button type="submit" class="btn btn-{{ $contact->is_read ? 'warning' : 'success' }}">
                        {{ $contact->is_read ? 'Marcar como no leído' : 'Marcar como leído' }}
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="mb-2"><strong>Nombre:</strong> {{ $contact->name }}</p>
                        <p class="mb-2"><strong>Teléfono:</strong> {{ $contact->phone }}</p>
                        <p class="mb-2"><strong>Email:</strong> {{ $contact->email }}</p>
                        <p class="mb-2"><strong>Origen:</strong> {{ $contact->origin }}</p>
                        <p class="mb-0"><strong>IP:</strong> {{ $contact->ip_address ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Detalle</h5>
                            <span class="badge bg-{{ $contact->is_read ? 'success' : 'warning' }}">
                                {{ $contact->is_read ? 'Leído' : 'No leído' }}
                            </span>
                        </div>
                        <p class="text-muted mb-0" style="white-space: pre-line;">{{ $contact->detail }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
