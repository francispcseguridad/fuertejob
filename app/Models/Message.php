<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;


    protected $table = 'messages';

    protected $fillable = [
        'thread_id',
        'sender_id',
        'content',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Un mensaje pertenece a un hilo.
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Obtiene el usuario que envió el mensaje.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
