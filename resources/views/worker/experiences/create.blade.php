@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2>➕ Añadir Nueva Experiencia</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('worker.experiences.store') }}">
                            @csrf

                            @include('worker.experiences._form', [
                                'experience' => new \App\Models\Experience(),
                            ])

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('worker.experiences.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">Guardar Experiencia</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
