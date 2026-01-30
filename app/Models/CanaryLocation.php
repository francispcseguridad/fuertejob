<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanaryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'province',
        'island',
        'country',
    ];
}
