<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'invoices';

    // Campos que pueden ser llenados masivamente
    protected $fillable = [
        'bono_purchase_id',
        'invoice_number',
        'issue_date',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'company_fiscal_data',
        'portal_fiscal_data',
        'pdf_path',
        'rectifies_invoice_id',
        'is_rectificativa',
    ];

    // Casting de atributos a tipos nativos para facilitar el manejo
    protected $casts = [
        // La fecha de emisión se castea a un objeto Carbon
        'issue_date' => 'date',
        // Los montos se castean a float para operaciones numéricas seguras
        'subtotal_amount' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
        'is_rectificativa' => 'boolean',
    ];

    /**
     * Define la relación: Una factura pertenece a una compra de bono (bono_purchase).
     * Nota: Asumo la existencia de un modelo BonoPurchase.
     */
    public function bonoPurchase()
    {
        // Ajusta el nombre del modelo si no es BonoPurchase
        return $this->belongsTo(BonoPurchase::class, 'bono_purchase_id');
    }

    public function rectifiesInvoice()
    {
        return $this->belongsTo(self::class, 'rectifies_invoice_id');
    }

    public function rectificativas()
    {
        return $this->hasMany(self::class, 'rectifies_invoice_id');
    }
}
