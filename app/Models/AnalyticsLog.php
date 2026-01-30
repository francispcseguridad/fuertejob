<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsLog extends Model
{
    use HasFactory;

    protected $table = 'analytics_logs';

    protected $fillable = [
        'user_id',
        'session_id',
        'url',
        'route_name',
        'route_params',
        'related_type',
        'related_id',
        'method',
        'ip_address',
        'user_agent',
        'referer',
    ];

    protected $casts = [
        'route_params' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
