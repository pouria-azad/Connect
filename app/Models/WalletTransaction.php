<?php

namespace App\Models;

use App\Events\WalletTransactionCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected static function booted()
    {
        static::created(function ($transaction) {
            event(new WalletTransactionCreated($transaction));
        });
    }
}
