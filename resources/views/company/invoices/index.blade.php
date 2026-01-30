@extends('layouts.app')

@section('title', 'Mis Facturas')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold text-gray-800">Historial de Facturación</h1>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Número Factura</th>
                                <th class="py-3">Fecha Emisión</th>
                                <th class="py-3">Concepto</th>
                                <th class="py-3">Total</th>
                                <th class="pe-4 text-end py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary">
                                        #{{ $invoice->invoice_number }}
                                    </td>
                                    <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($invoice->bonoPurchase)
                                            Compra de Bono ({{ $invoice->bonoPurchase->bonoCatalog->name ?? 'Bono' }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="fw-bold">{{ number_format($invoice->total_amount, 2) }} €</td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('empresa.invoices.pdf', $invoice) }}" target="_blank"
                                            class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt display-6 mb-3 d-block opacity-50"></i>
                                        No tienes facturas registradas.
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
