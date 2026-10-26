<?php

namespace Database\Seeders;

use App\Models\HospitalSetting;
use Illuminate\Database\Seeder;

class HospitalSettingSeeder extends Seeder
{
    /**
     * Development/demo hospital profile. Safe to re-run.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'hospital_name', 'group' => 'general', 'value' => DemoContent::HOSPITAL_NAME],
            ['key' => 'tagline', 'group' => 'general', 'value' => 'Compassionate care for every family'],
            ['key' => 'logo', 'group' => 'general', 'value' => DemoContent::image('Lakeside Logo', 400, 120)],
            ['key' => 'phone', 'group' => 'contact', 'value' => '+880 1700-000000'],
            ['key' => 'emergency_phone', 'group' => 'contact', 'value' => '+880 1700-911911'],
            ['key' => 'email', 'group' => 'contact', 'value' => 'hello@demo.hospital'],
            ['key' => 'address', 'group' => 'contact', 'value' => '12 Lakeside Avenue, Gulshan, Dhaka 1212, Bangladesh'],
            ['key' => 'working_hours', 'group' => 'contact', 'value' => json_encode([
                'saturday' => '9:00 AM – 9:00 PM',
                'sunday' => '9:00 AM – 9:00 PM',
                'monday' => '9:00 AM – 9:00 PM',
                'tuesday' => '9:00 AM – 9:00 PM',
                'wednesday' => '9:00 AM – 9:00 PM',
                'thursday' => '9:00 AM – 9:00 PM',
                'friday' => 'Emergency only (24/7)',
                'emergency' => '24/7',
            ], JSON_UNESCAPED_UNICODE)],
            ['key' => 'map_embed', 'group' => 'contact', 'value' => ''],
            ['key' => 'facebook_url', 'group' => 'social', 'value' => 'https://facebook.com'],
            ['key' => 'youtube_url', 'group' => 'social', 'value' => 'https://youtube.com'],
            ['key' => 'instagram_url', 'group' => 'social', 'value' => 'https://instagram.com'],
            ['key' => 'about_content', 'group' => 'content', 'value' => 'Lakeside General Hospital provides consultant clinics, diagnostics, and round-the-clock emergency support for patients and families in Dhaka.'],
            ['key' => 'emergency_content', 'group' => 'content', 'value' => 'The emergency desk is open day and night. Call +880 1700-911911 or come directly to the hospital.'],
            ['key' => 'privacy_content', 'group' => 'content', 'value' => 'Demo Privacy Policy. This site stores sample data for development. Do not submit real patient information.'],
            ['key' => 'terms_content', 'group' => 'content', 'value' => 'Demo Terms & Conditions for the Lakeside General Hospital website prototype.'],
        ];

        foreach ($settings as $setting) {
            HospitalSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'value' => $setting['value'],
                ],
            );
        }
    }
}
