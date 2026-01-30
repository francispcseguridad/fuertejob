<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'level',
        'description',
        'is_active',
    ];

    public function functions()
    {
        return $this->hasMany(AnalyticsFunction::class)->orderBy('position');
    }

    public function bonoCatalogs()
    {
        return $this->hasMany(BonoCatalog::class);
    }
}
