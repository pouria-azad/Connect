<?php

namespace App\Models;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Provider extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'provider_type',
        'shop_name',
        'senfi_number',
        'occupation_id',
        'bio',
        'province_id',
        'city_id',
        'can_serve_nation_wide',
        'expertise_areas',
        'is_verified',
        'profile_image',
        'last_activity_at',
        'average_rating',
        'successful_orders_count'
    ];

    protected $casts = [
        'can_serve_nation_wide' => 'boolean',
        'is_verified' => 'boolean',
        'last_activity_at' => 'datetime',
        'average_rating' => 'float',
        'successful_orders_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function senfi(): HasOne
    {
        return $this->hasOne(ProviderSenfi::class);
    }

    public function canctyar(): HasOne
    {
        return $this->hasOne(ProviderCanctyar::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'provider_services')
            ->withPivot(['price','custom_description'])
            ->withTimestamps();
    }

    public function occupation()
    {
        return $this->belongsTo(Occupation::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function medals()
    {
        return $this->hasMany(Medal::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function getSpecificProviderAttribute()
    {
        return $this->provider_type === 'senfi' ? $this->senfi : $this->canctyar;
    }
}

