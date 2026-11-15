<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    /**
     * Demo hospital notices. Safe to re-run.
     */
    public function run(): void
    {
        $notices = [
            [
                'title' => 'Emergency desk: bring one attendant only',
                'body' => 'To keep corridors clear for stretchers, only one attendant may enter the triage bay with each patient.',
                'published_on' => now()->toDateString(),
                'is_urgent' => true,
                'sort_order' => 0,
            ],
            [
                'title' => 'Blood bank walk-in hours this week',
                'body' => 'Voluntary donors are welcome Saturday to Thursday, 9:00 AM to 4:00 PM. Emergency requests continue 24/7.',
                'published_on' => now()->toDateString(),
                'is_urgent' => false,
                'sort_order' => 1,
            ],
            [
                'title' => 'OPD ticket counters open from 8:00 AM',
                'body' => 'Please collect a token before your consultant visit. Bring a national ID or previous report folder.',
                'published_on' => now()->subDays(1)->toDateString(),
                'is_urgent' => false,
                'sort_order' => 2,
            ],
            [
                'title' => 'Pharmacy counter closes for stock check at 1:30 PM',
                'body' => 'Prescriptions resume at 2:15 PM. Emergency medicines remain available from the triage bay.',
                'published_on' => now()->subDays(1)->toDateString(),
                'is_urgent' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Diagnostic reports ready after 4:00 PM',
                'body' => 'Same-day lab reports can be collected from the diagnostics desk with your invoice and ID.',
                'published_on' => now()->subDays(2)->toDateString(),
                'is_urgent' => false,
                'sort_order' => 4,
            ],
            [
                'title' => 'Flu vaccination camp — October',
                'body' => 'Free flu shots were offered in the OPD lobby throughout October. The camp has now closed.',
                'published_on' => now()->subMonths(1)->startOfMonth()->toDateString(),
                'expires_on' => now()->subMonths(1)->endOfMonth()->toDateString(),
                'is_urgent' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($notices as $notice) {
            Notice::query()->updateOrCreate(
                ['title' => $notice['title']],
                [
                    'body' => $notice['body'],
                    'published_on' => $notice['published_on'],
                    'expires_on' => $notice['expires_on'] ?? null,
                    'is_urgent' => $notice['is_urgent'],
                    'sort_order' => $notice['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
