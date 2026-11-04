<?php

namespace Database\Seeders;

use App\Models\HospitalAchievement;
use Illuminate\Database\Seeder;

class HospitalAchievementSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'year' => '2008',
                'title' => 'Campus founded',
                'description' => 'The hospital opened with consultant clinics and a night emergency desk.',
                'sort_order' => 1,
            ],
            [
                'year' => '2015',
                'title' => 'Diagnostic floor expanded',
                'description' => 'Lab and imaging moved onto one campus so reports stay close to the clinic.',
                'sort_order' => 2,
            ],
            [
                'year' => '2020',
                'title' => '24/7 emergency strengthened',
                'description' => 'Emergency staffing and triage pathways were upgraded for overnight arrivals.',
                'sort_order' => 3,
            ],
            [
                'year' => '2024',
                'title' => 'Digital visit desk',
                'description' => 'Appointments, check-in, and billing were connected through the hospital desk system.',
                'sort_order' => 4,
            ],
        ];

        foreach ($items as $item) {
            HospitalAchievement::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'year' => $item['year'],
                    'description' => $item['description'],
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );
        }
    }
}
