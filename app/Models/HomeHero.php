<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeHero extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'button1_text',
        'button1_url',
        'button2_text',
        'button2_url',
        'background_image',
        'is_active',
    ];
}
