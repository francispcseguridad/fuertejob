<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSector extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'url', 'order', 'is_active'];
}
