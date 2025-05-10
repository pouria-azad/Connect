<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {
    use HasFactory;
    protected $fillable = ['user_id', 'subject', 'status'];

    protected $attributes = [
        'status' => 'open',
    ];


    public function messages() {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
