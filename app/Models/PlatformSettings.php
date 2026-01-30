<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//Configuración de FuerteJob

class PlatformSettings extends Model
{
    use HasFactory;

    // Renombramos la tabla por convención de Laravel si no se llama 'platform_settings'
    protected $table = 'portal_settings';

    protected $fillable = [
        'site_name',
        'legal_name',
        'vat_id',
        'fiscal_address',
        'contact_email',
        'logo_url',
        'default_tax_rate',
        'invoice_prefix',
    ];

    // Nota: Como esta tabla solo debe tener una fila,
    // puedes añadir métodos estáticos para acceder a los datos fácilmente:
    public static function getFiscalData()
    {
        return static::first();
    }
}
