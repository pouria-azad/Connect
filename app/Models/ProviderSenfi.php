<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSenfi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provider_senfi';

    protected $fillable = [
        'provider_id',
        'business_name',
        'business_license_number',
        'tax_id',
        'business_address',
        'business_phone',
        'business_hours',
        'accepted_payment_methods',
        'has_physical_store',
        'portfolio_images',
        'tags',
        'base_service_fee',
        'is_verified',
        'verified_at',
        'verified_by_admin_id'
    ];

    protected $casts = [
        'business_hours' => 'array',
        'accepted_payment_methods' => 'array',
        'portfolio_images' => 'array',
        'tags' => 'array',
        'has_physical_store' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'base_service_fee' => 'decimal:2'
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->through('provider');
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_admin_id');
    }

    public function isVerified(): bool
    {
        return $this->is_verified && $this->verified_at !== null;
    }

    public function getBusinessHoursForDay(string $day): array
    {
        return $this->business_hours[$day] ?? [];
    }

    public function isOpenNow(): bool
    {
        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');
        
        $hours = $this->getBusinessHoursForDay($currentDay);
        
        foreach ($hours as $period) {
            if ($currentTime >= $period['open'] && $currentTime <= $period['close']) {
                return true;
            }
        }
        
        return false;
    }
} 