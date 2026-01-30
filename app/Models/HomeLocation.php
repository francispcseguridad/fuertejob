<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeLocation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'url', 'order', 'is_active'];
}
