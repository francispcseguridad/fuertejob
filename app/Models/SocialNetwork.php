<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialNetwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon_class',
        'url',
        'is_active',
        'order',
        'island_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }
}
