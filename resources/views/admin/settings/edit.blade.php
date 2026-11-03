@extends('layouts.admin')
@section('title', 'Hospital settings')
@section('heading', 'Website & hospital branding')
@section('content')
    <div class="patient-register">
        <section class="patient-register__hero">
            <div class="patient-register__intro">
                <p class="patient-register__eyebrow">Public website</p>
                <h2>Change the name, logo, and contact details</h2>
                <p>These values drive the public header, footer, emergency line, and printed slips. Manage homepage banners under Website → Banners.</p>
            </div>
            @can('content.manage')
                <div class="patient-register__callout">
                    <strong>Homepage banners</strong>
                    <span><a href="{{ route('admin.banners.index') }}" class="font-semibold text-teal-800 underline">Open banners</a> to change hero, emergency bar, and promo images.</span>
                </div>
            @endcan
        </section>

        <x-admin.form-page
            :action="route('admin.settings.update')"
            method="PUT"
            :cancel="route('admin.dashboard')"
            submit="Save branding"
            class="patient-register__form"
            enctype="multipart/form-data"
        >
            @php
                $general = $settings->get('general', collect());
                $byKey = $settings->flatten(1)->keyBy('key');
                $longKeys = ['about_content', 'emergency_content', 'privacy_content', 'terms_content', 'working_hours', 'map_embed'];
                $groupTitles = [
                    'contact' => 'Contact details',
                    'social' => 'Social links',
                    'content' => 'Website pages',
                ];
            @endphp

            <x-admin.form-section title="Brand identity" note="Hospital name and logo appear in the public header and footer." tone="teal" step="1">
                <x-form.input
                    name="settings[hospital_name]"
                    label="Hospital name"
                    :value="old('settings.hospital_name', $byKey->get('hospital_name')?->value)"
                    required
                />
                <x-form.input
                    name="settings[tagline]"
                    label="Website tagline"
                    :value="old('settings.tagline', $byKey->get('tagline')?->value)"
                    hint="Shown under the hospital name on the homepage."
                />

                <div class="form-span-2 doctor-photo-field" data-brand-logo>
                    <div class="doctor-photo-preview @if (! filled(old('settings.logo', $logo))) is-empty @endif" style="width: 12rem; height: 4.5rem; border-radius: 0.85rem;">
                        <img
                            data-brand-logo-preview
                            src="{{ old('settings.logo', $logo) ?: '' }}"
                            alt="Hospital logo"
                            @if (! filled(old('settings.logo', $logo))) hidden @endif
                            style="object-fit: contain; background: #fff; padding: 0.35rem;"
                        >
                        <span data-brand-logo-empty class="doctor-photo-preview__empty" @if (filled(old('settings.logo', $logo))) hidden @endif>No logo yet</span>
                    </div>
                    <x-form.input
                        name="logo_upload"
                        type="file"
                        label="Upload logo"
                        accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                        hint="PNG or SVG with a transparent background works best. Max 4 MB."
                        data-brand-logo-input
                    />
                    <x-form.input
                        name="settings[logo]"
                        label="Logo URL"
                        :value="old('settings.logo', $logo)"
                        hint="Optional if you upload a file. Paste an image link instead."
                        data-brand-logo-url
                    />
                </div>
            </x-admin.form-section>

            @foreach ($settings as $group => $items)
                @continue($group === 'general')
                <x-admin.form-section
                    :title="$groupTitles[$group] ?? str_replace('_', ' ', $group)"
                    :note="$group === 'contact' ? 'Shown in the top bar, footer, and emergency page.' : ($group === 'content' ? 'About, emergency, privacy, and terms pages.' : null)"
                >
                    @foreach ($items as $setting)
                        @if (in_array($setting->key, $longKeys, true))
                            <div class="form-span-2">
                                <x-form.textarea
                                    :name="'settings['.$setting->key.']'"
                                    :label="\App\Models\HospitalSetting::labelFor($setting->key)"
                                    :value="old('settings.'.$setting->key, $setting->value)"
                                    :rows="6"
                                />
                            </div>
                        @else
                            <x-form.input
                                :name="'settings['.$setting->key.']'"
                                :label="\App\Models\HospitalSetting::labelFor($setting->key)"
                                :value="old('settings.'.$setting->key, $setting->value)"
                            />
                        @endif
                    @endforeach
                </x-admin.form-section>
            @endforeach

            {{-- Keep any general keys beyond name/tagline/logo if present --}}
            @foreach ($general as $setting)
                @continue(in_array($setting->key, ['hospital_name', 'tagline', 'logo'], true))
                <input type="hidden" name="settings[{{ $setting->key }}]" value="{{ old('settings.'.$setting->key, $setting->value) }}">
            @endforeach

            @error('settings')
                <p class="text-sm text-red-700">{{ $message }}</p>
            @enderror
            @error('logo_upload')
                <p class="text-sm text-red-700">{{ $message }}</p>
            @enderror
        </x-admin.form-page>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-brand-logo]');
            if (!root) return;
            const preview = root.querySelector('[data-brand-logo-preview]');
            const empty = root.querySelector('[data-brand-logo-empty]');
            const box = root.querySelector('.doctor-photo-preview');
            const fileInput = root.querySelector('[data-brand-logo-input]');
            const urlInput = root.querySelector('[data-brand-logo-url]');

            const show = (src) => {
                if (!src) {
                    preview.hidden = true;
                    preview.removeAttribute('src');
                    empty.hidden = false;
                    box.classList.add('is-empty');
                    return;
                }
                preview.hidden = false;
                preview.src = src;
                empty.hidden = true;
                box.classList.remove('is-empty');
            };

            preview?.addEventListener('error', () => show(''));
            fileInput?.addEventListener('change', () => {
                const file = fileInput.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = () => show(String(reader.result || ''));
                reader.readAsDataURL(file);
            });
            urlInput?.addEventListener('input', () => {
                if (fileInput?.files?.length) return;
                show(urlInput.value.trim());
            });
        })();
    </script>
@endsection
