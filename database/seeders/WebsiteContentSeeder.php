<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Gallery;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    /**
     * Demo banners, gallery, and testimonials. Safe to re-run.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'display_name' => 'Rahima Akter',
                'display_role' => 'Patient, Dhaka',
                'quote' => 'The team explained each step clearly, and booking a consultant was straightforward.',
                'rating' => 5,
            ],
            [
                'display_name' => 'Mahmud Hasan',
                'display_role' => 'Cardiology patient',
                'quote' => 'I could review the cardiology consultants and choose a time that suited my family.',
                'rating' => 5,
            ],
            [
                'display_name' => 'Nusrat Jahan',
                'display_role' => 'Parent',
                'quote' => 'The pediatrics clinic was calm, and the doctor took time to answer our questions.',
                'rating' => 4,
            ],
            [
                'display_name' => 'Sajid Khan',
                'display_role' => 'Outpatient',
                'quote' => 'Appointment slots were easy to understand, and the visit started on time.',
                'rating' => 5,
            ],
            [
                'display_name' => 'Lamia Chowdhury',
                'display_role' => 'Health checkup guest',
                'quote' => 'The health packages made it simple to compare what each checkup includes.',
                'rating' => 4,
            ],
            [
                'display_name' => 'Tareq Islam',
                'display_role' => 'Emergency visitor',
                'quote' => 'The emergency number was easy to find, and the desk responded immediately.',
                'rating' => 5,
            ],
        ];

        foreach ($testimonials as $index => $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['sort_order' => $index + 1],
                [
                    'display_name' => $testimonial['display_name'],
                    'display_role' => $testimonial['display_role'],
                    'quote' => $testimonial['quote'],
                    'rating' => $testimonial['rating'],
                    'photo' => DemoContent::image($testimonial['display_name'], 200, 200),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $gallery = [
            ['title' => 'Main lobby (demo)', 'album' => 'Facilities', 'caption' => 'Placeholder lobby image for the gallery grid.'],
            ['title' => 'OPD corridor (demo)', 'album' => 'Facilities', 'caption' => 'Fictional outpatient corridor.'],
            ['title' => 'Consultation room (demo)', 'album' => 'Facilities', 'caption' => 'Sample consultation-room placeholder.'],
            ['title' => 'Laboratory desk (demo)', 'album' => 'Diagnostics', 'caption' => 'Demo laboratory photo placeholder.'],
            ['title' => 'Community health day (demo)', 'album' => 'Events', 'caption' => 'Fictional event photo for the Events album.'],
            ['title' => 'Nursing station (demo)', 'album' => 'Facilities', 'caption' => 'Placeholder used for masonry layout testing.'],
        ];

        foreach ($gallery as $index => $item) {
            Gallery::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'image' => DemoContent::image($item['title']),
                    'caption' => $item['caption'],
                    'album' => $item['album'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $banners = [
            [
                'title' => 'Trusted Care for Every Family',
                'subtitle' => 'Consultant care, diagnostics, and emergency support for every family.',
                'cta_label' => 'Book Appointment',
                'cta_url' => '/appointments',
                'placement' => 'hero',
            ],
            [
                'title' => '24/7 Emergency Desk',
                'subtitle' => 'Call +880 1700-911911 or come directly to the emergency desk.',
                'cta_label' => 'Emergency Info',
                'cta_url' => '/emergency',
                'placement' => 'emergency',
            ],
            [
                'title' => 'Executive Health Checkup',
                'subtitle' => 'Compare screening packages with clear inclusions and prices.',
                'cta_label' => 'View Packages',
                'cta_url' => '/health-packages',
                'placement' => 'promo',
            ],
            [
                'title' => 'Meet Our Consultants',
                'subtitle' => 'Browse specialists by department and book a convenient clinic time.',
                'cta_label' => 'Find a Doctor',
                'cta_url' => '/doctors',
                'placement' => 'hero',
            ],
        ];

        foreach ($banners as $index => $banner) {
            Banner::query()->updateOrCreate(
                ['sort_order' => $index + 1],
                [
                    'title' => $banner['title'],
                    'subtitle' => $banner['subtitle'],
                    'image' => DemoContent::image($banner['title'], 1600, 700),
                    'cta_label' => $banner['cta_label'],
                    'cta_url' => $banner['cta_url'],
                    'placement' => $banner['placement'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            );
        }
    }
}
