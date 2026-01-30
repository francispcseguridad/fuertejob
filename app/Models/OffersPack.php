<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OffersPack extends Model
{
    use HasFactory;

    protected $table = 'offers_packs';

    protected $fillable = [
        'name',
        'total_offers',
        'duration_days',
    ];

    public function bonoCatalogs(): BelongsToMany
    {
        return $this->belongsToMany(BonoCatalog::class, 'bono_offers_packs')
            ->withPivot('quantity');
    }
}
