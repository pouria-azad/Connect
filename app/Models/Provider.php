<?php

namespace App\Models;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Provider extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','bio','is_verified','profile_image'];

    public function user()
    {
        return $this->belongsTo(User::class);
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
}

