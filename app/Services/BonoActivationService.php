<?php

namespace App\Services;

use App\Models\BonoPurchase;
use App\Models\CompanyResourceBalance;
use Illuminate\Support\Facades\DB;

/**
 * Encapsula la lógica de activación/consumo de un bono:
 * toma los packs asociados y acredita asientos y vistas de CV a la empresa.
 */
class BonoActivationService
{
    /**
     * Procesa la activación de un bono y acredita recursos al balance de la empresa.
     */
    public function activate(BonoPurchase $purchase): void
    {
        $purchase->loadMissing('bonoCatalog', 'companyProfile');

        $bono = $purchase->bonoCatalog;
        $companyProfile = $purchase->companyProfile;

        if (!$bono || !$companyProfile) {
            return;
        }

        $offerCreditsToAdd = (int) ($bono->offer_credits ?? $bono->credits_included ?? 0);
        $cvToAdd = (int) ($bono->cv_views ?? 0);
        $seatToAdd = (int) ($bono->user_seats ?? 0);
        $visibilityDays = (int) ($bono->visibility_days ?? 0);

        if ($offerCreditsToAdd === 0 && $seatToAdd === 0 && $cvToAdd === 0 && $visibilityDays === 0) {
            return;
        }

        DB::transaction(function () use ($companyProfile, $seatToAdd, $cvToAdd, $offerCreditsToAdd, $visibilityDays) {
            $balance = CompanyResourceBalance::firstOrCreate(
                ['company_profile_id' => $companyProfile->id],
                ['available_cv_views' => 0, 'available_user_seats' => 0]
            );

            if ($seatToAdd !== 0) {
                $balance->available_user_seats += $seatToAdd;
                $balance->total_user_seats += $seatToAdd;
            }

            if ($cvToAdd !== 0) {
                $balance->available_cv_views += $cvToAdd;
                $balance->total_cv_views += $cvToAdd;
            }

            if ($offerCreditsToAdd !== 0) {
                $balance->available_offer_credits += $offerCreditsToAdd;
                $balance->total_offer_credits += $offerCreditsToAdd;
            }

            if ($visibilityDays !== 0) {
                $balance->offer_visibility_days = max((int) $balance->offer_visibility_days, $visibilityDays);
            }

            $balance->save();
        });
    }
}
