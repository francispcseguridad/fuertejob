<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeLoopText extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'is_active', 'image', 'url'];
}
