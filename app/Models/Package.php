<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//Paquetes de Publicación

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'is_premium_feature', // Si incluye destacada o solo estándar
    ];

    // Relación: Un Package puede tener muchas PurchaseOrders
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
