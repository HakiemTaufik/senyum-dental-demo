<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return ['title' => $title = fake()->unique()->words(2, true), 'slug' => Str::slug($title), 'content' => [['type' => 'rich_text', 'data' => ['heading' => null, 'body' => '<p>'.fake()->paragraph().'</p>']]], 'is_published' => true];
    }
}
