<?php

namespace App\Http\Controllers;

use App\Models\BonoCatalog;
use App\Models\BonoPurchase;
use App\Models\CompanyCreditLedger; // Necesario para la acreditación
use App\Services\BonoActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// use Stripe\Checkout\Session; // Comentamos la librería real
// use Stripe\Stripe; // Comentamos la librería real

class StripeController extends Controller
{
    /**
     * Constructor para configurar la clave API de Stripe.
     * COMENTADO: Deshabilitamos la conexión real a la API de Stripe en el constructor.
     */
    // public function __construct()
    // {
    //     Stripe::setApiKey(config('services.stripe.secret'));
    // }

    /**
     * SIMULACIÓN: Crea una compra PENDIENTE, ejecuta la lógica de acreditación
     * inmediatamente (simulando el Webhook), y redirige a éxito.
     *
     * @param BonoCatalog $bono El modelo de Bono seleccionado.
     */
    public function createCheckoutSession(BonoCatalog $bono)
    {
        $user = Auth::user();
        $companyProfile = $user->companyProfile;

        if (!$companyProfile) {
            return back()->with('error', 'No se encontró un perfil de compañía asociado.');
        }

        // --- SIMULACIÓN DE DATOS DE PAGO ---
        $transactionId = 'sim_txn_' . \Str::random(10) . '_' . time();
        $paymentGateway = 'SimulatedStripe';
        // ------------------------------------

        try {
            DB::beginTransaction();

            // 1. Crear un registro PENDIENTE en BonoPurchase (Paso necesario antes del pago)
            $purchase = BonoPurchase::create([
                'company_profile_id' => $companyProfile->id,
                'bono_catalog_id' => $bono->id,
                'purchase_date' => Carbon::now(),
                'amount_paid' => $bono->price,
                'payment_gateway' => $paymentGateway,
                'transaction_id' => $transactionId, // Usamos el ID de simulación
                'payment_status' => 'PENDIENTE',
            ]);

            // COMENTADO: Aquí iría la llamada a Stripe::create para obtener $session
            /*
            $session = Session::create([
                // ... configuración real de Stripe
            ]);
            $purchase->transaction_id = $session->id;
            $purchase->save();
            return redirect()->away($session->url);
            */

            // --- LÓGICA DE SIMULACIÓN DE WEBHOOK (Inicio) ---
            // 2. Simular que el pago fue exitoso (equivalente a recibir el Webhook)

            // A. Actualizar el estado de la Compra a COMPLETADO
            $purchase->update([
                'payment_status' => 'COMPLETADO',
            ]);

            // B. Registrar la Acreditación en el Ledger
            $offerCredits = (int) ($bono->offer_credits ?? $bono->credits_included ?? 0);
            if ($offerCredits > 0) {
                CompanyCreditLedger::create([
                    'company_id' => $purchase->company_profile_id,
                    'amount' => $offerCredits,
                    'description' => "Acreditación (SIMULADA) por compra del bono: {$bono->name}.",
                    'related_type' => BonoPurchase::class,
                    'related_id' => $purchase->id,
                ]);
            }

            app(BonoActivationService::class)->activate($purchase);
            // --- LÓGICA DE SIMULACIÓN DE WEBHOOK (Fin) ---

            DB::commit();

            // 3. Redirigir al éxito localmente. Pasamos el ID del bono para mostrar detalles si es necesario.
            return redirect()->route('empresa.bonos.success', ['bono_id' => $bono->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al simular compra de Stripe: " . $e->getMessage(), ['profile_id' => $companyProfile->id]);

            // Si falla, el registro PENDIENTE (si se creó) será revertido.
            return back()->with('error', 'Error al procesar la compra simulada. Inténtalo de nuevo.');
        }
    }

    /**
     * Maneja la redirección de éxito (Muestra mensaje).
     */
    public function success(Request $request)
    {
        // En simulación, el ID del bono viene de la redirección.
        $message = '¡Compra simulada exitosa! Tus créditos han sido añadidos inmediatamente.';
        return view('company.bonos.success')->with('success', $message);
    }

    /**
     * Maneja la redirección de cancelación (Muestra mensaje).
     */
    public function cancel()
    {
        return view('company.bonos.cancel')->with('warning', 'El proceso de pago fue cancelado o falló la simulación.');
    }
}
