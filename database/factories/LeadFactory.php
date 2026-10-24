<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => LeadType::Enquiry,
            'status' => LeadStatus::New,
            'name' => fake()->name(),
            'phone' => '268-'.fake()->numberBetween(460, 789).'-'.fake()->numerify('####'),
            'email' => fake()->unique()->safeEmail(),
            'contact_pref' => 'phone',
            'message' => fake()->sentence(),
        ];
    }

    public function type(LeadType $type): static
    {
        return $this->state(fn () => ['type' => $type]);
    }
}
