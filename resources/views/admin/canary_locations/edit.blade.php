@extends('layouts.app')

@section('title', 'Editar municipio/localidad (Canarias)')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-0">Editar municipio / localidad</h1>
                <p class="text-muted mb-0">Actualiza los datos de la población seleccionada.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                @include('admin.canary_locations._form', [
                    'location' => $location,
                    'action' => route('admin.localidades.update', $location),
                ])
            </div>
        </div>
    </div>
@endsection
