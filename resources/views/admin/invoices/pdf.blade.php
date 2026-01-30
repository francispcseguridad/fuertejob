<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            margin: 0;
            padding: 40px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            text-align: right;
            margin: 0;
            color: #555;
        }

        .invoice-meta {
            text-align: right;
            margin-top: 5px;
            color: #777;
        }

        .columns {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .column {
            width: 45%;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            color: #999;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .address p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            text-align: left;
            padding: 12px 0;
            border-bottom: 2px solid #ddd;
            text-transform: uppercase;
            font-size: 12px;
            color: #777;
        }

        td {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .text-end {
            text-align: right;
        }

        .totals {
            width: 300px;
            margin-left: auto;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 15px;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 10px 20px; cursor: pointer; background: #0d6efd; color: white; border: none; border-radius: 4px;">
            Imprimir / Guardar PDF</button>
    </div>

    <div class="invoice-header">
        <div class="company-logo">{{ $portalFiscalData->legal_name ?? 'FuerteJob' }}</div>
        <div>
            <h1 class="invoice-title">{{ $invoice->is_rectificativa ? 'FACTURA RECTIFICATIVA' : 'FACTURA' }}</h1>
            <div class="invoice-meta">#{{ $invoice->invoice_number }}</div>
            <div class="invoice-meta">Fecha: {{ $invoice->issue_date->format('d/m/Y') }}</div>
            @if ($invoice->is_rectificativa)
                <div class="invoice-meta">Rectificativa de #{{ $invoice->rectifiesInvoice->invoice_number ?? '—' }}
                </div>
            @endif
        </div>
    </div>

    <div class="columns">
        <div class="column">
            <div class="section-title">De (Emisor)</div>
            <div class="address">
                <p><strong>{{ $portalFiscalData->legal_name }}</strong></p>
                <p>{{ $portalFiscalData->fiscal_address }}</p>
                <p>NIF/CIF: {{ $portalFiscalData->vat_id }}</p>
                <p>{{ $portalFiscalData->email }}</p>
            </div>
        </div>
        <div class="column">
            <div class="section-title">Para (Cliente)</div>
            <div class="address">
                <p><strong>{{ $companyFiscalData->legal_name }}</strong></p>
                <p>{{ $companyFiscalData->fiscal_address }}</p>
                <p>NIF/CIF: {{ $companyFiscalData->vat_id }}</p>
                <p>{{ $companyFiscalData->email }}</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60%">Descripción</th>
                <th class="text-end">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Compra de Créditos/Bonos</strong><br>
                    <span style="color: #777; font-size: 14px;">
                        @if ($invoice->bonoPurchase && $invoice->bonoPurchase->bonoCatalog)
                            {{ $invoice->bonoPurchase->bonoCatalog->name }}
                        @else
                            Servicios Profesionales
                        @endif
                    </span>
                </td>
                <td class="text-end">{{ number_format($invoice->subtotal_amount, 2) }} €</td>
            </tr>
        </tbody>
    </table>

    @php
        $taxRate = isset($portalFiscalData->default_tax_rate) ? (float) $portalFiscalData->default_tax_rate : 0;
        $defaultIrpfRate = isset($portalFiscalData->default_irpf) ? (float) $portalFiscalData->default_irpf : 0;
        $rectificationIrpfRate = 15.0;
        $irpfRate = $invoice->is_rectificativa ? $rectificationIrpfRate : $defaultIrpfRate;
        $irpfAmount = round($invoice->subtotal_amount * ($irpfRate / 100), 2);
    @endphp

    <div class="totals">
        <div class="total-row">
            <span>Base Imponible</span>
            <span>{{ number_format($invoice->subtotal_amount, 2) }} €</span>
        </div>
        <div class="total-row">
            <span>IGIC ({{ number_format($taxRate, 2) }}%)</span>
            <span>{{ number_format($invoice->tax_amount, 2) }} €</span>
        </div>
        <div class="total-row">
            <span>IRPF ({{ number_format($irpfRate, 2) }}%)</span>
            <span>{{ number_format($irpfAmount, 2) }} €</span>
        </div>
        <div class="total-row final">
            <span>Total</span>
            <span>{{ number_format($invoice->total_amount, 2) }} €</span>
        </div>
    </div>

    <div class="footer">
        Gracias por confiar en nuestra plataforma. Para dudas, contacta con {{ $portalFiscalData->email }}.
    </div>
</body>

</html>
