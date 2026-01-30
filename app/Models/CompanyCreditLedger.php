<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CompanyCreditLedger extends Model
{
    use HasFactory;

    protected $table = 'company_credit_ledger';

    protected $fillable = [
        'company_id',
        'amount',
        'description',
        'related_id',
        'related_type',
    ];

    /**
     * Define la relación polimórfica para el registro relacionado (ej: Compra de Bono).
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Define la relación con la empresa.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
