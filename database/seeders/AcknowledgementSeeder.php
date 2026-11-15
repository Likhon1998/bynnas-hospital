<?php

namespace Database\Seeders;

use App\Models\Acknowledgement;
use Illuminate\Database\Seeder;

class AcknowledgementSeeder extends Seeder
{
    /**
     * Bangla ticker acknowledgements. Safe to re-run.
     */
    public function run(): void
    {
        $messages = [
            'আমাদের হাসপাতালে আপনাকে স্বাগতম। সেবাই আমাদের অঙ্গীকার।',
            'রোগী ও অভিভাবকদের সহযোগিতার জন্য আন্তরিক ধন্যবাদ।',
            'জরুরি সেবা ২৪ ঘণ্টা খোলা। প্রয়োজনে ইমার্জেন্সি ডেস্কে যোগাযোগ করুন।',
            'পরিষ্কার-পরিচ্ছন্নতা বজায় রাখতে সবাইকে অনুরোধ করা হচ্ছে।',
        ];

        foreach ($messages as $index => $message) {
            Acknowledgement::query()->updateOrCreate(
                ['message' => $message],
                [
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
