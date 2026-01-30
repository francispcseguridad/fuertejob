<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase del modelo Eloquent para la tabla 'faq_items'.
 * Almacena Preguntas Frecuentes y artículos de ayuda, segmentados por público objetivo.
 *
 * @property int $id Identificador único.
 * @property string $target_audience Público objetivo ('worker', 'company', 'general').
 * @property string $question La pregunta frecuente o título del artículo.
 * @property string $answer La respuesta detallada o contenido del artículo.
 * @property int $sort_order Orden de visualización.
 * @property bool $is_published Indica si el item está publicado.
 * @property \Illuminate\Support\Carbon $created_at Fecha y hora de creación.
 * @property \Illuminate\Support\Carbon $updated_at Fecha y hora de última actualización.
 */
class FaqItem extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'faq_items';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'target_audience',
        'question',
        'answer',
        'sort_order',
        'is_published',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constantes para los valores del campo ENUM 'target_audience'.
     */
    public const AUDIENCE_WORKER = 'worker';
    public const AUDIENCE_COMPANY = 'company';
    public const AUDIENCE_GENERAL = 'general';

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------

    /**
     * Scope para obtener solo los artículos publicados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope para obtener artículos por público objetivo.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $audience El público objetivo (worker, company, general).
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAudience($query, string $audience)
    {
        return $query->where('target_audience', $audience);
    }
}
