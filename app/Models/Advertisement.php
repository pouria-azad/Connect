<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'start_date',
        'end_date',
        'status',
        'code',
        'display_count',
        'click_count',
        'views',
        'image_url',
        'cost',
        'destination_url',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'display_count' => 'integer',
        'click_count' => 'integer',
        'views' => 'integer',
        'cost' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(AdvertisementPaymentTransaction::class);
    }

    public function isActive()
    {
        return $this->status === 'active' &&
            (is_null($this->start_date) || $this->start_date <= now()) &&
            (is_null($this->end_date) || $this->end_date >= now());
    }

    public function incrementClickCount()
    {
        $this->increment('click_count');
    }

    public function incrementDisplayCount()
    {
        $this->increment('display_count');
    }
} 