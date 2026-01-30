<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class JobViewLog extends Model
{
    protected $table = 'job_views_log'; // Nombre manual por el plural irregular
    public $timestamps = false; // Solo usamos viewed_at

    protected $fillable = ['job_offer_id', 'ip_address', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }
}
