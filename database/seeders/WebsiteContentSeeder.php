<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Testimonial;
use App\Support\PublicPhoto;
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
                    'photo' => PublicPhoto::person($index),
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
                    'image' => PublicPhoto::campus($index),
                    'caption' => $item['caption'],
                    'album' => $item['album'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $faqs = [
            [
                'question' => 'Do I need an appointment?',
                'answer' => 'Emergency care is open around the clock. For clinics and planned visits, booking ahead keeps your wait shorter.',
            ],
            [
                'question' => 'Do you accept my insurance?',
                'answer' => 'Most major plans are accepted. Bring your insurance card and a photo ID to the desk.',
            ],
            [
                'question' => 'What should I prepare for my first visit?',
                'answer' => 'Bring prior prescriptions, reports, and a list of medicines you take now.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $banners = [
            [
                'title' => 'See a consultant. Leave with a plan.',
                'subtitle' => 'Book a named doctor, pick an open clinic slot, and get clear next steps at our Dhaka campus.',
                'cta_label' => 'Book a visit',
                'cta_url' => '/#appointment',
                'placement' => 'hero',
            ],
            [
                'title' => 'Emergency desk, day and night',
                'subtitle' => 'Call +880 1700-911911 or come straight to the emergency entrance.',
                'cta_label' => 'Emergency info',
                'cta_url' => '/emergency',
                'placement' => 'emergency',
            ],
            [
                'title' => 'Need a clinic slot this week?',
                'subtitle' => 'Pick a doctor and an open time. We confirm your visit by phone.',
                'cta_label' => 'Book a visit',
                'cta_url' => '/#appointment',
                'placement' => 'promo',
            ],
            [
                'title' => 'Consultants by clinic',
                'subtitle' => 'Browse by department, then book a free clinic time that fits you.',
                'cta_label' => 'Find a doctor',
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
                    'image' => PublicPhoto::campus($index),
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
