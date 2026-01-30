<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level'];

    /**
     * Relación Many-to-Many con WorkerProfile.
     */
    public function workerProfiles()
    {
        // La tabla pivote será 'language_worker_profile' por convención
        return $this->belongsToMany(WorkerProfile::class);
    }
}
