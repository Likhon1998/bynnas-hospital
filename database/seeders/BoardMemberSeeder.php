<?php

namespace Database\Seeders;

use App\Models\BoardMember;
use App\Support\PublicPhoto;
use Illuminate\Database\Seeder;

class BoardMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Prof. Dr. Nazmul Haque',
                'title' => 'Chairman',
                'role_label' => 'Board of Directors',
                'bio' => 'Leads hospital governance and long-term clinical standards for the campus.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Engr. Farhana Rahman',
                'title' => 'Managing Director',
                'role_label' => 'Executive leadership',
                'bio' => 'Oversees operations, patient experience, and day-to-day campus management.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dr. Imran Kabir',
                'title' => 'Director, Clinical Services',
                'role_label' => 'Board of Directors',
                'bio' => 'Coordinates consultant quality, emergency readiness, and clinical pathways.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Ms. Sadia Chowdhury',
                'title' => 'Director, Finance & Admin',
                'role_label' => 'Board of Directors',
                'bio' => 'Guides billing integrity, approvals, and transparent hospital finances.',
                'sort_order' => 4,
            ],
        ];

        foreach ($members as $index => $member) {
            BoardMember::query()->updateOrCreate(
                ['name' => $member['name']],
                [
                    'title' => $member['title'],
                    'role_label' => $member['role_label'],
                    'bio' => $member['bio'],
                    'photo' => PublicPhoto::person($index),
                    'email' => null,
                    'phone' => null,
                    'is_active' => true,
                    'sort_order' => $member['sort_order'],
                ],
            );
        }
    }
}
