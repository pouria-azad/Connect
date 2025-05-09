<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {
    protected $fillable = ['user_id', 'subject', 'status'];
    public function messages() {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }
}
