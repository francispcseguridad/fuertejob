<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para registrar cada fase o interacción dentro del proceso de selección
 * para un candidato específico. Actúa como una tabla pivotante detallada.
 */
class SelectionProcessLog extends Model
{
    use HasFactory;

    /**
     * Define los campos que se pueden llenar masivamente.
     * @var array
     */
    protected $fillable = [
        'candidate_selection_id',
        'stage_order',
        'stage_name',
        'stage_date',
        'contact_type',
        'result',
        'interviewer_name',
        'interviewer_notes',
        'next_step',
    ];

    /**
     * Define las conversiones de tipos de atributos.
     * @var array
     */
    protected $casts = [
        'stage_date' => 'datetime',
        'stage_order' => 'integer',
    ];

    /**
     * Relación con la Selección del Candidato.
     * Un registro de log pertenece a una única selección de candidato.
     */
    public function candidateSelection()
    {
        return $this->belongsTo(CandidateSelection::class);
    }
}
