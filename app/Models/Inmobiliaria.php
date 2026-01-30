<?php

namespace App\Models;

use App\Models\Island;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inmobiliaria extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'island_id',
    ];

    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }
}
