<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modelo Pivot para la tabla skill_worker_profile
 * 
 * Este modelo permite eliminar la relación entre una habilidad (Skill) 
 * y un perfil de trabajador (WorkerProfile) SIN eliminar los registros 
 * en las tablas 'skills' o 'worker_profiles'.
 * 
 * EJEMPLOS DE USO:
 * 
 * 1. Desvincular una habilidad de un perfil (método recomendado):
 *    $workerProfile->skills()->detach($skillId);
 * 
 * 2. Desvincular todas las habilidades de un perfil:
 *    $workerProfile->skills()->detach();
 * 
 * 3. Desvincular múltiples habilidades:
 *    $workerProfile->skills()->detach([1, 2, 3]);
 * 
 * 4. Eliminar directamente desde este modelo:
 *    SkillWorkerProfile::where('skill_id', $skillId)
 *        ->where('worker_profile_id', $workerProfileId)
 *        ->delete();
 * 
 * 5. Sincronizar habilidades (elimina las no incluidas, agrega las nuevas):
 *    $workerProfile->skills()->sync([1, 2, 3]);
 */
class SkillWorkerProfile extends Pivot
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'skill_worker_profile';

    /**
     * Indica si el modelo debe tener timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'skill_id',
        'worker_profile_id',
    ];

    /**
     * Relación con el modelo Skill.
     */
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Relación con el modelo WorkerProfile.
     */
    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }

    /**
     * Desvincular una habilidad de un perfil de trabajador.
     * Este método NO elimina el registro de Skill, solo la relación.
     * 
     * @param int $skillId
     * @param int $workerProfileId
     * @return bool
     */
    public static function detachSkill(int $skillId, int $workerProfileId): bool
    {
        return self::where('skill_id', $skillId)
            ->where('worker_profile_id', $workerProfileId)
            ->delete() > 0;
    }

    /**
     * Desvincular todas las habilidades de un perfil de trabajador.
     * 
     * @param int $workerProfileId
     * @return int Número de registros eliminados
     */
    public static function detachAllSkills(int $workerProfileId): int
    {
        return self::where('worker_profile_id', $workerProfileId)->delete();
    }

    /**
     * Desvincular un perfil de trabajador de todas sus habilidades.
     * 
     * @param int $skillId
     * @return int Número de registros eliminados
     */
    public static function detachAllProfiles(int $skillId): int
    {
        return self::where('skill_id', $skillId)->delete();
    }
}
