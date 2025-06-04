<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'address',
        'phone',
        'logo',
        'description',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
} 