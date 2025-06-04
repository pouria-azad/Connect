<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderCanctyar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provider_canctyar';

    protected $fillable = [
        'user_id',
        'certification_number',
        'skills',
        'service_areas',
        'availability_hours',
        'can_travel',
        'travel_fee_per_km',
        'minimum_service_fee',
        'portfolio_images',
        'tags',
        'is_verified',
        'verified_at',
        'verified_by_admin_id'
    ];

    protected $casts = [
        'skills' => 'array',
        'service_areas' => 'array',
        'availability_hours' => 'array',
        'portfolio_images' => 'array',
        'tags' => 'array',
        'can_travel' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'travel_fee_per_km' => 'decimal:2',
        'minimum_service_fee' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_admin_id');
    }

    public function isVerified(): bool
    {
        return $this->is_verified && $this->verified_at !== null;
    }

    public function isAvailableNow(): bool
    {
        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');
        
        $hours = $this->availability_hours[$currentDay] ?? [];
        
        foreach ($hours as $period) {
            if ($currentTime >= $period['start'] && $currentTime <= $period['end']) {
                return true;
            }
        }
        
        return false;
    }

    public function servesArea(int $cityId): bool
    {
        return in_array($cityId, $this->service_areas);
    }

    public function calculateTravelFee(float $distance): float
    {
        if (!$this->can_travel) {
            return 0;
        }

        return $this->travel_fee_per_km * $distance;
    }

    public function hasSkill(string $skill): bool
    {
        return in_array($skill, $this->skills);
    }
} 