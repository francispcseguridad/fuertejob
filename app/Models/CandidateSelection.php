<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SelectionProcessLog;

/**
 * Modelo para gestionar la selección inicial de un candidato para una oferta de empleo.
 * Es el registro principal del seguimiento de un candidato.
 */
class CandidateSelection extends Model
{
    use HasFactory;

    /**
     * Define los campos que se pueden llenar masivamente.
     * @var array
     */
    protected $fillable = [
        'job_offer_id',
        'worker_profile_id',
        'company_profile_id',
        'selection_date',
        'current_status',
        'priority',
        'initial_assessment',
        'expected_salary',
        'time_to_hire_days',
    ];

    /**
     * Define las conversiones de tipos de atributos.
     * @var array
     */
    protected $casts = [
        'selection_date' => 'date',
        'expected_salary' => 'decimal:2',
    ];

    /**
     * Relación con la Oferta de Empleo.
     * Una selección pertenece a una oferta.
     */
    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    /**
     * Relación con el Perfil del Trabajador (Candidato).
     * Una selección pertenece a un perfil de trabajador.
     */
    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }

    /**
     * Relación con el Perfil de la Empresa.
     * Una selección pertenece a una empresa.
     */
    public function companyProfile()
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    /**
     * Relación con el Historial de Proceso de Selección (Log).
     * Una selección tiene múltiples entradas de log (fases de entrevista, resultados, etc.).
     */
    public function processLog()
    {
        return $this->hasMany(SelectionProcessLog::class);
    }
}
