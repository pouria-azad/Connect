<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementPaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisement_id',
        'user_id',
        'amount',
        'payment_method',
        'paid_at',
        'status',
        'gateway_ref_number',
    ];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 