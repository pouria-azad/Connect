<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {
    use HasFactory;
    protected $fillable = ['user_id', 'subject', 'status', 'priority'];

    protected $attributes = [
        'status' => 'open',
        'priority' => 'medium',
    ];

    protected $casts = [
        'priority' => 'string',
    ];

    public function messages() {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    public function lastMessage() {
        return $this->hasOne(SupportMessage::class, 'ticket_id')->latest();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
