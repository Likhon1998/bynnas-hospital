<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HospitalSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.hospital_name' => ['required', 'string', 'max:255'],
            'settings.tagline' => ['nullable', 'string', 'max:255'],
            'settings.logo' => ['nullable', 'string', 'max:2048'],
            'settings.phone' => ['nullable', 'string', 'max:255'],
            'settings.emergency_phone' => ['nullable', 'string', 'max:255'],
            'settings.email' => ['nullable', 'string', 'max:255'],
            'settings.address' => ['nullable', 'string', 'max:2000'],
            'settings.campus_name' => ['nullable', 'string', 'max:255'],
            'settings.working_hours' => ['nullable', 'string', 'max:5000'],
            'settings.map_embed' => ['nullable', 'string', 'max:10000'],
            'settings.facebook_url' => ['nullable', 'string', 'max:2048'],
            'settings.youtube_url' => ['nullable', 'string', 'max:2048'],
            'settings.instagram_url' => ['nullable', 'string', 'max:2048'],
            'settings.about_content' => ['nullable', 'string', 'max:50000'],
            'settings.emergency_content' => ['nullable', 'string', 'max:50000'],
            'settings.privacy_content' => ['nullable', 'string', 'max:50000'],
            'settings.terms_content' => ['nullable', 'string', 'max:50000'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif,svg', 'max:4096'],
        ];
    }
}
