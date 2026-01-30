<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyResourceBalance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_resources_balance';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_profile_id',
        'total_offer_credits',
        'used_offer_credits',
        'available_offer_credits',
        'total_cv_views',
        'used_cv_views',
        'available_cv_views',
        'total_user_seats',
        'used_user_seats',
        'available_user_seats',
        'offer_visibility_days',
    ];

    /**
     * Get the company profile that owns the resource balance.
     */
    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class, 'company_profile_id');
    }
}
