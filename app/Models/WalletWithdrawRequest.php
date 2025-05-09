<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletWithdrawRequest extends Model
{
    protected $fillable = ['user_id', 'amount', 'status', 'admin_note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
