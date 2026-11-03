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
            'secondary_phone' => null,
            'email' => fake()->unique()->safeEmail(),
            'nid_number' => fake()->numerify('##########'),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'blood_group' => fake()->randomElement(['A+', 'B+', 'O+', 'AB+', 'Unknown']),
            'marital_status' => fake()->randomElement(['single', 'married']),
            'occupation' => 'Demo occupation',
            'guardian_name' => fake()->name().' (Demo)',
            'address' => 'Demo address, Dhaka (fictional)',
            'district' => 'Dhaka',
            'emergency_contact_name' => fake()->name().' (Demo)',
            'emergency_contact_relation' => 'spouse',
            'emergency_contact_phone' => '01800'.fake()->numerify('######'),
            'notes' => null,
            'is_active' => true,
        ];
    }
}
