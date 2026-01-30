<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//Candidatura

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_offer_id',
        'worker_profile_id',
        'status', // Ej: 'pendiente', 'revisado', 'aceptado', 'rechazado'
        'message',
    ];

    // Relación: Una Application pertenece a una JobOffer
    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    // Relación: Una Application pertenece a un WorkerProfile
    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }
}
