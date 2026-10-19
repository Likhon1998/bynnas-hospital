<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('+1 day', '+14 days')->format('Y-m-d');
        $start = fake()->randomElement(['09:00:00', '09:30:00', '10:00:00', '16:00:00', '16:30:00']);
        $end = date('H:i:s', strtotime($start.' +30 minutes'));
        $token = (string) Str::uuid();

        return [
            'appointment_no' => 'APT-DEMO-'.fake()->unique()->numerify('######'),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'department_id' => Department::factory(),
            'appointment_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'reason' => 'Demo follow-up visit (fictional)',
            'status' => 'pending',
            'notes' => 'Development/demo appointment only.',
            'source' => 'admin',
            'created_by' => null,
            'confirmation_token' => $token,
            'booking_key' => fake()->unique()->numerify('demo-slot-########'),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }
}
