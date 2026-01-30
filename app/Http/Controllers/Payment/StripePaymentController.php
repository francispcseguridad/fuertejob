<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Crea una sesión de checkout de Stripe para pagos rápidos.
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'success_url' => ['required', 'url'],
            'cancel_url' => ['required', 'url'],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['string'],
        ]);

        $currency = strtoupper($data['currency'] ?? 'EUR');
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Pago en FuerteJob',
                        ],
                        'unit_amount' => (int) round($data['amount']),
                    ],
                    'quantity' => 1,
                ],
            ],
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
            'metadata' => $data['metadata'] ?? [],
        ]);

        return response()->json($session);
    }

    /**
     * Crea un PaymentIntent directo, útil para integraciones custom.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_method_types' => ['array'],
            'payment_method_types.*' => ['string'],
            'metadata' => ['nullable', 'array'],
            'metadata.*' => ['string'],
        ]);

        $currency = strtoupper($data['currency'] ?? 'EUR');
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => (int) round($data['amount']),
            'currency' => $currency,
            'payment_method_types' => $data['payment_method_types'] ?? ['card'],
            'metadata' => $data['metadata'] ?? [],
        ]);

        return response()->json($paymentIntent);
    }

    /**
     * Endpoint para recibir los eventos del webhook de Stripe.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $exception) {
            Log::error('Stripe webhook signature mismatch: ' . $exception->getMessage());
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                Log::info('Checkout completo', ['session' => $event->data->object]);
                break;
            case 'payment_intent.succeeded':
                Log::info('PaymentIntent succeeded', ['intent' => $event->data->object]);
                break;
            case 'payment_intent.payment_failed':
                Log::warning('PaymentIntent failed', ['intent' => $event->data->object]);
                break;
            default:
                Log::debug('Stripe webhook recibido', ['type' => $event->type]);
        }

        return response()->json(['received' => true]);
    }
}
