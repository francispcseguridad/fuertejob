<?php

namespace App\Models;

use App\Models\CompanyProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCreditUsageLog extends Model
{
    use HasFactory;

    protected $table = 'company_credit_usage_logs';

    protected $fillable = [
        'company_profile_id',
        'resource_type',
        'credits_used',
        'related_type',
        'related_id',
        'metadata',
        'description',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id');
    }

    public static function recordUsage(int $companyProfileId, string $resourceType, int $creditsUsed, array $options = []): self
    {
        return self::create([
            'company_profile_id' => $companyProfileId,
            'resource_type' => $resourceType,
            'credits_used' => $creditsUsed,
            'related_type' => $options['related_type'] ?? null,
            'related_id' => $options['related_id'] ?? null,
            'description' => $options['description'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);
    }
}
