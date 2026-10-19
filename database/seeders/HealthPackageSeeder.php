<?php

namespace Database\Seeders;

use App\Models\HealthPackage;
use App\Models\HealthPackageItem;
use Illuminate\Database\Seeder;

class HealthPackageSeeder extends Seeder
{
    /**
     * Development/demo packages. Safe to re-run by slug.
     */
    public function run(): void
    {
        $packages = [
            [
                'title' => 'Basic Health Checkup',
                'slug' => 'basic-health-checkup',
                'price' => 3500,
                'duration_label' => 'Same day',
                'featured' => true,
                'description' => 'A same-day screening visit with essential blood and urine tests.',
                'items' => ['Doctor consultation', 'CBC', 'Blood sugar (fasting)', 'Urine R/E', 'Blood pressure check'],
            ],
            [
                'title' => 'Executive Health Checkup',
                'slug' => 'executive-health-checkup',
                'price' => 12500,
                'duration_label' => 'Half day',
                'featured' => true,
                'description' => 'A half-day checkup covering heart, liver, kidney, and imaging tests.',
                'items' => ['Consultant review', 'CBC & lipid profile', 'Liver function tests', 'Kidney function tests', 'ECG', 'Chest X-ray', 'Ultrasound whole abdomen'],
            ],
            [
                'title' => 'Diabetes Screening',
                'slug' => 'diabetes-screening',
                'price' => 4500,
                'duration_label' => '2 hours',
                'featured' => false,
                'description' => 'Blood tests and diet counselling focused on diabetes risk.',
                'items' => ['HbA1c', 'Fasting blood sugar', '2-hour after meal sugar', 'Urine microalbumin', 'Diet counselling session'],
            ],
            [
                'title' => 'Cardiac Health Checkup',
                'slug' => 'cardiac-health-checkup',
                'price' => 8900,
                'duration_label' => 'Half day',
                'featured' => true,
                'description' => 'A cardiology checkup with consultation, ECG, and blood tests.',
                'items' => ['Cardiology consultation', 'ECG', 'Echocardiogram', 'Lipid profile', 'Blood pressure monitoring'],
            ],
            [
                'title' => "Women's Health Checkup",
                'slug' => 'womens-health-checkup',
                'price' => 7500,
                'duration_label' => 'Half day',
                'featured' => false,
                'description' => 'A women’s health visit with consultation, blood tests, and ultrasound.',
                'items' => ['Gynecology consultation', 'CBC', 'TSH', 'Pelvic ultrasound', 'Pap smear counselling'],
            ],
        ];

        foreach ($packages as $index => $package) {
            $record = HealthPackage::query()->updateOrCreate(
                ['slug' => $package['slug']],
                [
                    'title' => $package['title'],
                    'price' => $package['price'],
                    'duration_label' => $package['duration_label'],
                    'description' => $package['description'],
                    'image' => DemoContent::image($package['title']),
                    'is_featured' => $package['featured'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );

            $record->healthPackageItems()->delete();

            foreach ($package['items'] as $itemIndex => $title) {
                HealthPackageItem::query()->updateOrCreate(
                    [
                        'health_package_id' => $record->id,
                        'title' => $title,
                    ],
                    ['sort_order' => $itemIndex + 1],
                );
            }
        }
    }
}
