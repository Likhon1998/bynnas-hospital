<?php

namespace Database\Seeders;

use App\Models\DoctorTalk;
use Illuminate\Database\Seeder;

class DoctorTalkSeeder extends Seeder
{
    /**
     * Bangla health-awareness Doctor's Talk videos. Safe to re-run.
     */
    public function run(): void
    {
        $retiredEnglishTitles = [
            'Heart health basics for families',
            'When to visit Emergency',
            'Child fever: what parents should know',
            'Diabetes daily care tips',
            'Preparing for a consultant visit',
            'Blood pressure at home',
        ];

        DoctorTalk::query()
            ->whereIn('title', $retiredEnglishTitles)
            ->delete();

        $talks = [
            [
                'title' => 'হৃদরোগ সচেতনতায় করণীয় ও ডাক্তারের পরামর্শ',
                'doctor_name' => 'ডা. করিম',
                'youtube_url' => 'https://www.youtube.com/watch?v=VRyVWW_w-5A',
                'sort_order' => 1,
            ],
            [
                'title' => 'ডায়াবেটিসের লক্ষণ ও পরীক্ষার আগে করণীয়',
                'doctor_name' => 'ডা. নাহিদা',
                'youtube_url' => 'https://www.youtube.com/watch?v=jzO4TN5wL2w',
                'sort_order' => 2,
            ],
            [
                'title' => 'উচ্চ রক্তচাপের কারণ ও সতর্কতা',
                'doctor_name' => 'ডা. হাসান',
                'youtube_url' => 'https://www.youtube.com/watch?v=vGGO98gkYo8',
                'sort_order' => 3,
            ],
            [
                'title' => 'ব্লাড প্রেসার বেড়েছে না কমেছে—লক্ষণ ও প্রতিকার',
                'doctor_name' => 'ডা. আরেফিন',
                'youtube_url' => 'https://www.youtube.com/watch?v=Y3FGYLNkdR8',
                'sort_order' => 4,
            ],
            [
                'title' => 'ডায়াবেটিসের সহজ চিকিৎসা কীভাবে',
                'doctor_name' => 'ডা. জাহাঙ্গীর কবির',
                'youtube_url' => 'https://www.youtube.com/watch?v=Pv_KrU0kxjU',
                'sort_order' => 5,
            ],
            [
                'title' => 'অসুস্থতায় ঘরে বসেই ডাক্তারের পরামর্শ নেবেন যেভাবে',
                'doctor_name' => 'স্বাস্থ্য বাতায়ন',
                'youtube_url' => 'https://www.youtube.com/watch?v=SEGI_sI0NV0',
                'sort_order' => 6,
            ],
        ];

        foreach ($talks as $talk) {
            DoctorTalk::query()->updateOrCreate(
                ['title' => $talk['title']],
                [
                    'doctor_name' => $talk['doctor_name'],
                    'youtube_url' => $talk['youtube_url'],
                    'thumbnail' => null,
                    'is_active' => true,
                    'sort_order' => $talk['sort_order'],
                ],
            );
        }
    }
}
