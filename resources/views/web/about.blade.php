@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $aboutContent = $hospital->get('about_content')
        ?: 'We provide consultant clinics, diagnostics, and round-the-clock emergency support for patients and families.';
    $tagline = $hospital->get('tagline') ?: 'Compassionate care for every family';
    $heroImage = 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?auto=format&fit=crop&w=2000&q=80';
    $storyImage = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1200&q=80';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $values = [
        [
            'title' => 'Patient-first care',
            'text' => 'Every visit is built around clear guidance, gentle support, and treatment plans families can trust.',
        ],
        [
            'title' => 'Skilled specialists',
            'text' => 'Our consultants collaborate across departments so diagnosis and recovery stay connected.',
        ],
        [
            'title' => 'Always available',
            'text' => 'Emergency desks, clinics, and diagnostics stay ready when patients need us most.',
        ],
    ];
    $statItems = [
        ['value' => ($stats['doctors'] ?: 67).'+', 'label' => 'Qualified Doctors'],
        ['value' => ($stats['departments'] ?: 12).'+', 'label' => 'Departments'],
        ['value' => ($stats['services'] ?: 40).'+', 'label' => 'Clinical Services'],
        ['value' => ($stats['patients'] ?: 5000).'+', 'label' => 'Patients Served'],
    ];
@endphp

@section('title', 'About us | '.$hospitalName)
@section('meta_description', $tagline.' Learn about '.$hospitalName.' and the care we provide.')

@section('content')
    <section class="relative bg-white">
        <div class="hero-shell relative min-h-[18rem] overflow-hidden sm:min-h-[22rem]">
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="hero-teal-overlay absolute inset-0"></div>

            <div class="relative z-10 mx-auto flex max-w-3xl flex-col items-center px-6 pb-16 pt-32 text-center sm:pb-20 sm:pt-36">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/75">Who we are</p>
                <h1 class="mt-3 text-[2.1rem] font-bold leading-[1.15] tracking-tight text-white sm:text-4xl">
                    About us
                </h1>
                <p class="mt-4 max-w-md text-[0.95rem] leading-7 text-white/85">
                    {{ $tagline }}
                </p>
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-6 sm:px-8 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Our story</p>
                <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                    A hospital built around recovery and trust
                </h2>
                <p class="mt-5 text-sm leading-7 text-ink-600 sm:text-[0.95rem]">
                    {{ $aboutContent }}
                </p>
                <p class="mt-4 text-sm leading-7 text-ink-600 sm:text-[0.95rem]">
                    From first consultation to follow-up care, our teams keep communication simple and clinical standards high so every patient feels guided, safe, and supported.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-4">
                    <x-ui.button href="{{ route('home') }}#appointment" variant="outline-teal">
                        Book Appointment
                    </x-ui.button>
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-sm font-semibold text-teal-700 transition hover:text-teal-800">
                            Call {{ $phone }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-3 rounded-[1.75rem] bg-gradient-to-br from-teal-50 to-sand-100"></div>
                <img
                    src="{{ $storyImage }}"
                    alt="Hospital care team"
                    class="relative aspect-[5/4] w-full rounded-[1.5rem] object-cover object-center shadow-[0_20px_45px_-28px_rgb(13_71_71_/_0.4)]"
                >
            </div>
        </div>
    </section>

    <section class="bg-sand-50 py-14 sm:py-16">
        <div class="mx-auto max-w-6xl px-6 sm:px-8">
            <div class="max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">What guides us</p>
                <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                    Care that stays clear and human
                </h2>
            </div>

            <div class="mt-10 grid gap-8 sm:grid-cols-3 sm:gap-10">
                @foreach ($values as $index => $value)
                    <div class="border-t border-teal-700/25 pt-5">
                        <p class="text-sm font-semibold text-teal-700">0{{ $index + 1 }}</p>
                        <h3 class="mt-3 text-xl font-semibold tracking-tight text-navy-900">{{ $value['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-ink-600">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-12 sm:py-14">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-8 px-6 sm:grid-cols-4 sm:gap-6 sm:px-8">
            @foreach ($statItems as $stat)
                <div class="text-center sm:text-left">
                    <p class="text-3xl font-bold tracking-tight text-teal-700">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm font-medium text-ink-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    @if ($doctors->isNotEmpty())
        <section class="bg-sand-50 py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Our team</p>
                        <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                            Meet the consultants behind your care
                        </h2>
                    </div>
                    <a href="{{ route('services') }}" class="text-sm font-semibold text-teal-700 transition hover:text-teal-800">
                        Explore services →
                    </a>
                </div>

                <div class="mt-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($doctors as $doctor)
                        <article>
                            <div class="overflow-hidden rounded-2xl bg-teal-50">
                                <img
                                    src="{{ filled($doctor->photo) ? $doctor->photo : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80' }}"
                                    alt="Portrait of {{ $doctor->name }}"
                                    loading="lazy"
                                    class="aspect-[4/5] w-full object-cover object-top"
                                >
                            </div>
                            <h3 class="mt-3.5 text-base font-semibold text-navy-900">{{ $doctor->name }}</h3>
                            <p class="mt-1 text-sm text-ink-600">
                                {{ $doctor->department?->name ?: ($doctor->designation ?: 'Consultant') }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($departments->isNotEmpty())
        <section class="bg-white py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Departments</p>
                    <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                        Specialties ready when you need them
                    </h2>
                </div>

                <ul class="mt-8 grid gap-x-10 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($departments as $department)
                        <li class="border-b border-sand-100 py-3.5 text-[0.95rem] font-medium text-ink-900">
                            {{ $department->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="relative overflow-hidden text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-900 via-teal-800 to-navy-950"></div>
        <div class="relative mx-auto flex max-w-5xl flex-col items-start justify-between gap-6 px-6 py-14 sm:flex-row sm:items-center sm:px-8 sm:py-16">
            <div class="max-w-xl">
                <h2 class="text-[1.75rem] font-bold tracking-tight sm:text-3xl">
                    Ready for your next consultation?
                </h2>
                <p class="mt-3 text-sm leading-7 text-white/80">
                    Book with our team and get clear guidance from specialists who put recovery first.
                </p>
            </div>
            <x-ui.button href="{{ route('home') }}#appointment" variant="secondary" class="shrink-0">
                Book Appointment
            </x-ui.button>
        </div>
    </section>
@endsection
