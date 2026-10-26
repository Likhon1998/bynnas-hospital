@extends('layouts.admin')
@section('title', 'Hospital settings')
@section('heading', 'Hospital settings')
@section('content')
    <x-admin.form-page
        :action="route('admin.settings.update')"
        method="PUT"
        :cancel="route('admin.dashboard')"
        submit="Save settings"
        lede="These values appear on the public website and in printed bills. Keep contact details and working hours current."
    >
        @foreach ($settings as $group => $items)
            @php
                $longKeys = ['about_content', 'emergency_content', 'privacy_content', 'terms_content', 'working_hours', 'map_embed'];
            @endphp
            <x-admin.form-section :title="str_replace('_', ' ', $group)">
                @foreach ($items as $setting)
                    @if (in_array($setting->key, $longKeys, true))
                        <div class="form-span-2">
                            <x-form.textarea :name="'settings['.$setting->key.']'" :label="str_replace('_', ' ', $setting->key)" :value="$setting->value" :rows="6" />
                        </div>
                    @else
                        <x-form.input :name="'settings['.$setting->key.']'" :label="str_replace('_', ' ', $setting->key)" :value="$setting->value" />
                    @endif
                @endforeach
            </x-admin.form-section>
        @endforeach
        @error('settings')
            <p class="text-sm text-red-700">{{ $message }}</p>
        @enderror
    </x-admin.form-page>
@endsection
