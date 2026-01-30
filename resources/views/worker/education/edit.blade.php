@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h2>✏️ Editar Registro de Educación</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('worker.educacion.update', $educacion) }}">
                            @csrf
                            @method('PUT')

                            @include('worker.education._form', ['education' => $educacion])

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('worker.educacion.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Actualizar Registro</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
