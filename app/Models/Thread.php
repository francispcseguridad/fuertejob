<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;

    protected $table = 'threads';

    protected $fillable = [
        'starter_id',
        'receiver_id',
        'resource_type',
        'resource_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Obtiene todos los mensajes dentro de este hilo.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Obtiene el usuario que inició la conversación (Starter).
     */
    public function starter()
    {
        return $this->belongsTo(User::class, 'starter_id');
    }

    /**
     * Obtiene el usuario que recibe la conversación (Receiver).
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Obtiene el recurso asociado (JobOffer, CandidateSelection, etc.).
     */
    public function resource()
    {
        return $this->morphTo();
    }
}
