<?php

namespace Database\Factories;

use App\Models\Dentist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dentist>
 */
class DentistFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Dr. '.fake()->name(), 'position' => 'Dental Surgeon', 'is_published' => true];
    }
}
