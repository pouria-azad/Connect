<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'request_id',
        'sender_user_id',
        'recipient_user_id',
        'amount',
        'final_amount',
        'discount_code_id',
        'status',
        'invoice_number',
        'paid_at',
        'payment_method',
        'gateway_transaction_id',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recipient_user()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function sender_user()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
} 