@extends('layouts.app')
@section('title', 'Ofertas de trabajo')
@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold text-dark">Ofertas de Trabajo</h1>
                <p class="text-muted mb-0">Listado administrado. Edita o visualiza como lo ven los candidatos.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary"><i
                    class="bi bi-arrow-left me-1"></i>Volver
                al dashboard</a>
        </div>
        <div class="mb-3">
            <a href="{{ route('admin.ofertas.pendientes') }}" class="btn btn-warning">
                <i class="bi bi-hourglass-split me-1"></i>Ofertas pendientes
                @if (!empty($pendingCount))
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                @endif
            </a>
        </div>
        {{-- Filtros --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Título o ubicación">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            @foreach (['Borrador', 'Publicado', 'Finalizada'] as $st)
                                <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Empresa</label>
                        <select name="company_profile_id" class="form-select">
                            <option value="">Todas</option>
                            <option value="0" @selected(request('company_profile_id') === '0')>Sin empresa (0)</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(request('company_profile_id') == $company->id)>
                                    {{ $company->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Isla</label>
                        <select name="island" class="form-select">
                            <option value="">Todas</option>
                            @foreach ($islands as $island)
                                <option value="{{ $island }}" @selected(request('island') === $island)>{{ $island }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Desde</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}"
                                    class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a class="btn btn-light" href="{{ route('admin.ofertas.index') }}">Limpiar</a>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Ubicación</th>
                            <th>Isla</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($offers as $offer)
                            <tr>
                                <td>{{ $offer->id }}</td>
                                <td class="fw-semibold text-dark">{{ $offer->title }}</td>
                                <td>{{ $offer->companyProfile->company_name ?? 'Sin empresa (0)' }}</td>
                                <td>{{ $offer->location }}</td>
                                <td>{{ $offer->island ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $offer->status }}</span></td>
                                <td>{{ optional($offer->created_at)->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('public.jobs.show', $offer->id) }}"
                                        class="btn btn-sm btn-outline-secondary" target="_blank"><i
                                            class="bi bi-box-arrow-up-right me-1"></i>Ver pública</a>
                                    <a href="{{ route('admin.ofertas.edit', $offer) }}" class="btn btn-sm btn-primary"><i
                                            class="bi bi-pencil-square me-1"></i>Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No hay ofertas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                {{ $offers->links() }}
            </div>
        </div>
    </div>
@endsection
