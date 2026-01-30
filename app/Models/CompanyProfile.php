<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute; // Importación necesaria para Accessors

// Importación para la nueva relación
use App\Models\JobOffer;
use App\Models\Sector;
use App\Models\CompanyResourceBalance;
use App\Models\CompanyCreditUsageLog;

/**
 * @property int $id
 * @property int $user_id
 * @property string $company_name
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $logo_url
 * @property string|null $video_url
 * @property string|null $website_url
 * @property string|null $legal_name
 * @property string|null $vat_id
 * @property string|null $fiscal_address
 * @property string|null $contact
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\BonoPurchase> $purchases
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\CompanyCreditLedger> $creditLedger
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\JobOffer> $jobOffers // <<< NUEVO: Relación con Ofertas de Trabajo
 * @property int $current_credit_balance // ¡Cambiado a int!
 */
class CompanyProfile extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'description',
        'phone',
        'email',
        'logo_url',
        'video_url',
        'website_url',
        'legal_name',
        'vat_id',
        'fiscal_address',
        'contact',
        'contact_phone',
        'contact_email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    // --- RELACIONES ELOQUENT ---

    /**
     * Define la relación: Un perfil de empresa pertenece a un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación: Un perfil de empresa ha realizado múltiples compras de bonos.
     */
    public function purchases(): HasMany
    {
        // Utiliza la clave foránea 'company_profile_id' en la tabla 'bono_purchases'.
        return $this->hasMany(BonoPurchase::class, 'company_profile_id');
    }

    /**
     * Define la relación: Un perfil de empresa puede tener múltiples ofertas de trabajo.
     */
    public function jobOffers(): HasMany
    {
        // Asumiendo que JobOffer utiliza 'company_profile_id' como clave foránea.
        return $this->hasMany(JobOffer::class, 'company_profile_id');
    }

    /**
     * Relación muchos a muchos con Sectores.
     */
    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class, 'company_profile_sector')
            ->withTimestamps();
    }

    // --- GESTIÓN DE CRÉDITOS ---

    /**
     * Relación: Un perfil de empresa tiene múltiples movimientos en el libro de créditos (Ledger).
     */
    public function creditLedger(): HasMany
    {
        // Asumiendo que CompanyCreditLedger utiliza 'company_id' como clave foránea.
        return $this->hasMany(CompanyCreditLedger::class, 'company_id');
    }

    /**
     * Accessor para calcular el saldo de crédito actual.
     * Permite acceder al saldo como $companyProfile->current_credit_balance
     *
     * Se realiza un CAST (int) para asegurar que el valor devuelto sea un número entero,
     * eliminando cualquier decimal que pueda resultar de la función SUM().
     */
    protected function currentCreditBalance(): Attribute
    {
        return Attribute::make(
            // La clave aquí es (int) para asegurar que el resultado sea un entero.
            get: fn() => (int) $this->creditLedger()->sum('amount'),
        );
    }

    public function resourceBalance(): HasOne
    {
        // Relación 1 a 1 con el balance de recursos
        return $this->hasOne(CompanyResourceBalance::class, 'company_profile_id');
    }

    public function creditUsageLogs(): HasMany
    {
        return $this->hasMany(CompanyCreditUsageLog::class, 'company_profile_id');
    }
}
