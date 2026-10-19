<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name).' (Demo)',
            'slug' => Str::slug($name).'-demo-'.fake()->unique()->numerify('###'),
            'description' => 'Demo department record for local development only. '.fake()->paragraph(),
            'icon' => 'building',
            'image' => 'https://placehold.co/1200x800/0F766E/FFFFFF?text=Demo+Department',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
