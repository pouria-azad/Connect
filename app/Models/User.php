<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'mobile_number',
        'username',
        'full_name',
        'national_code',
        'referral_code',
        'referred_by_user_id',
        'is_admin',
        'mobile_verified_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'mobile_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    // Generate referral code on creation
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                do {
                    $user->referral_code = strtoupper(Str::random(8));
                } while (static::where('referral_code', $user->referral_code)->exists());
            }
            // Set default display_name if not provided
            if (empty($user->display_name)) {
                $user->display_name = $user->full_name;
            }
        });
    }

    // Relationships
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user', 'user_id', 'conversation_id')
            ->withPivot('is_blocked')
            ->withTimestamps();
    }

    public function bankCards(): HasMany
    {
        return $this->hasMany(UserBankCard::class);
    }

    public function assignedGiftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class, 'assigned_to_user_id');
    }

    public function redeemedGiftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class, 'redeemed_by_user_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referralTransactions()
    {
        return $this->hasMany(WalletTransaction::class)->where('type', 'referral');
    }

    public function adminReviewingBankCards(): HasMany
    {
        return $this->hasMany(UserBankCard::class, 'verified_by_user_id');
    }

    public function createdAdvertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'created_by_admin_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_user_id');
    }

    public function providerReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'service_provider_user_id');
    }

    public function serviceRequestsAsCustomer(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'customer_user_id');
    }

    public function serviceRequestsAsServiceProvider(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'service_provider_user_id');
    }

    public function acceptedServiceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'accepted_service_provider_user_id');
    }

    public function clubWallet(): HasOne
    {
        return $this->hasOne(ClubWallet::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Helper to get wallet balance
    public function getWalletBalanceAttribute(): float
    {
        return $this->wallet ? $this->wallet->balance : 0.00;
    }

    // Helper to get display name (if not set, fallback to full_name or username)
    public function getDisplayNameAttribute($value): string
    {
        return $value ?? $this->full_name ?? $this->username ?? 'کاربر';
    }
}
