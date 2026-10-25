@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $aboutContent = $hospital->get('about_content')
        ?: 'We run consultant clinics, a diagnostic floor, and an emergency desk that stays open at night.';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $storyImage = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1200&q=80';
    $values = [
        [
            'title' => 'Say the plan out loud',
            'text' => 'Before a test or admission, the doctor should tell you what happens next in ordinary words. If that does not happen, ask the desk.',
        ],
        [
            'title' => 'Keep labs in the building',
            'text' => 'Reports should not travel across town if we can help it. Clinics and diagnostics sit on the same campus.',
        ],
        [
            'title' => 'Someone is always at emergency',
            'text' => 'The night desk does not close on Friday. If it is urgent, come in. Do not wait for OPD hours.',
        ],
    ];
    $statItems = [
        ['value' => (string) ($stats['doctors'] ?: 0), 'label' => 'Doctors'],
        ['value' => (string) ($stats['departments'] ?: 0), 'label' => 'Departments'],
        ['value' => (string) ($stats['services'] ?: 0), 'label' => 'Services'],
        ['value' => 'Night', 'label' => 'Emergency cover'],
    ];
@endphp

@section('title', 'About us | '.$hospitalName)
@section('meta_description', 'How '.$hospitalName.' works, who practises here, and how to reach the desk.')

@section('content')
    <x-web.page-intro
        kicker="About the hospital"
        title="About us"
        :note="$hospitalName.' is in Gulshan. Families come here for a named consultant, a blood test, or because something cannot wait until morning.'"
    />

    <section class="bg-[#f7f5ef]">
        <div class="web-wrap web-section grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="max-w-xl">
                <h2 class="web-h2">Our story</h2>
                <p class="web-copy mt-4">{{ $aboutContent }}</p>
                <p class="web-copy mt-4">
                    The building is ordinary on purpose. Reception is on the ground floor. If you have old papers, bring them. It saves a second visit.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <x-ui.button href="{{ route('home') }}#appointment" variant="outline-teal" class="!rounded">
                        Request a visit
                    </x-ui.button>
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="web-link">
                            Call {{ $phone }}
                        </a>
                    @endif
                </div>
            </div>
            <img
                src="{{ $storyImage }}"
                alt="A doctor at {{ $hospitalName }}"
                referrerpolicy="no-referrer"
                class="aspect-[5/4] w-full border border-[#ddd8cc] bg-[#c5cdc8] object-cover object-center"
            >
        </div>
    </section>

    <section class="border-y border-[#ddd8cc] bg-[#fbfaf6]">
        <div class="web-wrap web-section">
            <h2 class="web-h2">How we try to work</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-3 sm:gap-10">
                @foreach ($values as $value)
                    <div class="border-t border-[#c9c2b2] pt-4">
                        <h3 class="web-h3">{{ $value['title'] }}</h3>
                        <p class="web-copy mt-3">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#f7f5ef]">
        <div class="web-wrap web-section--tight grid grid-cols-2 gap-x-8 gap-y-6 sm:grid-cols-4">
            @foreach ($statItems as $stat)
                <p class="web-stat">
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </p>
            @endforeach
        </div>
    </section>

    @if ($doctors->isNotEmpty())
        <section class="border-t border-[#ddd8cc] bg-[#fbfaf6]">
            <div class="web-wrap web-section">
                <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
                    <h2 class="web-h2">Consultants you may meet</h2>
                    <a href="{{ route('services') }}" class="web-link">Explore services</a>
                </div>
                <div class="mt-8 grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($doctors as $doctor)
                        <article class="web-person">
                            <img
                                src="{{ filled($doctor->photo) ? $doctor->photo : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80' }}"
                                alt="Portrait of {{ $doctor->name }}"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                class="aspect-[4/5] w-full border border-[#ddd8cc] bg-[#c5cdc8] object-cover object-top"
                            >
                            <h3 class="web-h3">{{ $doctor->name }}</h3>
                            <p class="web-muted">
                                {{ $doctor->department?->name ?: ($doctor->designation ?: 'Consultant') }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($departments->isNotEmpty())
        <section class="border-t border-[#ddd8cc] bg-[#f7f5ef]">
            <div class="web-wrap web-section">
                <h2 class="web-h2">Departments in the building</h2>
                <ul class="mt-6 grid gap-x-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($departments as $department)
                        <li class="border-b border-[#ddd8cc] py-3 text-[#1c332c]">{{ $department->name }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
