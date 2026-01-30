<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//Experiencia laboral

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_profile_id',
        'job_title',
        'company_name',
        'start_date',
        'end_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * IMPORTANTE: Se eliminó la propiedad $casts para 'start_date' y 'end_date'.
     * Esto soluciona el error de Carbon\Exceptions\InvalidFormatException
     * al intentar guardar un valor 'null' en estas columnas.
     */

    // --- Relaciones ---

    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }
}
