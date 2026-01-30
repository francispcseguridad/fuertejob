<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommercialContact extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'origin',
        'detail',
        'is_read',
        'ip_address',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
