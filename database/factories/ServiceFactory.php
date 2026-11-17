<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => $name = fake()->unique()->words(2, true), 'slug' => Str::slug($name), 'summary' => fake()->sentence(), 'description' => '<p>'.fake()->paragraph().'</p>', 'icon' => 'tooth', 'is_published' => true, 'is_bookable' => true];
    }
}
