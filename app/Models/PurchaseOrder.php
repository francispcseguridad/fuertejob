<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//Órdenes de Compra

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_profile_id',
        'package_id',
        'amount',
        'tax_rate',
        'status', // Ej: 'pendiente', 'pagado', 'fallido'
        'payment_intent_id', // ID de la transacción de Stripe/PayPal
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    // Relación: Una PurchaseOrder pertenece a una CompanyProfile
    public function companyProfile()
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    // Relación: Una PurchaseOrder pertenece a un Package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Relación: Una PurchaseOrder tiene una Invoice
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
