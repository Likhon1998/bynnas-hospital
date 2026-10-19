<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Dr. '.fake()->unique()->name();

        return [
            'user_id' => null,
            'department_id' => Department::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'photo' => 'https://placehold.co/600x800/0F766E/FFFFFF?text=Demo+Doctor',
            'designation' => 'Consultant',
            'qualifications' => 'MBBS, MD (Demo)',
            'specialization' => 'General Medicine',
            'experience_years' => fake()->numberBetween(5, 20),
            'registration_number' => 'DEMO-REG-'.fake()->unique()->numerify('####'),
            'consultation_fee' => fake()->randomElement([1000, 1200, 1500, 1800]),
            'biography' => 'Fictional demo doctor profile for website development. Not a real clinician. Patients must consult qualified healthcare professionals.',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
