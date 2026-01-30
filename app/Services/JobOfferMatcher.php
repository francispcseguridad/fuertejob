<?php

namespace App\Services;

use App\Models\JobOffer;
use App\Models\Language;
use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class JobOfferMatcher
{
    private const WEIGHTS = [
        'sectors' => 45,
        'skills' => 20,
        'tools' => 15,
        'languages' => 5,
        'modality' => 5,
        'location' => 5,
    ];

    /**
     * Calcula los candidatos ordenados por match para una oferta concreta.
     *
     * @param JobOffer $offer
     * @param int|null $companyProfileId
     * @return array
     */
    public function match(JobOffer $offer, ?int $companyProfileId = null): array
    {
        $requiredSkillIds = $offer->skills()->pluck('skills.id')->toArray();
        $requiredToolIds = $offer->tools()->pluck('tools.id')->toArray();

        $requiredLanguageNames = json_decode($offer->required_languages ?? '[]', true);
        $requiredLanguageIds = [];

        if (!empty($requiredLanguageNames)) {
            $requiredLanguageIds = Language::whereIn('name', $requiredLanguageNames)
                ->pluck('id')
                ->toArray();
        }

        $offerModality = $offer->modality;
        $offerLocation = $offer->location;

        $totalRequiredSkills = count($requiredSkillIds);
        $totalRequiredTools = count($requiredToolIds);
        $totalRequiredLanguages = count($requiredLanguageIds);

        $cvUnlockedSelect = '0';
        $cvUnlockedBindings = [];
        if ($companyProfileId) {
            $cvUnlockedSelect = "EXISTS(SELECT 1 FROM company_cv_view_logs WHERE company_profile_id = ? AND worker_profile_id = worker_profiles.id)";
            $cvUnlockedBindings = [$companyProfileId];
        }

        $skillSql = '0';
        if (!empty($requiredSkillIds)) {
            $ids = implode(',', $requiredSkillIds);
            $skillSql = "(SELECT COUNT(DISTINCT skill_id) FROM skill_worker_profile WHERE skill_id IN ($ids) AND worker_profile_id = worker_profiles.id)";
        }

        $toolSql = '0';
        if (!empty($requiredToolIds)) {
            $ids = implode(',', $requiredToolIds);
            $toolSql = "(SELECT COUNT(DISTINCT tool_id) FROM tool_worker_profile WHERE tool_id IN ($ids) AND worker_profile_id = worker_profiles.id)";
        }

        $languageSql = '0';
        if (!empty($requiredLanguageIds)) {
            $ids = implode(',', $requiredLanguageIds);
            $languageSql = "(SELECT COUNT(DISTINCT language_id) FROM language_worker_profile WHERE language_id IN ($ids) AND worker_profile_id = worker_profiles.id)";
        }

        $sectorSql = '0';
        if ($offer->job_sector_id) {
            $sectorSql = "(SELECT COUNT(*) FROM job_sector_worker_profile WHERE job_sector_id = {$offer->job_sector_id} AND worker_profile_id = worker_profiles.id)";
        }

        $workersWithScore = WorkerProfile::query()
            ->select('worker_profiles.*')
            ->selectRaw("EXISTS(SELECT 1 FROM candidate_selections WHERE candidate_selections.worker_profile_id = worker_profiles.id AND candidate_selections.job_offer_id = ?) as is_selected", [$offer->id])
            ->selectRaw("$skillSql as skill_matches")
            ->selectRaw("$toolSql as tool_matches")
            ->selectRaw("$languageSql as language_matches")
            ->selectRaw("$sectorSql as sector_match")
            ->selectRaw($cvUnlockedSelect . ' as cv_unlocked', $cvUnlockedBindings)
            ->selectRaw($this->buildScoreSelect(
                $totalRequiredSkills,
                $totalRequiredTools,
                $totalRequiredLanguages,
                $offerModality,
                $offerLocation,
                $skillSql,
                $toolSql,
                $languageSql,
                $sectorSql
            ) . ' as match_score')
            ->orderByDesc('match_score')
            ->with([
                'user',
                'skills:id,name',
                'tools:id,name',
                'languages:id,name',
                'educations',
            ]);

        $matchedWorkers = $workersWithScore->get();

        return [
            'weights' => self::WEIGHTS,
            'totalWeight' => array_sum(self::WEIGHTS),
            'matchedWorkers' => $matchedWorkers,
        ];
    }

    /**
     * Retorna los trabajadores que han marcado el mismo sector de la oferta.
     *
     * @param JobOffer $offer
     * @return Collection
     */
    public function getWorkersBySector(JobOffer $offer): Collection
    {
        if (!$offer->job_sector_id) {
            return collect();
        }

        return WorkerProfile::whereHas('desiredSectors', function (Builder $query) use ($offer) {
            $query->where('job_sectors.id', $offer->job_sector_id);
        })
            ->with('user')
            ->get();
    }

    /**
     * Devuelve los trabajadores que deben recibir notificación de publicación.
     *
     * @param JobOffer $offer
     * @param float $minMatchShare
     * @param int|null $companyProfileId
     * @return Collection
     */
    public function getPublicationRecipients(JobOffer $offer, float $minMatchShare = 0.4, ?int $companyProfileId = null): Collection
    {
        $sectorWorkers = $this->getWorkersBySector($offer);
        $matchData = $this->match($offer, $companyProfileId);

        $threshold = ($matchData['totalWeight'] ?? 0) * $minMatchShare;
        $matchedWorkers = collect($matchData['matchedWorkers'])
            ->filter(function (WorkerProfile $worker) use ($threshold) {
                return (float) ($worker->match_score ?? 0) >= $threshold;
            });

        return $sectorWorkers
            ->merge($matchedWorkers)
            ->unique('id')
            ->filter(function (WorkerProfile $worker) {
                return $worker->user && !empty($worker->user->email);
            })
            ->values();
    }

    /**
     * Construye la parte SQL que calcula la puntuación total del match.
     *
     * @param int $totalRequiredSkills
     * @param int $totalRequiredTools
     * @param int $totalRequiredLanguages
     * @param string|null $offerModality
     * @param string|null $offerLocation
     * @param string $skillSql
     * @param string $toolSql
     * @param string $languageSql
     * @param string $sectorSql
     * @return string
     */
    protected function buildScoreSelect(
        int $totalRequiredSkills,
        int $totalRequiredTools,
        int $totalRequiredLanguages,
        ?string $offerModality,
        ?string $offerLocation,
        string $skillSql,
        string $toolSql,
        string $languageSql,
        string $sectorSql
    ): string {
        $offerModality = $offerModality ?? 'presencial';
        $offerLocation = $offerLocation ?? '';

        $scoreSkills = $totalRequiredSkills > 0
            ? "(COALESCE(($skillSql), 0) / $totalRequiredSkills) * " . self::WEIGHTS['skills']
            : self::WEIGHTS['skills'];

        $scoreTools = $totalRequiredTools > 0
            ? "(COALESCE(($toolSql), 0) / $totalRequiredTools) * " . self::WEIGHTS['tools']
            : self::WEIGHTS['tools'];

        $scoreLanguages = $totalRequiredLanguages > 0
            ? "(COALESCE(($languageSql), 0) / $totalRequiredLanguages) * " . self::WEIGHTS['languages']
            : self::WEIGHTS['languages'];

        $scoreModality = "
            CASE worker_profiles.preferred_modality
                WHEN '{$offerModality}' THEN " . self::WEIGHTS['modality'] . "
                WHEN 'hibrido' AND '{$offerModality}' IN ('presencial', 'remoto') THEN " . self::WEIGHTS['modality'] . " * 0.75
                WHEN 'presencial' AND '{$offerModality}' = 'hibrido' THEN " . self::WEIGHTS['modality'] . " * 0.75
                WHEN 'remoto' AND '{$offerModality}' = 'hibrido' THEN " . self::WEIGHTS['modality'] . " * 0.75
                ELSE 0
            END
        ";

        $scoreLocation = "
            CASE
                WHEN '{$offerModality}' IN ('presencial', 'hibrido') AND (worker_profiles.city = '{$offerLocation}' OR worker_profiles.country = '{$offerLocation}') THEN " . self::WEIGHTS['location'] . "
                WHEN '{$offerModality}' = 'remoto' THEN " . self::WEIGHTS['location'] . " * 0.5
                ELSE 0
            END
        ";

        $scoreSector = "CASE WHEN ($sectorSql) > 0 THEN " . self::WEIGHTS['sectors'] . " ELSE 0 END";

        return "COALESCE( ({$scoreSector}), 0) +
                COALESCE( ({$scoreSkills}), 0) + 
                COALESCE( ({$scoreTools}), 0) + 
                COALESCE( ({$scoreLanguages}), 0) + 
                COALESCE( ({$scoreModality}), 0) + 
                COALESCE( ({$scoreLocation}), 0)";
    }
}
