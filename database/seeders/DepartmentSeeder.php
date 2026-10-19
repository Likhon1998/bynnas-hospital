<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Development/demo departments. Safe to re-run by slug.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'icon' => 'heart',
                'is_featured' => true,
                'description' => 'Clinics for chest pain, blood pressure, and follow-up after a heart problem.',
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'icon' => 'brain',
                'is_featured' => true,
                'description' => 'Assessment of headache, stroke risk, seizures, and nerve-related symptoms.',
            ],
            [
                'name' => 'Orthopedics',
                'slug' => 'orthopedics',
                'icon' => 'bone',
                'is_featured' => false,
                'description' => 'Bone, joint, and sports-injury clinics, including fracture follow-up.',
            ],
            [
                'name' => 'Gynecology & Obstetrics',
                'slug' => 'gynecology-obstetrics',
                'icon' => 'person',
                'is_featured' => true,
                'description' => 'Women’s health, antenatal visits, and gynecology consultations.',
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'icon' => 'baby',
                'is_featured' => true,
                'description' => 'Child health clinics for growth, vaccination advice, and common childhood illness.',
            ],
            [
                'name' => 'General Medicine',
                'slug' => 'general-medicine',
                'icon' => 'stethoscope',
                'is_featured' => false,
                'description' => 'Consultant care for fever, diabetes review, and general adult medicine.',
            ],
            [
                'name' => 'General Surgery',
                'slug' => 'general-surgery',
                'icon' => 'scalpel',
                'is_featured' => false,
                'description' => 'Surgical consultation and assessment before an operation.',
            ],
            [
                'name' => 'Dermatology',
                'slug' => 'dermatology',
                'icon' => 'sparkles',
                'is_featured' => false,
                'description' => 'Outpatient clinics for skin, hair, and allergy-related concerns.',
            ],
            [
                'name' => 'ENT',
                'slug' => 'ent',
                'icon' => 'ear',
                'is_featured' => false,
                'description' => 'Ear, nose, and throat consultations, including sinus and hearing visits.',
            ],
            [
                'name' => 'Urology',
                'slug' => 'urology',
                'icon' => 'activity',
                'is_featured' => false,
                'description' => 'Consultant clinics for urinary symptoms and men’s health.',
            ],
        ];

        foreach ($departments as $index => $department) {
            Department::query()->updateOrCreate(
                ['slug' => $department['slug']],
                [
                    'name' => $department['name'],
                    'description' => $department['description'],
                    'icon' => $department['icon'],
                    'image' => DemoContent::image($department['name']),
                    'is_featured' => $department['is_featured'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
