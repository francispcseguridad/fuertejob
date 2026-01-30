<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modelo Pivot explícito para la relación N:M entre JobOffer y Skill.
 * Se utiliza para mapear la tabla 'job_offer_skill'.
 */
class JobOffersSkill extends Pivot
{
    // Define la tabla que debe usar este modelo pivot
    protected $table = 'job_offer_skill';

    // Indica que los IDs no son autoincrementales, ya que la clave primaria es compuesta
    public $incrementing = false;

    // Define las columnas de la clave primaria compuesta (opcional, pero buena práctica)
    protected $primaryKey = ['job_offer_id', 'skill_id'];

    // Relación inversa a la Oferta de Trabajo
    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    // Relación inversa a la Habilidad (Maestra)
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
