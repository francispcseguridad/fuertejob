<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_profile_id',
        'file_name',
        'file_path', // Ruta relativa dentro de storage
        'is_primary',
    ];

    /**
     * Relación: Un Cv pertenece a un WorkerProfile
     */
    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }

    /**
     * Obtiene el CV primario para un perfil dado.
     * @param int $workerProfileId
     * @return \App\Models\Cv|null
     */
    public static function getPrimaryCvByWorkerProfileId(int $workerProfileId)
    {
        // Asume que solo quieres el marcado como primario
        return static::where('worker_profile_id', $workerProfileId)
            ->where('is_primary', true)
            ->latest() // Por si hay múltiples primarios (idealmente no debería pasar), toma el más reciente
            ->first();
    }
}
