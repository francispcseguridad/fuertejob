<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo para las Islas (Ubicación)
 */
class Island extends Model
{
    protected $fillable = ['name'];

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    public function candidates(): HasMany
    {
        // Si en la tabla worker_profiles la columna se llama 'island_id'
        return $this->hasMany(WorkerProfile::class, 'island_id');

        // NOTA: Si tu columna en worker_profiles se llama simplemente 'island' 
        // y guarda el ID, asegúrate de que el segundo parámetro sea 'island'.
    }
}
