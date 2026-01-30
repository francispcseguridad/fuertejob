<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $company_profile_id
 * @property string $title
 * @property string $description
 * @property string|null $requirements  // Requisitos del puesto
 * @property string|null $benefits      // Beneficios ofrecidos
 * @property string $modality           // presencial, remoto, hibrido
 * @property string $location
 * @property string|null $salary_range // Rango salarial
 * @property string $contract_type
 * @property string $status // draft, published, paused, closed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $status_display // Accessor para el estado en español
 * @property string $modality_display // Accessor para la modalidad en español
 * @property float $max_salary_attribute // NUEVO: Accessor para el salario máximo
 * @property \App\Models\CompanyProfile $companyProfile
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Application> $applications
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Skill> $skills
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Tool> $tools
 */
class JobOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'job_offers';

    protected $fillable = [
        'company_profile_id',
        'title',
        'description',
        'requirements', // Añadido
        'benefits',     // Añadido
        'modality',     // Añadido
        'location',
        'province',     // Añadido
        'island',       // Añadido
        'island_id',
        'job_sector_id',
        'analytics_model_id',
        'salary_range',
        'contract_type',
        'status',
        'company_visible',
        'is_published',
        'pending_review',
        'pending_review_at',
        'pending_review_reason',
    ];

    protected $casts = [
        'company_visible' => 'boolean',
        'is_published' => 'boolean',
        'pending_review' => 'boolean',
        'pending_review_at' => 'datetime',
    ];
    
    // ====================================================================
    // ACCESSORES
    // ====================================================================

    /**
     * Accessor para extraer el valor máximo del rango salarial (usado en el matching).
     * El controlador lo necesita como $jobOffer->max_salary_attribute.
     * * @return float El salario máximo o un valor por defecto muy alto.
     */
    public function getMaxSalaryAttribute(): float
    {
        // El formato de salary_range se asume como "MIN - MAX" o similar.
        // 1. Buscamos el último número en la cadena (asumiendo que es el máximo).
        // 2. Quitamos cualquier caracter no numérico (excepto punto/coma si se usa como separador decimal).

        $salaryRange = $this->salary_range ?? '';

        // Expresión regular para encontrar el último número que podría tener separadores de miles/decimales.
        // Busca el último grupo de dígitos que puede incluir puntos o comas.
        if (preg_match('/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d+)?)\s*$/', $salaryRange, $matches)) {
            $value = $matches[1];

            // Lógica para limpiar y convertir a float (asumiendo formato europeo 1.000,00 o americano 1,000.00)

            // Si el último separador es una coma y hay más de 3 dígitos antes, asumimos que la coma es decimal (formato europeo D,M)
            if (str_contains($value, ',') && substr_count($value, ',') == 1 && strrpos($value, ',') > strrpos($value, '.')) {
                // Europeo: miles con punto, decimales con coma -> Cambiamos coma a punto y quitamos puntos
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // Americano: miles con coma, decimales con punto -> Quitamos comas
                $value = str_replace(',', '', $value);
            }

            return (float) $value;
        }

        // Si no se puede parsear, devolvemos un valor alto por seguridad, pero idealmente se controla el formato.
        return 999999.0;
    }


    /**
     * Accessor para mapear el estado de la base de datos (inglés) a un formato de visualización (español).
     * Usa $oferta->status_display
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'Borrador' => 'Borrador',
            'Publicado' => 'Publicado',
            'Pausada' => 'Pausada',
            'Finalizada' => 'Finalizada',
            default => 'Desconocido',
        };
    }

    /**
     * Accessor para mapear la modalidad (todo minúsculas) a un formato presentable.
     * Usa $oferta->modality_display
     */
    public function getModalityDisplayAttribute(): string
    {
        return match ($this->modality) {
            'presencial' => 'Presencial',
            'remoto' => 'Remoto',
            'hibrido' => 'Híbrido',
            default => 'N/A',
        };
    }

    // ====================================================================
    // RELACIONES
    // ====================================================================

    /**
     * Define la relación: Una JobOffer pertenece a una CompanyProfile.
     */
    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function analyticsModel(): BelongsTo
    {
        return $this->belongsTo(AnalyticsModel::class);
    }

    /**
     * Relación: Una JobOffer tiene muchas Applications.
     */
    public function applications(): HasMany
    {
        // Asumiendo que existe un modelo Application
        return $this->hasMany(Application::class);
    }

    /**
     * Relación: Una JobOffer tiene muchas habilidades (Cargadas via Pivot).
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_offer_skill');
    }

    /**
     * Relación: Una JobOffer tiene muchas herramientas (Cargadas via Pivot).
     */
    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'job_offer_tool');
    }

    /**
     * Relación: Candidatos seleccionados para la oferta (Gestionado via candidates_selections).
     */
    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(WorkerProfile::class, 'candidate_selections')
            ->withPivot([
                'company_profile_id',
                'selection_date',
                'current_status',
                'priority',
                'initial_assessment',
                'expected_salary',
                'time_to_hire_days'
            ])
            ->withTimestamps();
    }

    public function islandRelation(): BelongsTo
    {
        return $this->belongsTo(Island::class, 'island_id');
    }

    public function jobSector(): BelongsTo
    {
        return $this->belongsTo(JobSector::class, 'job_sector_id');
    }


    public function viewLogs(): HasMany
    {
        return $this->hasMany(JobViewLog::class);
    }

    // Métodos de utilidad para analíticas
    public function getDaysToFirstCVAttribute(): ?int
    {
        if (!$this->published_at || !$this->first_cv_received_at) return null;
        return $this->published_at->diffInDays($this->first_cv_received_at);
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->views_count === 0) return 0;
        return round(($this->applications_count / $this->views_count) * 100, 2);
    }
}
