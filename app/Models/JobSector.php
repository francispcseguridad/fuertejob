<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobSector extends Model
{
    protected $fillable = ['name', 'code', 'icon'];

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class, 'job_sector_id');
    }

    /**
     * Trabajadores que han seleccionado este sector.
     */
    public function workerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(WorkerProfile::class, 'job_sector_worker_profile');
    }
}

class ProfessionalCategory extends Model
{
    protected $fillable = ['group_number', 'name', 'description'];

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class, 'professional_category_id');
    }
}
