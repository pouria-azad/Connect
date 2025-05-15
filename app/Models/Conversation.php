<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['order_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_blocked')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }
}
