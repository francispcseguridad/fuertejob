<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalSetting extends Model
{
    use HasFactory;

    protected $table = 'portal_settings';

    /**
     * Los atributos que se pueden asignar masivamente.
     * Los campos 'logo_url' y otros datos se almacenan aquí.
     * @var array
     */
    protected $fillable = [
        'site_name',
        'legal_name',
        'vat_id',
        'fiscal_address',
        'contact_email',
        'logo_url',
        'default_tax_rate',
        'default_irpf',
        'invoice_prefix',
        'imagen_academias',
        'imagen_inmobiliarias',
    ];

    /**
     * Obtiene la única instancia de configuración. Si no existe, crea una nueva fila.
     * @return static
     */
    public static function getSettings()
    {
        return static::firstOrCreate([]);
    }
}
