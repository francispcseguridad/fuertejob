<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BonoCatalog;

class AnalyticsFunction extends Model
{
    use HasFactory;

    protected $fillable = [
        'analytics_model_id',
        'name',
        'code',
        'description',
        'details',
        'position',
        'is_active',
    ];

    public function analyticsModel()
    {
        return $this->belongsTo(AnalyticsModel::class);
    }

    public function bonoCatalogs()
    {
        return $this->belongsToMany(BonoCatalog::class, 'analytics_function_bono_catalog');
    }
}
