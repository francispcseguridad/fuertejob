<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceManagementController extends Controller
{
    /**
     * Listado de facturas emitidas.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['bonoPurchase.companyProfile.user', 'rectifiesInvoice', 'bonoPurchase.bonoCatalog'])
            ->orderByDesc('issue_date');

        // Filtrado por fechas
        if ($request->filled('start_date')) {
            $query->whereDate('issue_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('issue_date', '<=', $request->end_date);
        }

        // Filtrado por empresa (like)
        if ($request->filled('company')) {
            $query->whereHas('bonoPurchase.companyProfile', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->company . '%');
            })->orWhereHas('bonoPurchase.companyProfile.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->company . '%');
            });
        }

        // Filtrado por concepto
        if ($request->filled('concept')) {
            $concept = $request->concept;
            if ($concept === 'rectificativa') {
                $query->where('is_rectificativa', true);
            } elseif ($concept === 'compra') {
                $query->where('is_rectificativa', false)->whereHas('bonoPurchase.bonoCatalog');
            } elseif ($concept === 'servicios') {
                $query->where('is_rectificativa', false)->whereDoesntHave('bonoPurchase.bonoCatalog');
            }
        }

        // Si es exportación
        if ($request->has('export')) {
            $invoices = $query->get();
            if ($request->export === 'csv') {
                return $this->exportToCsv($invoices);
            }
            if ($request->export === 'pdf') {
                return view('admin.invoices.export_pdf', compact('invoices'));
            }
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    private function exportToCsv($invoices)
    {
        $filename = "facturas_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            // Bom para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Factura', 'Fecha', 'Empresa', 'Email', 'Concepto', 'Total']);

            foreach ($invoices as $invoice) {
                $companyProfile = optional(optional($invoice->bonoPurchase)->companyProfile);
                $companyUser = optional($companyProfile->user);

                $concept = '';
                if ($invoice->is_rectificativa) {
                    $concept = 'Rectificativa de #' . ($invoice->rectifiesInvoice->invoice_number ?? '');
                } elseif (optional($invoice->bonoPurchase)->bonoCatalog) {
                    $concept = 'Compra Bono: ' . $invoice->bonoPurchase->bonoCatalog->name;
                } else {
                    $concept = 'Servicios';
                }

                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->issue_date?->format('d/m/Y'),
                    $companyProfile->company_name ?? ($companyUser->name ?? 'Sin datos'),
                    $companyUser->email ?? '',
                    $concept,
                    number_format($invoice->total_amount, 2, ',', '') . ' €'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Vista imprimible/PDF de una factura específica.
     */
    public function showPdf(Invoice $invoice)
    {
        $companyFiscalData = json_decode($invoice->company_fiscal_data);
        $portalFiscalData = json_decode($invoice->portal_fiscal_data);

        return view('admin.invoices.pdf', compact('invoice', 'companyFiscalData', 'portalFiscalData'));
    }

    public function rectify(Invoice $invoice)
    {
        if ($invoice->is_rectificativa) {
            return redirect()
                ->route('admin.facturas.index')
                ->with('error', 'No se puede rectificar una factura rectificativa.');
        }

        $sequence = Invoice::where('rectifies_invoice_id', $invoice->id)->count() + 1;
        $rectNumber = 'RECT-' . $invoice->invoice_number . '-' . $sequence;

        Invoice::create([
            'bono_purchase_id' => $invoice->bono_purchase_id,
            'invoice_number' => $rectNumber,
            'issue_date' => Carbon::now(),
            'subtotal_amount' => -1 * (float) $invoice->subtotal_amount,
            'tax_amount' => -1 * (float) $invoice->tax_amount,
            'total_amount' => -1 * (float) $invoice->total_amount,
            'company_fiscal_data' => $invoice->company_fiscal_data,
            'portal_fiscal_data' => $invoice->portal_fiscal_data,
            'pdf_path' => null,
            'rectifies_invoice_id' => $invoice->id,
            'is_rectificativa' => true,
        ]);

        return redirect()
            ->route('admin.facturas.index')
            ->with('success', 'Factura rectificativa creada correctamente.');
    }
}
