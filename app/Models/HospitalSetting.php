<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable([
    'key',
    'value',
    'group',
])]
class HospitalSetting extends Model
{
    /**
     * @return Collection<string, string|null>
     */
    public static function map(): Collection
    {
        return static::query()->pluck('value', 'key');
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'hospital_name' => 'Hospital name',
            'tagline' => 'Website tagline',
            'logo' => 'Logo URL',
            'phone' => 'Phone',
            'emergency_phone' => 'Emergency phone',
            'email' => 'Email',
            'address' => 'Address',
            'campus_name' => 'Campus name',
            'working_hours' => 'Working hours (JSON)',
            'map_embed' => 'Map embed HTML / URL',
            'facebook_url' => 'Facebook URL',
            'youtube_url' => 'YouTube URL',
            'instagram_url' => 'Instagram URL',
            'about_content' => 'About page content',
            'emergency_content' => 'Emergency page content',
            'privacy_content' => 'Privacy policy',
            'terms_content' => 'Terms & conditions',
        ];
    }

    /**
     * @return list<array{key: string, group: string, value: string}>
     */
    public static function defaultDefinitions(): array
    {
        return [
            ['key' => 'hospital_name', 'group' => 'general', 'value' => 'Hospital'],
            ['key' => 'tagline', 'group' => 'general', 'value' => 'Consultant clinics and emergency care in Dhaka'],
            ['key' => 'logo', 'group' => 'general', 'value' => ''],
            ['key' => 'phone', 'group' => 'contact', 'value' => ''],
            ['key' => 'emergency_phone', 'group' => 'contact', 'value' => ''],
            ['key' => 'email', 'group' => 'contact', 'value' => ''],
            ['key' => 'address', 'group' => 'contact', 'value' => ''],
            ['key' => 'campus_name', 'group' => 'contact', 'value' => ''],
            ['key' => 'working_hours', 'group' => 'contact', 'value' => '{}'],
            ['key' => 'map_embed', 'group' => 'contact', 'value' => ''],
            ['key' => 'facebook_url', 'group' => 'social', 'value' => ''],
            ['key' => 'youtube_url', 'group' => 'social', 'value' => ''],
            ['key' => 'instagram_url', 'group' => 'social', 'value' => ''],
            ['key' => 'about_content', 'group' => 'content', 'value' => ''],
            ['key' => 'emergency_content', 'group' => 'content', 'value' => ''],
            ['key' => 'privacy_content', 'group' => 'content', 'value' => ''],
            ['key' => 'terms_content', 'group' => 'content', 'value' => ''],
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (static::defaultDefinitions() as $definition) {
            static::query()->firstOrCreate(
                ['key' => $definition['key']],
                [
                    'group' => $definition['group'],
                    'value' => $definition['value'],
                ],
            );
        }
    }

    public static function labelFor(string $key): string
    {
        return static::labels()[$key] ?? str_replace('_', ' ', $key);
    }
}
