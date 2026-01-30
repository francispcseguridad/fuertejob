@extends('layouts.app')

@section('title', 'Nuevo municipio/localidad (Canarias)')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-0">Añadir municipio / localidad</h1>
                <p class="text-muted mb-0">Registra poblaciones canarias para complementar las búsquedas de ubicación.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                @include('admin.canary_locations._form', [
                    'location' => $location,
                    'action' => route('admin.localidades.store'),
                ])
            </div>
        </div>
    </div>
@endsection
