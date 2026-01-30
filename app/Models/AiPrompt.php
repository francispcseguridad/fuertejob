<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase del modelo Eloquent para la tabla 'ai_prompts'.
 * Esta tabla almacena los fragmentos de conocimiento o instrucciones
 * del sistema que se utilizan para guiar la generación de texto de la IA.
 *
 * @property int $id Identificador único.
 * @property string $category Categoría del prompt (ej: 'Candidatos', 'Empresas').
 * @property string $title Título descriptivo del prompt.
 * @property string $detail El contenido de la instrucción o fragmento de conocimiento para la IA.
 * @property string $status Estado del prompt ('active', 'inactive').
 * @property \Illuminate\Support\Carbon $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon $updated_at Fecha y hora de última actualización.
 */
class AiPrompt extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ai_prompts';

    /**
     * Los atributos que son asignables masivamente (fillable).
     * Esto permite asignar valores a estos campos usando métodos como `create()` o `fill()`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'title',
        'detail',
        'status',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * El campo `status` es de tipo ENUM en la base de datos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string', // El campo ENUM se maneja como string
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Las constantes de los valores de estado para mejorar la legibilidad del código.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------

    /**
     * Scope para obtener solo los prompts que están activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope para obtener prompts por una categoría específica.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
