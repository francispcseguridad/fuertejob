@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h2>✏️ Editar Experiencia</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('worker.experiencias.update', $experiencia) }}">
                            @csrf
                            @method('PUT')

                            @include('worker.experiences._form', ['experience' => $experiencia])

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('worker.experiencias.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Actualizar Experiencia</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
