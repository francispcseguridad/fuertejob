<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'email_templates';

    /**
     * Campos que pueden ser llenados de forma masiva (Mass Assignable).
     *
     * @var array
     */
    protected $fillable = [
        'name',    // Nombre interno de la plantilla (para el administrador)
        'type',    // Tipo o clave para asociar el envío (e.g., 'registration', 'password_reset', 'job_alert')
        'subject', // Asunto del correo (dinámico con placeholders)
        'body',    // Cuerpo del correo (HTML o Markdown)
    ];
}
