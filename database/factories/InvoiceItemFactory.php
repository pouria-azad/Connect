<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unit = $this->faker->numberBetween(1000, 100000);
        return [
            'invoice_id' => Invoice::factory(),
            'title' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $unit,
            'total_price' => $quantity * $unit,
        ];
    }
} 