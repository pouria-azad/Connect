<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'service_request_id',
        'customer_user_id',
        'service_provider_user_id',
        'user_id',
        'product_id',
        'rating',
        'comment',
        'rating_details',
        'is_verified',
        'is_visible',
    ];

    protected $casts = [
        'rating_details' => 'array',
        'is_verified' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'service_provider_user_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
} 