<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\CandidateSelection;
use App\Models\JobSector;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $professional_summary
 * @property string|null $city
 * @property bool $rgpd_acceptance
 * @property bool $data_veracity
 * @property string $first_name
 * @property string $last_name
 * @property string|null $phone_number
 * @property string|null $country
 * @property string|null $profile_image_url
 * @property bool $is_available
 * @property string $preferred_modality // Nuevo: presencial, remoto, hibrido
 * @property string|null $min_expected_salary // Nuevo: Rango salarial o mínimo esperado
 * @property string|null $contract_preference // Nuevo: Tipo de contrato preferido
 * @property float $min_expected_salary_attribute // Accessor para el salario mínimo como float
 * @property string $languages_list // Accessor para los idiomas como lista de strings
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Experience> $experiences
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Education> $educations
 * @property \App\Models\Cv|null $cv
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Skill> $skills
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Tool> $tools
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Language> $languages
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\JobPosition> $desiredPositions
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\JobSector> $desiredSectors
 */
class WorkerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'professional_summary',
        'city',
        'rgpd_acceptance',
        'data_veracity',
        'first_name',
        'last_name',
        'phone_number',
        'country',
        'profile_image_url',
        'is_available',
        // --- Nuevos campos de preferencias para Matching ---
        'preferred_modality',
        'min_expected_salary',
        'contract_preference',
        'province',
        'island',
    ];

    protected function profileImageUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($this->isCvPreviewPath($value)) {
                    return 'img/workers/default-avatar.svg';
                }

                return $value ?: 'img/workers/default-avatar.svg';
            }
        );
    }

    private function isCvPreviewPath(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        $normalized = strtolower($value);

        return str_contains($normalized, '.pdf')
            || str_contains($normalized, 'private_cvs')
            || str_contains($normalized, 'cvs/');
    }

    public function hasCustomProfileImage(): bool
    {
        return (bool) $this->getRawOriginal('profile_image_url');
    }

    // ====================================================================
    // ACCESSORES
    // ====================================================================

    /**
     * Accessor para obtener el salario mínimo esperado como un valor numérico (float).
     * Esto es crucial para la lógica de matching.
     * @return float El salario mínimo esperado o 0.0 si no se puede parsear.
     */
    public function getMinExpectedSalaryAttribute(): float
    {
        $salary = $this->min_expected_salary ?? '';

        // Intenta extraer el primer número de la cadena (que asumimos es el mínimo).
        if (preg_match('/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d+)?)/', $salary, $matches)) {
            $value = $matches[1];

            // Lógica para limpiar y convertir a float.
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

        return 0.0;
    }

    /**
     * Accessor para obtener una lista de los nombres de los idiomas conocidos.
     * @return array<string>
     */
    public function getLanguagesListAttribute(): array
    {
        return $this->languages->pluck('name')->toArray();
    }


    // ====================================================================
    // RELACIONES
    // ====================================================================

    /**
     * Relación uno a uno con el usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación uno a muchos con experiencias laborales.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * Relación uno a muchos con educación.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Relación uno a uno con el CV asociado.
     */
    public function cv(): HasOne
    {
        return $this->hasOne(Cv::class);
    }

    // --- RELACIONES MANY-TO-MANY ---

    /**
     * Habilidades blandas o de gestión.
     */
    public function skills(): BelongsToMany
    {
        // Asume tabla pivote 'skill_worker_profile'
        return $this->belongsToMany(Skill::class);
    }

    /**
     * Herramientas, software y hardware específicos.
     */
    public function tools(): BelongsToMany
    {
        // Asume tabla pivote 'tool_worker_profile'
        return $this->belongsToMany(Tool::class);
    }

    /**
     * Idiomas conocidos.
     */
    public function languages(): BelongsToMany
    {
        // Asume tabla pivote 'language_worker_profile'
        return $this->belongsToMany(Language::class)->withPivot('level');
    }

    /**
     * Sectores deseados por el trabajador.
     */
    public function desiredSectors(): BelongsToMany
    {
        return $this->belongsToMany(JobSector::class, 'job_sector_worker_profile');
    }

    /**
     * Candidaturas del trabajador (ofertas a las que se ha inscrito).
     * Relación uno a muchos con CandidateSelection.
     */
    public function candidateSelections(): HasMany
    {
        return $this->hasMany(CandidateSelection::class);
    }
}
