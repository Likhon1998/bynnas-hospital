<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    /**
     * Fictional demo doctors and weekly schedules. Safe to re-run.
     */
    public function run(): void
    {
        $doctorUser = User::query()->where('email', 'doctor@demo.hospital')->first();

        $doctors = [
            [
                'name' => 'Dr. Arif Rahman',
                'department' => 'cardiology',
                'designation' => 'Senior Consultant',
                'qualifications' => 'MBBS, MD (Cardiology) — Demo credentials',
                'specialization' => 'Interventional Cardiology',
                'experience_years' => 18,
                'registration_number' => 'DEMO-REG-C-1001',
                'consultation_fee' => 2000,
                'featured' => true,
                'link_demo_user' => true,
                'schedules' => [
                    ['weekday' => 'saturday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'sunday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                ],
            ],
            [
                'name' => 'Dr. Omar Faruk',
                'department' => 'cardiology',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, D-Card — Demo credentials',
                'specialization' => 'Heart Failure',
                'experience_years' => 12,
                'registration_number' => 'DEMO-REG-C-1002',
                'consultation_fee' => 1600,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'monday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'wednesday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Imran Chowdhury',
                'department' => 'neurology',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, MD (Neurology) — Demo credentials',
                'specialization' => 'Stroke and Headache',
                'experience_years' => 14,
                'registration_number' => 'DEMO-REG-N-1003',
                'consultation_fee' => 1800,
                'featured' => true,
                'schedules' => [
                    ['weekday' => 'tuesday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'thursday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                ],
            ],
            [
                'name' => 'Dr. Saif Hasan',
                'department' => 'orthopedics',
                'designation' => 'Senior Consultant',
                'qualifications' => 'MBBS, MS (Ortho) — Demo credentials',
                'specialization' => 'Joint Replacement',
                'experience_years' => 16,
                'registration_number' => 'DEMO-REG-O-1004',
                'consultation_fee' => 1800,
                'featured' => true,
                'schedules' => [
                    ['weekday' => 'saturday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'monday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Nayeem Hasan',
                'department' => 'orthopedics',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, D-Ortho — Demo credentials',
                'specialization' => 'Sports Injury',
                'experience_years' => 9,
                'registration_number' => 'DEMO-REG-O-1005',
                'consultation_fee' => 1400,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'sunday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'wednesday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Nadia Karim',
                'department' => 'gynecology-obstetrics',
                'designation' => 'Senior Consultant',
                'qualifications' => 'MBBS, FCPS (OBGYN) — Demo credentials',
                'specialization' => 'High-risk Pregnancy',
                'experience_years' => 15,
                'registration_number' => 'DEMO-REG-G-1006',
                'consultation_fee' => 1800,
                'featured' => true,
                'schedules' => [
                    ['weekday' => 'saturday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                    ['weekday' => 'tuesday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                ],
            ],
            [
                'name' => 'Dr. Anika Rahman',
                'department' => 'gynecology-obstetrics',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, MCPS — Demo credentials',
                'specialization' => 'General Gynecology',
                'experience_years' => 8,
                'registration_number' => 'DEMO-REG-G-1007',
                'consultation_fee' => 1400,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'thursday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'sunday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Farhana Ahmed',
                'department' => 'pediatrics',
                'designation' => 'Senior Consultant',
                'qualifications' => 'MBBS, FCPS (Pediatrics) — Demo credentials',
                'specialization' => 'Child Nutrition',
                'experience_years' => 13,
                'registration_number' => 'DEMO-REG-P-1008',
                'consultation_fee' => 1500,
                'featured' => true,
                'schedules' => [
                    ['weekday' => 'saturday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'wednesday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                ],
            ],
            [
                'name' => 'Dr. Tasnim Jahan',
                'department' => 'pediatrics',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, DCH — Demo credentials',
                'specialization' => 'Neonatal Follow-up',
                'experience_years' => 7,
                'registration_number' => 'DEMO-REG-P-1009',
                'consultation_fee' => 1200,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'monday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => null, 'break_end' => null],
                    ['weekday' => 'thursday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                ],
            ],
            [
                'name' => 'Dr. Tanvir Islam',
                'department' => 'general-medicine',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, FCPS (Medicine) — Demo credentials',
                'specialization' => 'Diabetes and Hypertension',
                'experience_years' => 11,
                'registration_number' => 'DEMO-REG-M-1010',
                'consultation_fee' => 1300,
                'featured' => true,
                'schedules' => [
                    ['weekday' => 'sunday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'tuesday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Kabir Hossain',
                'department' => 'general-medicine',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, MD (Medicine) — Demo credentials',
                'specialization' => 'Internal Medicine',
                'experience_years' => 10,
                'registration_number' => 'DEMO-REG-M-1011',
                'consultation_fee' => 1200,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'wednesday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'saturday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                ],
            ],
            [
                'name' => 'Dr. Sharmin Akter',
                'department' => 'general-surgery',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, MS (Surgery) — Demo credentials',
                'specialization' => 'Laparoscopic Surgery',
                'experience_years' => 12,
                'registration_number' => 'DEMO-REG-S-1012',
                'consultation_fee' => 1700,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'monday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'thursday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Laila Sultana',
                'department' => 'dermatology',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, DDV — Demo credentials',
                'specialization' => 'Clinical Dermatology',
                'experience_years' => 9,
                'registration_number' => 'DEMO-REG-D-1013',
                'consultation_fee' => 1400,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'tuesday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                    ['weekday' => 'friday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Mehnaz Chowdhury',
                'department' => 'ent',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, FCPS (ENT) — Demo credentials',
                'specialization' => 'Sinus and Hearing',
                'experience_years' => 10,
                'registration_number' => 'DEMO-REG-E-1014',
                'consultation_fee' => 1500,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'wednesday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                    ['weekday' => 'saturday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => null, 'break_end' => null],
                ],
            ],
            [
                'name' => 'Dr. Rashed Khan',
                'department' => 'urology',
                'designation' => 'Consultant',
                'qualifications' => 'MBBS, MS (Urology) — Demo credentials',
                'specialization' => 'General Urology',
                'experience_years' => 11,
                'registration_number' => 'DEMO-REG-U-1015',
                'consultation_fee' => 1600,
                'featured' => false,
                'schedules' => [
                    ['weekday' => 'sunday', 'start_time' => '16:00:00', 'end_time' => '20:00:00', 'break_start' => '18:00:00', 'break_end' => '18:20:00'],
                    ['weekday' => 'thursday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start' => '11:00:00', 'break_end' => '11:20:00'],
                ],
            ],
        ];

        foreach ($doctors as $index => $data) {
            $department = Department::query()->where('slug', $data['department'])->firstOrFail();
            $slug = Str::slug($data['name']);

            $doctor = Doctor::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => ($data['link_demo_user'] ?? false) ? $doctorUser?->id : null,
                    'department_id' => $department->id,
                    'name' => $data['name'],
                    'photo' => DoctorPhotos::forIndex($index),
                    'designation' => $data['designation'],
                    'qualifications' => $data['qualifications'],
                    'specialization' => $data['specialization'],
                    'experience_years' => $data['experience_years'],
                    'registration_number' => $data['registration_number'],
                    'consultation_fee' => $data['consultation_fee'],
                    'biography' => $data['name'].' is a consultant at Lakeside General Hospital, seeing patients in '.$data['specialization'].'.',
                    'is_featured' => $data['featured'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );

            foreach ($data['schedules'] as $schedule) {
                DoctorSchedule::query()->updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'weekday' => $schedule['weekday'],
                        'start_time' => $schedule['start_time'],
                    ],
                    [
                        'end_time' => $schedule['end_time'],
                        'break_start' => $schedule['break_start'],
                        'break_end' => $schedule['break_end'],
                        'slot_duration_minutes' => 30,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
