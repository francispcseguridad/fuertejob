<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clase del modelo Eloquent para la tabla 'cms_contents'.
 * Almacena páginas estáticas (legal, privacidad) y entradas de blog.
 *
 * @property int $id
 * @property string $type Tipo de contenido ('page' o 'blog').
 * @property string $title Título principal.
 * @property string $slug URL amigable única.
 * @property string $body Contenido HTML/Markdown.
 * @property string|null $meta_title Título para SEO.
 * @property string|null $meta_description Descripción meta.
 * @property string|null $meta_keywords Palabras clave meta.
 * @property bool $is_published Estado de publicación.
 * @property \Illuminate\Support\Carbon|null $published_at Fecha de publicación.
 * @property int|null $user_id Autor (si es blog).
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User|null $author
 */
class CmsContent extends Model
{
    use HasFactory;

    protected $table = 'cms_contents';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'body',
        'meta_title',
        'imagen_url',
        'meta_description',
        'meta_keywords',
        'is_published',
        'published_at',
        'user_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constantes para los valores del campo ENUM 'type'.
     */
    public const TYPE_PAGE = 'page';
    public const TYPE_BLOG = 'blog';

    // --------------------------------------------------------------------------
    // RELACIONES
    // --------------------------------------------------------------------------

    /**
     * Obtiene el autor de la entrada (si el tipo es 'blog').
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // --------------------------------------------------------------------------
    // SCOPES
    // --------------------------------------------------------------------------

    /**
     * Scope para obtener solo el contenido publicado.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope para obtener solo páginas estáticas.
     */
    public function scopePages($query)
    {
        return $query->where('type', self::TYPE_PAGE);
    }

    /**
     * Scope para obtener solo entradas de blog.
     */
    public function scopeBlogPosts($query)
    {
        return $query->where('type', self::TYPE_BLOG);
    }
}
