<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $table = 'contact_messages';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'role_type',
        'inquiry_type',
        'message',
        'attachment_path',
        'ip_address',
        'status',
        'response_message',
        'responded_at',
    ];
}
