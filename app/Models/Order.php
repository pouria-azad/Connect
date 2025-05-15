<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'service_id',
        'status',
        'price',
        'requirements',
        'delivery_date'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'price' => 'decimal:2'
    ];

    /**
     * Get the user who ordered the service.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider who delivers the service.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the service that was ordered.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the conversation associated with the order.
     */
    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }
}
