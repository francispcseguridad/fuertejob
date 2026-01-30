@extends('layouts.app')

@section('title', 'Gestión de CTA Empresas')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">CTA Empresas</h1>
                <p class="text-muted small mb-0">Sección de llamada a la acción para empresas.</p>
            </div>
            <a href="{{ route('admin.home_company_ctas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nuevo CTA
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if ($ctas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Título</th>
                                    <th>Botón</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ctas as $cta)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">{{ $cta->title }}</td>
                                        <td class="text-muted">{{ $cta->button_text }}</td>
                                        <td>
                                            @if ($cta->is_active)
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Activo</span>
                                            @else
                                                <span
                                                    class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.home_company_ctas.edit', $cta->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.home_company_ctas.destroy', $cta->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este elemento?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                        </div>
                        <h5 class="text-muted">No hay registros encontrados</h5>
                        <p class="text-muted small">Crea un nuevo CTA para comenzar.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
