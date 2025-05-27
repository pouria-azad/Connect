<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'initial_balance',
        'current_balance',
        'is_used',
        'is_active',
        'created_by_user_id',
        'used_by_user_id',
        'assigned_to_user_id',
        'redeemed_by_user_id',
        'expires_at',
        'redeemed_at',
        'source_type',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'is_active' => 'boolean',
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime'
    ];

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function isValid(): bool
    {
        return !$this->is_used && 
               $this->is_active && 
               $this->current_balance > 0 && 
               $this->expires_at->isFuture();
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'reference_id')
            ->where('type', 'deposit_gift_card');
    }
}
