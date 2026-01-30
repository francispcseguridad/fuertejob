@extends('layouts.app')
@section('title', 'Consultas recibidas')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Consultas de contacto</h1>
                <p class="text-muted mb-0">Revisa, filtra y responde a las dudas que envían visitantes, empresas y
                    trabajadores.</p>
            </div>
            <a href="{{ route('contact.create') }}" class="btn btn-outline-primary">Ver formulario público</a>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.contact_messages.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="name">Nombre</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="{{ $filters['name'] ?? '' }}" placeholder="Nombre o apellido">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="role_type">Rol</label>
                        <select name="role_type" id="role_type" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($roleTypes as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['role_type'] ?? '') === $key)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="inquiry_type">Tipo</label>
                        <select name="inquiry_type" id="inquiry_type" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($inquiryTypes as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['inquiry_type'] ?? '') === $key)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="status">Estado</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="new" @selected(($filters['status'] ?? '') === 'new')>Nuevo</option>
                            <option value="responded" @selected(($filters['status'] ?? '') === 'responded')>Respondido</option>
                            <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Cerrado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_from">Fecha desde</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                            value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="date_to">Fecha hasta</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                            value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.contact_messages.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr>
                                <td>{{ $message->id }}</td>
                                <td>{{ $message->first_name }} {{ $message->last_name }}</td>
                                <td>{{ $message->email }}</td>
                                <td>{{ $roleTypes[$message->role_type] ?? ucfirst($message->role_type) }}</td>
                                <td>{{ $inquiryTypes[$message->inquiry_type] ?? ucfirst($message->inquiry_type) }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $message->status === 'responded' ? 'success' : ($message->status === 'closed' ? 'secondary' : 'warning') }}">
                                        {{ ucfirst($message->status) }}
                                    </span>
                                </td>
                                <td>{{ $message->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.contact_messages.show', $message) }}"
                                        class="btn btn-sm btn-outline-primary">Ver</a>
                                    <a href="{{ route('admin.contact_messages.print', $message) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No hay registros que coincidan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
@endsection
