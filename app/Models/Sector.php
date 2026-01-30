<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =================================================================
    // RELACIONES JERÁRQUICAS
    // =================================================================

    /**
     * Obtiene el sector padre si esta instancia es un subsector.
     * Un sector (child) pertenece a un padre (parent).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Sector::class, 'parent_id');
    }

    /**
     * Obtiene todos los subsectores si esta instancia es un sector principal.
     * Un sector (parent) tiene muchos hijos (children).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Sector::class, 'parent_id');
    }

    // =================================================================
    // SCOPES ÚTILES
    // =================================================================

    /**
     * Scope para obtener solo las categorías principales (aquellas sin padre).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id')
            ->orderBy('sort_order');
    }

    /**
     * Scope para obtener solo los subsectores (aquellos con padre).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubCategories($query)
    {
        return $query->whereNotNull('parent_id')
            ->orderBy('sort_order');
    }
}
