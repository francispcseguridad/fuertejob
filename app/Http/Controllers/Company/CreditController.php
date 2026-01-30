<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyCreditLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * Calcula y devuelve el saldo actual y el historial de la empresa autenticada.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->id;

        // 1. Obtener el saldo actual sumando todos los montos en el ledger.
        $currentBalance = CompanyCreditLedger::where('company_id', $companyId)->sum('amount');

        // 2. Obtener el historial de transacciones.
        $transactions = CompanyCreditLedger::where('company_id', $companyId)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'balance' => (float) $currentBalance, // Convertir a float para JSON
            'transactions' => $transactions,
        ]);
    }

    /**
     * Simula una recarga de crédito (añade una transacción positiva al ledger).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topUp(Request $request)
    {
        // Simulación: Recarga de crédito manual
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $company = $request->user();
        $amount = $request->amount;

        try {
            DB::beginTransaction();

            // 1. Registrar la transacción positiva (Crédito / Ganancia)
            CompanyCreditLedger::create([
                'company_id' => $company->id,
                'amount' => $amount, // Positivo
                'description' => "Recarga de Crédito por $amount.00 (Simulación)",
                // 'related' => ... aquí podrías relacionarlo con una tabla de pagos
            ]);

            // 2. Recalcular el saldo después de la recarga
            $newBalance = CompanyCreditLedger::where('company_id', $company->id)->sum('amount');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Crédito de $amount añadido correctamente.",
                'new_balance' => (float) $newBalance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la recarga de crédito.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
