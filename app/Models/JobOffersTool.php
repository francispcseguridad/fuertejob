<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modelo Pivot explícito para la relación N:M entre JobOffer y Tool.
 * Se utiliza para mapear la tabla 'job_offer_tool'.
 */
class JobOffersTool extends Pivot
{
    // Define la tabla que debe usar este modelo pivot
    protected $table = 'job_offer_tool';

    // Indica que los IDs no son autoincrementales, ya que la clave primaria es compuesta
    public $incrementing = false;

    // Define las columnas de la clave primaria compuesta (opcional, pero buena práctica)
    protected $primaryKey = ['job_offer_id', 'tool_id'];

    // Relación inversa a la Oferta de Trabajo
    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    // Relación inversa a la Herramienta (Maestra)
    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
}
