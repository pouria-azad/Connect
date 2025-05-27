<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'new_user_id',
        'referrer_user_id',
        'bonus_amount_per_user',
        'referral_date',
        'new_user_wallet_transaction_id',
        'referrer_user_wallet_transaction_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
