<?php

namespace App\Http\Controllers\Company; // 1. Namespace actualizado

use App\Http\Controllers\Controller; // Mantenemos la herencia del controlador base
use App\Models\BonoCatalog;         // El catálogo de bonos disponibles
use App\Models\BonoPurchase;        // El registro de la compra real
use App\Models\CompanyCreditLedger; // El registro del movimiento de crédito
use App\Models\CompanyProfile;
use App\Models\CompanyResourceBalance;
use App\Models\CompanyCreditUsageLog;
use App\Models\Invoice;             // El modelo de factura
use App\Models\PortalSetting;
use App\Models\User;
use App\Http\Controllers\MailsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BonoController extends Controller
{
    /**
     * Muestra el catálogo de bonos disponibles para la empresa.
     * Corresponde a la ruta GET /empresa/bonos/catalogo
     */
    public function index()
    {
        $companyProfile = Auth::user()?->companyProfile;
        $resourceBalance = $companyProfile ? $this->resolveResourceBalance($companyProfile) : null;
        $companyState = [
            'credit_balance' => (int) ($companyProfile?->current_credit_balance ?? 0),
            'available_offer_credits' => (int) ($resourceBalance?->available_offer_credits ?? 0),
            'available_cv_views' => (int) ($resourceBalance?->available_cv_views ?? 0),
            'available_user_seats' => (int) ($resourceBalance?->available_user_seats ?? 0),
        ];

        try {
            $bonos = BonoCatalog::all();
        } catch (\Exception $e) {
            $bonos = collect([
                (object)['id' => 1, 'name' => 'Bono Bronce (Simulado)', 'description' => '50 créditos para empezar.', 'price' => 50.00, 'credits_included' => 50],
                (object)['id' => 2, 'name' => 'Bono Plata (Simulado)', 'description' => '120 créditos con descuento.', 'price' => 100.00, 'credits_included' => 120],
                (object)['id' => 3, 'name' => 'Bono Oro (Simulado)', 'description' => '250 créditos para grandes empresas.', 'price' => 200.00, 'credits_included' => 250],
            ]);
        }

        return view('company.bono_catalog', compact('bonos', 'companyState'));
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
            'privacy' => ['accepted'],
        ]);

        $contactName = trim($data['name'] . ' ' . ($data['surname'] ?? ''));
        $contactName = $contactName ?: $data['email'];
        $details = collect([
            ['Nombre', $contactName],
            ['Empresa', $data['company'] ?? '—'],
            ['Email', $data['email']],
            ['Teléfono', $data['phone'] ?? '—'],
        ])->map(function ($item) {
            return "<p><strong>{$item[0]}:</strong> {$item[1]}</p>";
        })->implode('');

        $body = $details . "<p><strong>Consulta:</strong><br>" . nl2br(e($data['message'])) . "</p>";
        $subject = 'Consulta catálogo de bonos';
        $recipient = 'info@fuertejob.com';

        try {
            MailsController::enviaremail(
                $recipient,
                $contactName,
                $data['email'],
                $subject,
                $body
            );

            $copyBody = '<p>Esto es una copia del email que usted ha generado.</p>' . $body;
            MailsController::enviaremail(
                $data['email'],
                $contactName,
                $recipient,
                'Copia: ' . $subject,
                $copyBody
            );

            return response()->json([
                'message' => 'Correo enviado correctamente.',
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error("BonoController::contact error sending mail: " . $e->getMessage(), [
                'payload' => $data,
            ]);

            return response()->json([
                'message' => 'No se pudo enviar el correo. Intenta de nuevo más tarde.',
            ], 500);
        }
    }

    /**
     * Procesa la compra de un bono y registra la acreditación de créditos.
     * Esto simula el callback exitoso de una pasarela de pago (ej. Stripe).
     * Corresponde a la ruta POST /empresa/bonos/{bono}/purchase
     * * @param Request $request
     * @param BonoCatalog $bono El modelo de Bono seleccionado para la compra.
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request, BonoCatalog $bono)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autorizado. Debe iniciar sesión.'], 401);
        }

        $companyProfile = Auth::user()->companyProfile;
        if (!$companyProfile) {
            return response()->json(['message' => 'Usuario no asociado a un perfil de compañía.'], 403);
        }

        if ($bono->is_extra) {
            return $this->processExtraPurchase($companyProfile, $bono);
        }

        return $this->processStandardPurchase($companyProfile, $bono);
    }

    private function processStandardPurchase(CompanyProfile $companyProfile, BonoCatalog $bono)
    {
        $transactionId = 'txn_' . \Str::random(10) . '_' . time();
        $paymentGateway = 'StripeSimulated';

        try {
            DB::beginTransaction();

            $purchase = BonoPurchase::create([
                'company_profile_id' => $companyProfile->id,
                'bono_catalog_id' => $bono->id,
                'purchase_date' => Carbon::now(),
                'amount_paid' => $bono->price,
                'payment_gateway' => $paymentGateway,
                'transaction_id' => $transactionId,
                'payment_status' => 'COMPLETADO',
            ]);

            CompanyCreditLedger::create([
                'company_id' => $companyProfile->id,
                'amount' => $bono->credits_included,
                'description' => "Acreditación por compra del bono: {$bono->name}.",
                'related_type' => BonoPurchase::class,
                'related_id' => $purchase->id,
            ]);

            $this->activateBonoResources($purchase);

            $settings = PortalSetting::getSettings();
            $taxRatePercent = (float) ($settings->default_tax_rate ?? 0);
            $irpfRatePercent = (float) ($settings->default_irpf ?? 0);
            $taxRate = $taxRatePercent / 100;
            $irpfRate = $irpfRatePercent / 100;
            $totalAmount = (float) $bono->price;
            $denominator = 1 + $taxRate - $irpfRate;
            if ($denominator <= 0) {
                $denominator = 1;
            }
            $subtotalAmount = $totalAmount / $denominator;
            $taxAmount = $subtotalAmount * $taxRate;
            $irpfAmount = $subtotalAmount * $irpfRate;

            $companyFiscalData = [
                'legal_name' => $companyProfile->legal_name ?? $companyProfile->company_name,
                'vat_id' => $companyProfile->vat_id ?? 'N/A',
                'fiscal_address' => $companyProfile->fiscal_address ?? 'N/A',
                'email' => $companyProfile->email ?? Auth::user()->email,
            ];

            $portalFiscalData = [
                'legal_name' => $settings->legal_name ?? 'Fuertejob S.L.',
                'vat_id' => $settings->vat_id ?? 'B12345678',
                'fiscal_address' => $settings->fiscal_address ?? 'Calle Principal 123, Fuerteventura, España',
                'email' => $settings->contact_email ?? 'facturacion@fuertejob.com',
                'default_tax_rate' => $taxRatePercent,
                'default_irpf' => $irpfRatePercent,
                'irpf_amount' => round($irpfAmount, 2),
            ];

            Invoice::create([
                'bono_purchase_id' => $purchase->id,
                'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($purchase->id, 6, '0', STR_PAD_LEFT),
                'issue_date' => Carbon::now(),
                'subtotal_amount' => round($subtotalAmount, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($totalAmount, 2),
                'company_fiscal_data' => json_encode($companyFiscalData),
                'portal_fiscal_data' => json_encode($portalFiscalData),
                'pdf_path' => null,
            ]);

            DB::commit();
            $this->notifyAdminsOfBonoPurchase($companyProfile, $bono, $totalAmount);

            return response()->json([
                'message' => 'Compra exitosa y ' . $bono->credits_included . ' créditos añadidos a tu cuenta.',
                'credits_added' => $bono->credits_included,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Fallo al registrar doble transacción de bono: " . $e->getMessage(), [
                'profile_id' => $companyProfile->id,
                'bono_id' => $bono->id,
            ]);

            return response()->json(['message' => 'Error crítico al registrar la transacción. Contacta soporte.'], 500);
        }
    }

    private function processExtraPurchase(CompanyProfile $companyProfile, BonoCatalog $bono)
    {
        $balance = $this->resolveResourceBalance($companyProfile);
        $creditCost = max(0, (int) ($bono->credit_cost ?? 0));

        if ($creditCost > 0 && $balance->available_offer_credits < $creditCost) {
            return response()->json([
                'message' => 'Saldo insuficiente para comprar este extra. Necesitas ' . $creditCost . ' créditos disponibles.'
            ], 402);
        }

        $oldCreditBalance = (int) ($companyProfile->current_credit_balance ?? 0);
        $transactionId = 'extra_' . \Str::random(8) . '_' . time();

        try {
            DB::beginTransaction();

            $purchase = BonoPurchase::create([
                'company_profile_id' => $companyProfile->id,
                'bono_catalog_id' => $bono->id,
                'purchase_date' => Carbon::now(),
                'amount_paid' => 0,
                'payment_gateway' => 'InternalCredit',
                'transaction_id' => $transactionId,
                'payment_status' => 'COMPLETADO',
            ]);

            if ($creditCost > 0) {
                $balance->available_offer_credits = max(0, (int) $balance->available_offer_credits - $creditCost);
                $balance->used_offer_credits += $creditCost;
                $balance->save();

                CompanyCreditLedger::create([
                    'company_id' => $companyProfile->id,
                    'amount' => -$creditCost,
                    'description' => "Cargo por extra: {$bono->name}",
                    'related_type' => BonoPurchase::class,
                    'related_id' => $purchase->id,
                ]);

                CompanyCreditUsageLog::recordUsage(
                    $companyProfile->id,
                    'bono_extra',
                    $creditCost,
                    [
                        'related_type' => BonoPurchase::class,
                        'related_id' => $purchase->id,
                        'description' => "Créditos usados para el extra: {$bono->name}",
                        'metadata' => ['bono_id' => $bono->id],
                    ]
                );
            }

            $this->activateBonoResources($purchase, $balance);
            DB::commit();

            $newCreditBalance = max(0, $oldCreditBalance - $creditCost);
            $this->notifyAdminsOfBonoPurchase($companyProfile, $bono, 0, $creditCost);

            return response()->json([
                'message' => "Extra '{$bono->name}' activado. Se descontaron {$creditCost} crédito(s).",
                'available_offer_credits' => $balance->available_offer_credits,
                'credit_balance' => $newCreditBalance,
                'credits_used' => $creditCost,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al aplicar extra de bono: " . $e->getMessage(), [
                'profile_id' => $companyProfile->id,
                'bono_id' => $bono->id,
            ]);
            return response()->json(['message' => 'Error al aplicar el extra. Intenta nuevamente.'], 500);
        }
    }

    private function notifyAdminsOfBonoPurchase($companyProfile, $bono, $amountPaid, ?int $creditsUsed = null): void
    {
        $admins = User::where('rol', 'admin')->get(['name', 'email']);
        if ($admins->isEmpty()) {
            \Log::warning('No hay administradores para notificar compra de bono.', [
                'company_profile_id' => $companyProfile->id ?? null,
                'bono_id' => $bono->id ?? null,
            ]);
            return;
        }

        $companyName = $companyProfile->company_name
            ?? $companyProfile->legal_name
            ?? 'Empresa';
        if ($creditsUsed !== null && $creditsUsed > 0) {
            $amountFormatted = number_format($creditsUsed, 0, ',', '.');
            $subject = 'Extra de bono consumido - ' . $companyName;
            $message = "La empresa <strong>{$companyName}</strong> acaba de canjear un extra por <strong>{$amountFormatted} créditos</strong>.";
        } else {
            $amountFormatted = number_format((float) $amountPaid, 2, ',', '.');
            $subject = 'Compra de bono - ' . $companyName;
            $message = "La empresa <strong>{$companyName}</strong> acaba de comprar un bono por valor de <strong>{$amountFormatted} €</strong>.";
        }
        $senderEmail = config('mail.from.address', 'no-reply@fuertejob.com');
        $senderName = config('mail.from.name', 'FuerteJob');

        foreach ($admins as $admin) {
            MailsController::enviaremail(
                $admin->email,
                $admin->name ?? $admin->email,
                $senderEmail,
                $subject,
                $message
            );
        }
    }

    private function activateBonoResources(BonoPurchase $purchase, ?CompanyResourceBalance $balance = null): void
    {
        $purchase->loadMissing('bonoCatalog.seatPacks', 'bonoCatalog.cvPacks', 'companyProfile');

        $bono = $purchase->bonoCatalog;
        $companyProfile = $purchase->companyProfile;

        if (!$bono || !$companyProfile) {
            return;
        }

        if (!$balance) {
            $balance = $this->resolveResourceBalance($companyProfile);
        }

        $seatToAdd = ($bono->user_seats ?? 0);
        foreach ($bono->seatPacks as $pack) {
            $seatToAdd += ($pack->seat_count ?? 0) * ($pack->pivot->quantity ?? 1);
        }

        $cvToAdd = ($bono->cv_views ?? 0);
        foreach ($bono->cvPacks as $pack) {
            $cvToAdd += ($pack->cv_count ?? 0) * ($pack->pivot->quantity ?? 1);
        }

        $offerCreditsToAdd = $bono->is_extra ? 0 : (int) ($bono->offer_credits ?? 0);
        $visibilityDays = (int) ($bono->visibility_days ?? 0);

        if ($seatToAdd === 0 && $cvToAdd === 0 && $offerCreditsToAdd === 0) {
            return;
        }

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

        if ($visibilityDays > $balance->offer_visibility_days) {
            $balance->offer_visibility_days = $visibilityDays;
        }

        $balance->save();
    }

    private function resolveResourceBalance(CompanyProfile $companyProfile): CompanyResourceBalance
    {
        return CompanyResourceBalance::firstOrCreate(
            ['company_profile_id' => $companyProfile->id],
            [
                'available_cv_views' => 0,
                'total_cv_views' => 0,
                'used_cv_views' => 0,
                'available_user_seats' => 0,
                'total_user_seats' => 0,
                'used_user_seats' => 0,
                'total_offer_credits' => 0,
                'used_offer_credits' => 0,
                'available_offer_credits' => 0,
                'offer_visibility_days' => 0,
            ]
        );
    }
}
