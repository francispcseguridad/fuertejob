<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne; // Nuevo
use App\Models\CompanyCreditUsageLog;

/**
 * @property int $id
 * @property int $company_profile_id
 * @property int $bono_catalog_id
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property float $amount_paid
 * @property string $payment_gateway
 * @property string $transaction_id
 * @property string $payment_status // Ej: PENDIENTE, COMPLETADO, FALLIDO
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\CompanyProfile $companyProfile
 * @property \App\Models\BonoCatalog $bonoCatalog
 */
class BonoPurchase extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bono_purchases';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_profile_id',
        'bono_catalog_id',
        'purchase_date',
        'amount_paid',
        'payment_gateway',
        'transaction_id',
        'payment_status',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'company_profile_id' => 'integer',
        'bono_catalog_id' => 'integer',
        // 'purchase_date' => 'date', // Mantener como está o usar 'datetime' si guarda hora
        'amount_paid' => 'float',  // Asegurar que el monto se maneje como un número decimal
    ];

    // --- RELACIONES ELOQUENT ---

    /**
     * Define la relación: Una compra de bono pertenece a un Perfil de Empresa.
     */
    public function companyProfile(): BelongsTo
    {
        // Asumiendo que CompanyProfile::class existe
        return $this->belongsTo(CompanyProfile::class);
    }

    /**
     * Define la relación: Una compra de bono pertenece a un Bono del Catálogo.
     */
    public function bonoCatalog(): BelongsTo
    {
        // Asumiendo que BonoCatalog::class existe
        return $this->belongsTo(BonoCatalog::class);
    }

    /**
     * Define la relación polimórfica: Una compra tiene un único registro de acreditación en el Ledger.
     */
    public function ledgerEntry(): MorphOne
    {
        return $this->morphOne(CompanyCreditLedger::class, 'related');
    }

    public function usageLogs(): MorphMany
    {
        return $this->morphMany(CompanyCreditUsageLog::class, 'related');
    }
}
