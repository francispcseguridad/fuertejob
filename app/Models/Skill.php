<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relación Many-to-Many con WorkerProfile.
     */
    public function workerProfiles()
    {
        return $this->belongsToMany(WorkerProfile::class);
    }
}
