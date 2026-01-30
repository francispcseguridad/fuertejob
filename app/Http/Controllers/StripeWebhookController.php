<?php

namespace App\Http\Controllers;

// use App\Models\BonoPurchase; // Dejamos comentados los usos para indicar que la lógica no corre
// use App\Models\CompanyCreditLedger;
// use App\Models\BonoCatalog;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Stripe\Stripe;
// use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Maneja los eventos de Webhook de Stripe.
     * COMENTADO: Este endpoint está bypassado en modo de simulación.
     */
    public function handleWebhook(Request $request)
    {
        \Log::info("Webhook recibido, pero lógica de negocio deshabilitada por simulación.");

        /*
        // Configuración de Stripe (usa la clave secreta)
        Stripe::setApiKey(config('services.stripe.secret'));
        
        // Clave secreta del Webhook (debe coincidir con la configurada en Stripe)
        $endpointSecret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // 1. Verificar la firma del Webhook
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 2. Procesar el evento
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            default:
                // Ignorar otros eventos
        }
        */

        // Devolvemos 200 para no causar reintentos en el webhook de Stripe si está configurado.
        return response()->json(['status' => 'success (Simulation mode)']);
    }

    /*
    protected function handleCheckoutSessionCompleted($session)
    {
        // Lógica de acreditación de créditos real (AHORA EN StripeController@createCheckoutSession)
    }
    */
}
