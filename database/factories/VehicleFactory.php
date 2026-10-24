<?php

namespace Database\Factories;

use App\Enums\Condition;
use App\Enums\Transmission;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Vehicle> */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $make = fake()->randomElement(['Nissan', 'Suzuki']);
        $model = $make === 'Nissan' ? fake()->randomElement(['X-Trail', 'Navara', 'Almera', 'Kicks']) : fake()->randomElement(['Swift', 'Vitara', 'Jimny', 'Ertiga']);
        $year = fake()->numberBetween(2015, 2024);

        return [
            'slug' => Str::slug("{$year} {$make} {$model}").'-'.Str::lower(Str::random(5)),
            'condition' => Condition::Used,
            'status' => VehicleStatus::Available,
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'body_type' => fake()->randomElement(['SUV', 'Hatchback', 'Sedan', 'Pickup']),
            'transmission' => fake()->randomElement(Transmission::cases()),
            'mileage_km' => fake()->numberBetween(8000, 120000),
            'price' => fake()->optional(0.7)->numberBetween(25, 140) * 1000 ?: null,
            'published' => true,
            'is_demo' => true,
        ];
    }
}
