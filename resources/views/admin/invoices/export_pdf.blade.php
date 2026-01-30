<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Facturas</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #0d6efd;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f8f9fa;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 10px;
            color: #666;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 8px 16px; cursor: pointer; background: #0d6efd; color: white; border: none; border-radius: 4px;">Imprimir
            / Guardar PDF</button>
    </div>

    <div class="header">
        <h1>Listado de Facturas Emitidas</h1>
        <p>Fecha de generación: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Empresa</th>
                <th>Concepto</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSum = 0; @endphp
            @foreach ($invoices as $invoice)
                @php
                    $companyProfile = optional(optional($invoice->bonoPurchase)->companyProfile);
                    $companyUser = optional($companyProfile->user);
                    $totalSum += $invoice->total_amount;
                @endphp
                <tr>
                    <td class="fw-bold">#{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->issue_date?->format('d/m/Y') }}</td>
                    <td>
                        {{ $companyProfile->company_name ?? ($companyUser->name ?? 'Sin datos') }}<br>
                        <small style="color: #777;">{{ $companyUser->email ?? '' }}</small>
                    </td>
                    <td>
                        @if ($invoice->is_rectificativa)
                            Rectificativa de #{{ $invoice->rectifiesInvoice->invoice_number ?? '—' }}
                        @elseif (optional($invoice->bonoPurchase)->bonoCatalog)
                            Compra: {{ $invoice->bonoPurchase->bonoCatalog->name }}
                        @else
                            Servicios
                        @endif
                    </td>
                    <td class="text-end fw-bold">{{ number_format($invoice->total_amount, 2) }} €</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end fw-bold" style="padding-top: 15px;">TOTAL ACUMULADO:</td>
                <td class="text-end fw-bold" style="padding-top: 15px; font-size: 14px; border-top: 2px solid #333;">
                    {{ number_format($totalSum, 2) }} €</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Este documento es un extracto informativo del sistema de gestión.
    </div>
</body>

</html>
