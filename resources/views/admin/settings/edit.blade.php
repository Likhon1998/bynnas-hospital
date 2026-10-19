@extends('layouts.admin')
@section('title', 'Hospital settings')
@section('heading', 'Hospital settings')
@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')
        @foreach ($settings as $group => $items)
            <section class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
                <h2 class="font-display text-xl capitalize text-navy-900">{{ str_replace('_', ' ', $group) }}</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($items as $setting)
                        @php
                            $isLong = in_array($setting->key, ['about_content', 'emergency_content', 'privacy_content', 'terms_content', 'working_hours', 'map_embed'], true);
                        @endphp
                        @if ($isLong)
                            <x-form.textarea :name="'settings['.$setting->key.']'" :label="str_replace('_', ' ', $setting->key)" :value="$setting->value" :rows="6" />
                        @else
                            <x-form.input :name="'settings['.$setting->key.']'" :label="str_replace('_', ' ', $setting->key)" :value="$setting->value" />
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
        @error('settings') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save settings</button>
    </form>
@endsection
