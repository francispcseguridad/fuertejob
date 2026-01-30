<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = ['user_id', 'island_id', 'name', 'cv_path'];

    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
