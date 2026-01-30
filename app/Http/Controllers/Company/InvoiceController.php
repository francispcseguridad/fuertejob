<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * Muestra el listado de facturas de la empresa.
     */
    public function index()
    {
        $user = Auth::user();
        $companyProfile = $user->companyProfile;

        if (!$companyProfile) {
            return redirect()->route('empresa.profile.index')
                ->with('error', 'Debes completar tu perfil de empresa primero.');
        }

        // Obtener facturas a través de la relación con BonoPurchase
        $invoices = Invoice::whereHas('bonoPurchase', function ($query) use ($companyProfile) {
            $query->where('company_profile_id', $companyProfile->id);
        })
            ->orderBy('issue_date', 'desc')
            ->paginate(10);

        return view('company.invoices.index', compact('invoices'));
    }

    /**
     * Genera y muestra la factura en formato PDF (o vista imprimible).
     */
    public function downloadPdf(Invoice $invoice)
    {
        $user = Auth::user();
        $companyProfile = $user->companyProfile;

        // Verificación de seguridad: ¿La factura pertenece a esta empresa?
        if ($invoice->bonoPurchase->company_profile_id !== $companyProfile->id) {
            abort(403, 'No tienes permiso para ver esta factura.');
        }

        // Decodificar datos JSON para la vista
        $companyFiscalData = json_decode($invoice->company_fiscal_data);
        $portalFiscalData = json_decode($invoice->portal_fiscal_data);

        // En un entorno con barryvdh/laravel-dompdf instalado:
        // $pdf = PDF::loadView('company.invoices.pdf', compact('invoice', 'companyFiscalData', 'portalFiscalData'));
        // return $pdf->download($invoice->invoice_number . '.pdf');

        // Por ahora, retornamos la vista 'printable'
        return view('company.invoices.pdf', compact('invoice', 'companyFiscalData', 'portalFiscalData'));
    }
}
