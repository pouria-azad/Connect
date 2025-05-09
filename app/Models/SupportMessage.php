<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model {
    protected $fillable = ['ticket_id', 'user_id', 'message', 'is_admin'];
    public function ticket() {
        return $this->belongsTo(SupportTicket::class);
    }
}
