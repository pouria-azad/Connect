<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'chat_id' => null,
            'request_id' => null,
            'sender_user_id' => User::factory(),
            'recipient_user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(10000, 1000000),
            'final_amount' => $this->faker->numberBetween(10000, 1000000),
            'discount_code_id' => null,
            'status' => 'pending',
            'invoice_number' => $this->faker->unique()->numerify('INV#####'),
            'paid_at' => null,
            'payment_method' => null,
            'gateway_transaction_id' => null,
        ];
    }
} 