<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Development/demo services. Safe to re-run by slug.
     */
    public function run(): void
    {
        $services = [
            ['title' => 'Emergency Care', 'slug' => 'emergency-care', 'department' => null, 'featured' => true, 'summary' => 'A 24/7 desk for urgent assessment and immediate treatment.'],
            ['title' => 'ICU', 'slug' => 'icu', 'department' => 'general-medicine', 'featured' => true, 'summary' => 'Intensive care for patients who need close monitoring.'],
            ['title' => 'CCU', 'slug' => 'ccu', 'department' => 'cardiology', 'featured' => true, 'summary' => 'Cardiac care for heart emergencies and recovery.'],
            ['title' => 'NICU', 'slug' => 'nicu', 'department' => 'pediatrics', 'featured' => true, 'summary' => 'Specialist care for newborns who need extra support.'],
            ['title' => 'Ambulance', 'slug' => 'ambulance', 'department' => null, 'featured' => true, 'summary' => 'Request an ambulance through the emergency desk.'],
            ['title' => 'Laboratory', 'slug' => 'laboratory', 'department' => 'general-medicine', 'featured' => false, 'summary' => 'Pathology tests and sample collection on site.'],
            ['title' => 'ECG', 'slug' => 'ecg', 'department' => 'cardiology', 'featured' => false, 'summary' => 'Electrocardiogram testing for heart rhythm and chest symptoms.'],
            ['title' => 'X-Ray', 'slug' => 'x-ray', 'department' => 'orthopedics', 'featured' => false, 'summary' => 'Digital X-ray for bones, chest, and injury review.'],
            ['title' => 'Ultrasound', 'slug' => 'ultrasound', 'department' => 'gynecology-obstetrics', 'featured' => false, 'summary' => 'Ultrasound imaging by appointment.'],
            ['title' => 'CT Scan', 'slug' => 'ct-scan', 'department' => 'neurology', 'featured' => false, 'summary' => 'CT imaging for detailed views of the head, chest, and abdomen.'],
            ['title' => 'MRI', 'slug' => 'mri', 'department' => 'neurology', 'featured' => false, 'summary' => 'MRI scans for brain, spine, and joint assessment.'],
            ['title' => 'Physiotherapy', 'slug' => 'physiotherapy', 'department' => 'orthopedics', 'featured' => false, 'summary' => 'Rehabilitation for injury, surgery, and mobility.'],
            ['title' => 'Pharmacy', 'slug' => 'pharmacy', 'department' => null, 'featured' => false, 'summary' => 'An in-house pharmacy for prescribed medicines.'],
            ['title' => 'Blood Bank', 'slug' => 'blood-bank', 'department' => null, 'featured' => false, 'summary' => 'Blood products available for emergency and planned care.'],
            ['title' => 'Health Checkup', 'slug' => 'health-checkup', 'department' => 'general-medicine', 'featured' => true, 'summary' => 'Structured screening packages with clear prices.'],
        ];

        foreach ($services as $index => $service) {
            $departmentId = null;

            if ($service['department']) {
                $departmentId = Department::query()->where('slug', $service['department'])->value('id');
            }

            Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                [
                    'department_id' => $departmentId,
                    'title' => $service['title'],
                    'summary' => $service['summary'],
                    'body' => '<p>'.$service['summary'].'</p>',
                    'icon' => $service['slug'],
                    'image' => ServicePhotos::forSlug($service['slug'], $index),
                    'is_featured' => $service['featured'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
