<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            do {
                $user->referral_code = strtoupper(Str::random(8));
            } while (static::where('referral_code', $user->referral_code)->exists());
        });
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function getWalletBalanceAttribute()
    {
        return $this->balance;
    }


    public function updateBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

}
