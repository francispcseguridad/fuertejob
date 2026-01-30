@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h2>➕ Añadir Nuevo Registro de Educación</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('worker.education.store') }}">
                            @csrf

                            @include('worker.education._form', [
                                'education' => new \App\Models\Education(),
                            ])

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('worker.education.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Guardar Registro</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
