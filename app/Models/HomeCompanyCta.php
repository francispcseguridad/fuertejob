<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCompanyCta extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'button_text', 'button_url', 'background_image', 'is_active'];
}
