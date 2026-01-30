<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\UserSeatPack;
use App\Models\CvPurchasePack;
use App\Models\OffersPack;

class BonoCatalog extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'bono_catalogs';

    /**
     * Los atributos que son asignables masivamente (Mass Assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'credits_included',
        'offer_credits',
        'cv_views',
        'user_seats',
        'visibility_days',
        'duration_days',
        'is_active',
        'is_extra',
        'destacado',
        'credit_cost',
        'analytics_model_id',
    ];

    /**
     * Conversiones de tipos para los atributos del modelo.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float', // El precio debe ser un número decimal (float/double)
        'credits_included' => 'integer',
        'offer_credits' => 'integer',
        'cv_views' => 'integer',
        'user_seats' => 'integer',
        'visibility_days' => 'integer',
        'duration_days' => 'integer',
        'is_active' => 'boolean', // Campo booleano para indicar si está activo
        'is_extra' => 'boolean', // Campo booleano para indicar si es un extra
        'destacado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(BonoPurchase::class, 'bono_catalog_id');
    }

    // Packs de Asientos incluidos en este Bono
    public function seatPacks()
    {
        return $this->belongsToMany(UserSeatPack::class, 'bono_seat_packs')
            ->withPivot('quantity');
    }

    // Packs de CVs incluidos en este Bono
    public function cvPacks()
    {
        return $this->belongsToMany(CvPurchasePack::class, 'bono_cv_packs')
            ->withPivot('quantity');
    }

    /**
     * Ejemplo de cálculo: Obtener el total de CVs que otorga este bono
     */
    public function getTotalCvCountAttribute()
    {
        return $this->cvPacks->sum(function ($pack) {
            return $pack->cv_count * $pack->pivot->quantity;
        });
    }

    public function offersPacks()
    {
        return $this->belongsToMany(OffersPack::class, 'bono_offers_packs')
            ->withPivot('quantity');
    }

    public function analyticsFunctions(): BelongsToMany
    {
        return $this->belongsToMany(AnalyticsFunction::class, 'analytics_function_bono_catalog');
    }

    public function analyticsModel()
    {
        return $this->belongsTo(AnalyticsModel::class);
    }
}
