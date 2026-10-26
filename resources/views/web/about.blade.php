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

    <section class="site-block">
        <div class="web-wrap grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="max-w-xl">
                <div class="site-section-head">
                    <h2>Our story</h2>
                </div>
                <p class="text-[0.95rem] leading-7 text-[#475569]">{{ $aboutContent }}</p>
                <p class="mt-4 text-[0.95rem] leading-7 text-[#475569]">
                    The building is ordinary on purpose. Reception is on the ground floor. If you have old papers, bring them. It saves a second visit.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <a href="{{ route('home') }}#appointment" class="site-btn site-btn--green">Request a visit</a>
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-sm font-semibold text-[#16a34a]">
                            Call {{ $phone }}
                        </a>
                    @endif
                </div>
            </div>
            <img
                src="{{ $storyImage }}"
                alt="A doctor at {{ $hospitalName }}"
                referrerpolicy="no-referrer"
                class="aspect-[5/4] w-full rounded-[1.5rem] bg-[#dbe4f0] object-cover object-center shadow-[0_24px_50px_-36px_rgb(15_23_42_/_0.55)]"
            >
        </div>
    </section>

    <section class="site-block site-block--soft">
        <div class="web-wrap">
            <div class="site-section-head">
                <h2>How we try to work</h2>
            </div>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($values as $value)
                    <div class="rounded-[1.35rem] border border-[#e2e8f0] bg-white p-5 shadow-[0_16px_34px_-32px_rgb(15_23_42_/_0.4)]">
                        <h3 class="text-base font-bold text-[#122447]">{{ $value['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-[#64748b]">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-stats">
        <div class="web-wrap site-stats__row">
            @foreach ($statItems as $stat)
                <div>
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    @if ($doctors->isNotEmpty())
        <section class="site-block">
            <div class="web-wrap">
                <div class="site-section-head flex flex-wrap items-end justify-between gap-4">
                    <h2>Consultants you may meet</h2>
                    <a href="{{ route('services') }}" class="text-sm font-semibold text-[#16a34a]">Explore services</a>
                </div>
                <div class="site-doctors site-doctors--grid">
                    @foreach ($doctors as $doctor)
                        <article class="site-doctor">
                            <div class="site-doctor__photo">
                                <img
                                    src="{{ filled($doctor->photo) ? $doctor->photo : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80' }}"
                                    alt="Portrait of {{ $doctor->name }}"
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                >
                            </div>
                            <div class="site-doctor__card">
                                <h3>{{ $doctor->name }}</h3>
                                <p>{{ $doctor->department?->name ?: ($doctor->designation ?: 'Consultant') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($departments->isNotEmpty())
        <section class="site-block site-block--soft">
            <div class="web-wrap">
                <div class="site-section-head">
                    <h2>Departments in the building</h2>
                </div>
                <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($departments as $department)
                        <li class="rounded-xl border border-[#e2e8f0] bg-white px-4 py-3 font-semibold text-[#122447]">{{ $department->name }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
