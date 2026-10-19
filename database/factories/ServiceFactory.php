<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'department_id' => Department::factory(),
            'title' => ucwords($title).' (Demo)',
            'slug' => Str::slug($title).'-demo-'.fake()->unique()->numerify('###'),
            'summary' => 'Demo hospital service for local development.',
            'body' => 'This is fictional demo content for the hospital website. It is not a clinical service listing for a real facility.',
            'icon' => 'heart-pulse',
            'image' => 'https://placehold.co/1200x800/0F766E/FFFFFF?text=Demo+Service',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
