<?php

namespace App\Services;

use App\Models\BonoCatalog;
use App\Models\BonoPurchase;
use App\Models\CompanyProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Servicio ligero que simula el flujo de compra y callback de pago.
 * En producción aquí se integraría la pasarela real (Stripe/PayPal).
 */
class BonoPaymentService
{
    public const STATUS_PENDING = 'PENDIENTE';
    public const STATUS_COMPLETED = 'COMPLETADO';
    public const STATUS_FAILED = 'FALLIDO';

    public function __construct(
        protected BonoActivationService $activationService
    ) {}

    /**
     * Crea una compra en estado PENDIENTE y devuelve una URL simulada de pago.
     */
    public function initiatePurchase(CompanyProfile $companyProfile, BonoCatalog $bono): array
    {
        $purchase = BonoPurchase::create([
            'company_profile_id' => $companyProfile->id,
            'bono_catalog_id' => $bono->id,
            'purchase_date' => Carbon::now(),
            'amount_paid' => $bono->price,
            'payment_gateway' => 'SimulatedPayPal',
            'transaction_id' => 'txn_' . Str::random(10) . '_' . time(),
            'payment_status' => self::STATUS_PENDING,
        ]);

        return [
            'purchase' => $purchase,
            'redirect_url' => route('api.bonos.callback', ['purchase_id' => $purchase->id]),
        ];
    }

    /**
     * Maneja el callback de pago y, si es exitoso, activa el bono.
     */
    public function handlePaymentCallback(int $purchaseId, bool $paymentSuccess, ?string $transactionId = null): ?BonoPurchase
    {
        return DB::transaction(function () use ($purchaseId, $paymentSuccess, $transactionId) {
            $purchase = BonoPurchase::lockForUpdate()->find($purchaseId);

            if (!$purchase || $purchase->payment_status === self::STATUS_COMPLETED) {
                return null;
            }

            $purchase->transaction_id = $transactionId ?? $purchase->transaction_id;
            $purchase->payment_status = $paymentSuccess ? self::STATUS_COMPLETED : self::STATUS_FAILED;
            $purchase->purchase_date = $purchase->purchase_date ?? Carbon::now();
            $purchase->save();

            if ($paymentSuccess) {
                $this->activationService->activate($purchase);
            }

            return $purchase;
        });
    }
}
