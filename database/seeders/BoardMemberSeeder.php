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
                'name' => 'Prof. Dr. Anwar Hossain',
                'slug' => 'prof-dr-anwar-hossain',
                'title' => 'Founder',
                'role' => 'founder',
                'role_label' => 'Founding leadership',
                'bio' => 'Founded the campus with a focus on named consultants and an emergency desk that never closes.',
                'achievements' => [
                    'Established the main campus',
                    'Opened 24/7 emergency care',
                    'Built the first consultant roster',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Mrs. Nasrin Akter',
                'slug' => 'mrs-nasrin-akter',
                'title' => 'Co-founder',
                'role' => 'co_founder',
                'role_label' => 'Founding leadership',
                'bio' => 'Co-founded the hospital and still guides patient experience and community outreach.',
                'achievements' => [
                    'Led patient experience standards',
                    'Launched community health camps',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Prof. Dr. Nazmul Haque',
                'slug' => 'prof-dr-nazmul-haque',
                'title' => 'Chairman',
                'role' => 'chairman',
                'role_label' => 'Board of Directors',
                'bio' => 'Leads hospital governance and long-term clinical standards for the campus.',
                'achievements' => [
                    'Strengthened clinical governance',
                    'Expanded specialty clinics',
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Engr. Farhana Rahman',
                'slug' => 'engr-farhana-rahman',
                'title' => 'Managing Director',
                'role' => 'managing_director',
                'role_label' => 'Executive leadership',
                'bio' => 'Oversees operations, patient experience, and day-to-day campus management.',
                'achievements' => [
                    'Modernised reception and billing desks',
                ],
                'sort_order' => 4,
            ],
            [
                'name' => 'Dr. Imran Kabir',
                'slug' => 'dr-imran-kabir',
                'title' => 'Director, Clinical Services',
                'role' => 'director',
                'role_label' => 'Board of Directors',
                'bio' => 'Coordinates consultant quality, emergency readiness, and clinical pathways.',
                'achievements' => [],
                'sort_order' => 5,
            ],
            [
                'name' => 'Ms. Sadia Chowdhury',
                'slug' => 'ms-sadia-chowdhury',
                'title' => 'Director, Finance & Admin',
                'role' => 'director',
                'role_label' => 'Board of Directors',
                'bio' => 'Guides billing integrity, approvals, and transparent hospital finances.',
                'achievements' => [],
                'sort_order' => 6,
            ],
        ];

        foreach ($members as $index => $member) {
            BoardMember::query()->updateOrCreate(
                ['slug' => $member['slug']],
                [
                    'name' => $member['name'],
                    'title' => $member['title'],
                    'role' => $member['role'],
                    'role_label' => $member['role_label'],
                    'bio' => $member['bio'],
                    'achievements' => $member['achievements'],
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
