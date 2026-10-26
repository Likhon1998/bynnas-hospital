<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class PatientAndAppointmentSeeder extends Seeder
{
    /**
     * Fictional patients and schedule-safe demo appointments. Safe to re-run.
     */
    public function run(): void
    {
        $receptionist = User::query()->where('email', 'receptionist@hospital.com')->first();

        $patients = [
            [
                'full_name' => 'Rahim Uddin (Demo)',
                'phone' => '01700000001',
                'email' => 'rahim.uddin.demo@example.test',
                'date_of_birth' => '1984-03-12',
                'gender' => 'male',
            ],
            [
                'full_name' => 'Fatema Begum (Demo)',
                'phone' => '01700000002',
                'email' => 'fatema.begum.demo@example.test',
                'date_of_birth' => '1990-07-21',
                'gender' => 'female',
            ],
            [
                'full_name' => 'Karim Mia (Demo)',
                'phone' => '01700000003',
                'email' => 'karim.mia.demo@example.test',
                'date_of_birth' => '1976-11-02',
                'gender' => 'male',
            ],
            [
                'full_name' => 'Sadia Noor (Demo)',
                'phone' => '01700000004',
                'email' => 'sadia.noor.demo@example.test',
                'date_of_birth' => '1995-01-18',
                'gender' => 'female',
            ],
            [
                'full_name' => 'Jahidul Islam (Demo)',
                'phone' => '01700000005',
                'email' => 'jahidul.islam.demo@example.test',
                'date_of_birth' => '1988-09-09',
                'gender' => 'male',
            ],
        ];

        $patientIds = [];

        foreach ($patients as $patient) {
            $record = Patient::query()->updateOrCreate(
                ['email' => $patient['email']],
                [
                    'full_name' => $patient['full_name'],
                    'phone' => $patient['phone'],
                    'date_of_birth' => $patient['date_of_birth'],
                    'gender' => $patient['gender'],
                    'address' => 'House 00, Demo Road, Dhaka (fictional)',
                    'emergency_contact_name' => 'Emergency Contact (Demo)',
                    'emergency_contact_phone' => '01800000000',
                    'is_active' => true,
                ],
            );

            $patientIds[] = $record->id;
        }

        $appointments = [
            ['no' => 'APT-DEMO-0001', 'doctor' => 'dr-arif-rahman', 'patient' => 0, 'weekday' => 'saturday', 'start' => '09:00:00', 'status' => 'pending', 'when' => 'next'],
            ['no' => 'APT-DEMO-0002', 'doctor' => 'dr-nadia-karim', 'patient' => 1, 'weekday' => 'saturday', 'start' => '16:00:00', 'status' => 'confirmed', 'when' => 'next'],
            ['no' => 'APT-DEMO-0003', 'doctor' => 'dr-saif-hasan', 'patient' => 2, 'weekday' => 'saturday', 'start' => '09:30:00', 'status' => 'confirmed', 'when' => 'next'],
            ['no' => 'APT-DEMO-0004', 'doctor' => 'dr-tanvir-islam', 'patient' => 3, 'weekday' => 'sunday', 'start' => '09:00:00', 'status' => 'pending', 'when' => 'next'],
            ['no' => 'APT-DEMO-0005', 'doctor' => 'dr-farhana-ahmed', 'patient' => 4, 'weekday' => 'saturday', 'start' => '10:00:00', 'status' => 'confirmed', 'when' => 'next'],
            ['no' => 'APT-DEMO-0006', 'doctor' => 'dr-arif-rahman', 'patient' => 0, 'weekday' => 'sunday', 'start' => '16:00:00', 'status' => 'completed', 'when' => 'previous'],
            ['no' => 'APT-DEMO-0007', 'doctor' => 'dr-imran-chowdhury', 'patient' => 1, 'weekday' => 'tuesday', 'start' => '09:00:00', 'status' => 'completed', 'when' => 'previous'],
            ['no' => 'APT-DEMO-0008', 'doctor' => 'dr-laila-sultana', 'patient' => 2, 'weekday' => 'tuesday', 'start' => '09:30:00', 'status' => 'cancelled', 'when' => 'next'],
            ['no' => 'APT-DEMO-0009', 'doctor' => 'dr-rashed-khan', 'patient' => 3, 'weekday' => 'thursday', 'start' => '09:00:00', 'status' => 'no_show', 'when' => 'previous'],
            ['no' => 'APT-DEMO-0010', 'doctor' => 'dr-mehnaz-chowdhury', 'patient' => 4, 'weekday' => 'saturday', 'start' => '09:30:00', 'status' => 'confirmed', 'when' => 'next'],
        ];

        foreach ($appointments as $item) {
            $doctor = Doctor::query()->where('slug', $item['doctor'])->firstOrFail();
            $schedule = DoctorSchedule::query()
                ->where('doctor_id', $doctor->id)
                ->where('weekday', $item['weekday'])
                ->where('is_active', true)
                ->firstOrFail();

            $start = Carbon::createFromFormat('H:i:s', $item['start']);
            $end = $start->copy()->addMinutes((int) $schedule->slot_duration_minutes);
            $scheduleStart = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $scheduleEnd = Carbon::createFromFormat('H:i:s', $schedule->end_time);

            if ($start->lt($scheduleStart) || $end->gt($scheduleEnd)) {
                throw new RuntimeException("Demo appointment {$item['no']} is outside {$item['doctor']} schedule.");
            }

            if ($schedule->break_start && $schedule->break_end) {
                $breakStart = Carbon::createFromFormat('H:i:s', $schedule->break_start);
                $breakEnd = Carbon::createFromFormat('H:i:s', $schedule->break_end);

                if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                    throw new RuntimeException("Demo appointment {$item['no']} overlaps a break.");
                }
            }

            $date = $this->dateForWeekday($item['weekday'], $item['when']);
            $cancelled = $item['status'] === 'cancelled';
            $bookingKey = $cancelled ? null : sprintf('%d:%s:%s', $doctor->id, $date->toDateString(), $start->format('H:i'));

            $existing = Appointment::query()->where('appointment_no', $item['no'])->first();

            Appointment::query()->updateOrCreate(
                ['appointment_no' => $item['no']],
                [
                    'patient_id' => $patientIds[$item['patient']],
                    'doctor_id' => $doctor->id,
                    'department_id' => $doctor->department_id,
                    'appointment_date' => $date->toDateString(),
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'reason' => 'Demo visit for website testing (fictional)',
                    'status' => $item['status'],
                    'notes' => 'Development/demo appointment. Not a real clinical booking.',
                    'source' => 'admin',
                    'created_by' => $receptionist?->id,
                    'confirmation_token' => $existing?->confirmation_token ?? (string) Str::uuid(),
                    'booking_key' => $bookingKey,
                    'cancelled_at' => $cancelled ? now() : null,
                    'cancellation_reason' => $cancelled ? 'Cancelled in demo dataset' : null,
                ],
            );
        }
    }

    private function dateForWeekday(string $weekday, string $when): Carbon
    {
        $map = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ];

        $target = $map[$weekday];
        $date = now()->startOfDay();

        if ($when === 'previous') {
            if ($date->dayOfWeek === $target) {
                return $date->subWeek();
            }

            return $date->previous($target);
        }

        if ($date->dayOfWeek === $target) {
            return $date->addWeek();
        }

        return $date->next($target);
    }
}
