<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'full_name' => fake()->name().' (Demo)',
            'phone' => '01700'.fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => 'Demo address, Dhaka (fictional)',
            'emergency_contact_name' => fake()->name().' (Demo)',
            'emergency_contact_phone' => '01800'.fake()->numerify('######'),
            'is_active' => true,
        ];
    }
}
