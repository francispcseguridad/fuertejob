@extends('layouts.app')
@section('title', 'Facturas del Portal')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-6 fw-bold text-dark">Gestión de Facturas</h1>
                <p class="text-muted mb-0">Consulta y exporta todas las facturas emitidas.</p>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-primary rounded-pill dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-download me-1"></i> Exportar
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">
                                <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i> CSV (Excel)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}"
                                target="_blank">
                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> PDF (Imprimir)
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('admin.facturas.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="company" class="form-label small fw-bold text-muted">Empresa / Cliente</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="company" id="company" class="form-control bg-light border-start-0"
                                placeholder="Nombre o CIF..." value="{{ request('company') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label small fw-bold text-muted">Desde</label>
                        <input type="date" name="start_date" id="start_date" class="form-control bg-light"
                            value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label small fw-bold text-muted">Hasta</label>
                        <input type="date" name="end_date" id="end_date" class="form-control bg-light"
                            value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="concept" class="form-label small fw-bold text-muted">Tipo Concepto</label>
                        <select name="concept" id="concept" class="form-select bg-light">
                            <option value="">Todos</option>
                            <option value="compra" {{ request('concept') == 'compra' ? 'selected' : '' }}>Compra Bonos
                            </option>
                            <option value="rectificativa" {{ request('concept') == 'rectificativa' ? 'selected' : '' }}>
                                Rectificativas</option>
                            <option value="servicios" {{ request('concept') == 'servicios' ? 'selected' : '' }}>
                                Servicios/Otros</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1 rounded-pill">
                            <i class="bi bi-filter me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.facturas.index') }}" class="btn btn-light rounded-pill" title="Limpiar">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4">Factura</th>
                                <th>Fecha</th>
                                <th>Empresa</th>
                                <th>Concepto</th>
                                <th>Total</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                @php
                                    $companyProfile = optional(optional($invoice->bonoPurchase)->companyProfile);
                                    $companyUser = optional($companyProfile->user);
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary">#{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->issue_date?->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $companyProfile->company_name ?? ($companyUser->name ?? 'Sin datos') }}
                                        </div>
                                        <small class="text-muted">{{ $companyUser->email ?? '—' }}</small>
                                    </td>
                                    <td>
                                        @if ($invoice->is_rectificativa)
                                            Factura Rectificativa de
                                            #{{ $invoice->rectifiesInvoice->invoice_number ?? '—' }}
                                        @elseif (optional($invoice->bonoPurchase)->bonoCatalog)
                                            Compra de Bono ({{ $invoice->bonoPurchase->bonoCatalog->name }})
                                        @else
                                            Servicios
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ number_format($invoice->total_amount, 2) }} €</td>
                                    <td class="pe-4 text-end">
                                        @if (!$invoice->is_rectificativa)
                                            <form action="{{ route('admin.facturas.rectificar', $invoice) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Anular
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.facturas.pdf', $invoice) }}" target="_blank"
                                            class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt display-6 mb-3 d-block opacity-50"></i>
                                        No hay facturas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($invoices->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
