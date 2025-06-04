<?php

namespace Database\Factories;

use App\Models\RequestFile;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFileFactory extends Factory
{
    protected $model = RequestFile::class;

    public function definition(): array
    {
        return [
            'request_id' => null,
            'file_url' => '/storage/' . $this->faker->uuid . '.jpg',
            'file_name' => $this->faker->word . '.jpg',
            'file_size' => $this->faker->numberBetween(1000, 1000000),
            'file_type' => 'image/jpeg',
        ];
    }
} 