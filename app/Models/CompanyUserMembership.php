<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CompanyCreditUsageLog;
use App\Models\CompanyResourceBalance;
use App\Models\User;

class CompanyUserMembership extends Model
{
    use HasFactory;

    protected $table = 'company_user_memberships';

    protected $fillable = [
        'company_profile_id',
        'user_id',
        'bono_purchase_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (CompanyUserMembership $membership) {
            if (!$membership->expires_at && $membership->bono_purchase_id) {
                $purchase = $membership->bonoPurchase()->with('bonoCatalog')->first();
                $durationDays = (int) ($purchase?->bonoCatalog?->duration_days ?? 0);
                if ($durationDays > 0) {
                    $membership->expires_at = ($purchase->purchase_date ?? now())->addDays($durationDays);
                }
            }

            $balance = CompanyResourceBalance::firstOrCreate(
                ['company_profile_id' => $membership->company_profile_id],
                ['available_cv_views' => 0, 'available_user_seats' => 0]
            );

            if ($balance->available_user_seats <= 0) {
                return false;
            }

            return true;
        });

        static::created(function (CompanyUserMembership $membership) {
            $balance = CompanyResourceBalance::firstOrCreate(
                ['company_profile_id' => $membership->company_profile_id],
                ['available_cv_views' => 0, 'available_user_seats' => 0]
            );

            $balance->available_user_seats = max(0, (int) $balance->available_user_seats - 1);
            $balance->used_user_seats += 1;
            $balance->save();

            CompanyCreditUsageLog::recordUsage(
                $membership->company_profile_id,
                'user_seat_assignment',
                1,
                [
                    'related_type' => User::class,
                    'related_id' => $membership->user_id,
                    'description' => 'Crédito usado para asignar un asiento de usuario',
                    'metadata' => [
                        'user_id' => $membership->user_id,
                        'membership_id' => $membership->id,
                    ],
                ]
            );
        });
    }

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bonoPurchase(): BelongsTo
    {
        return $this->belongsTo(BonoPurchase::class, 'bono_purchase_id');
    }
}
