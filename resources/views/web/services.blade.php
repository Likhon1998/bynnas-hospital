@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $heroImage = 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=2000&q=80';
    $steps = [
        ['title' => 'Choose a service', 'text' => 'Pick the care you need from emergency, clinics, or diagnostics.'],
        ['title' => 'Book a slot', 'text' => 'Share your preferred date and contact details with our desk.'],
        ['title' => 'Meet your doctor', 'text' => 'Arrive prepared and leave with a clear next step for recovery.'],
    ];
@endphp

@section('title', 'Services | '.$hospitalName)
@section('meta_description', 'Explore hospital services including emergency care, diagnostics, ICU, pharmacy, and specialist clinics.')

@section('content')
    <section class="relative bg-white">
        <div class="hero-shell relative min-h-[20rem] overflow-hidden sm:min-h-[24rem]">
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="hero-teal-overlay absolute inset-0"></div>

            <div class="relative z-10 mx-auto flex max-w-3xl flex-col items-center px-6 pb-16 pt-32 text-center sm:pb-20 sm:pt-36">
                <p class="reveal text-xs font-semibold uppercase tracking-[0.18em] text-white/75">Clinical care</p>
                <h1 class="reveal reveal-delay-1 mt-3 text-[2.2rem] font-bold leading-[1.12] tracking-tight text-white sm:text-5xl">
                    Our services
                </h1>
                <p class="reveal reveal-delay-2 mt-4 max-w-lg text-[0.95rem] leading-7 text-white/85">
                    From urgent care to specialist clinics and advanced diagnostics — complete hospital support under one roof.
                </p>
            </div>
        </div>
    </section>

    @if ($featuredServices->isNotEmpty())
        <section class="bg-white py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Featured care</p>
                        <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                            Services families rely on most
                        </h2>
                    </div>
                    <a href="{{ route('home') }}#appointment" class="text-sm font-semibold text-teal-700 transition hover:text-teal-800">
                        Book appointment →
                    </a>
                </div>

                <div class="mt-10 grid gap-5 lg:grid-cols-12 lg:gap-6">
                    @foreach ($featuredServices->take(3) as $index => $service)
                        <a
                            href="{{ route('services.show', $service) }}"
                            @class([
                                'service-tile group relative isolate overflow-hidden rounded-[1.75rem]',
                                'lg:col-span-7 min-h-[20rem] sm:min-h-[22rem]' => $index === 0,
                                'lg:col-span-5 min-h-[16rem] sm:min-h-[18rem]' => $index === 1,
                                'lg:col-span-12 min-h-[14rem] sm:min-h-[16rem]' => $index === 2,
                            ])
                        >
                            <img
                                src="{{ $service->image }}"
                                alt="{{ $service->title }}"
                                class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                            >
                            <div class="service-tile-overlay absolute inset-0"></div>
                            <div class="relative flex h-full flex-col justify-end p-6 sm:p-8">
                                @if ($service->department)
                                    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-white/70">
                                        {{ $service->department->name }}
                                    </p>
                                @endif
                                <h3 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                    {{ $service->title }}
                                </h3>
                                <p class="mt-2 max-w-xl text-sm leading-6 text-white/85">
                                    {{ $service->summary }}
                                </p>
                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white">
                                    View service
                                    <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-sand-50 py-14 sm:py-16">
        <div class="mx-auto max-w-6xl px-6 sm:px-8">
            <div class="max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">How it works</p>
                <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                    Simple steps to the right care
                </h2>
            </div>

            <div class="mt-10 grid gap-8 sm:grid-cols-3 sm:gap-10">
                @foreach ($steps as $index => $step)
                    <div class="border-t border-teal-700/25 pt-5">
                        <p class="text-sm font-semibold text-teal-700">0{{ $index + 1 }}</p>
                        <h3 class="mt-3 text-xl font-semibold tracking-tight text-navy-900">{{ $step['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-ink-600">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-6xl px-6 sm:px-8">
            <div class="max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">All services</p>
                <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                    Complete hospital support
                </h2>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <a
                        href="{{ route('services.show', $service) }}"
                        class="service-panel group flex flex-col overflow-hidden rounded-[1.5rem] transition duration-300 hover:-translate-y-1"
                    >
                        <div class="relative aspect-[16/10] overflow-hidden">
                            <img
                                src="{{ $service->image }}"
                                alt="{{ $service->title }}"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-teal-950/55 via-transparent to-transparent"></div>
                            @if ($service->is_featured)
                                <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-[0.7rem] font-semibold uppercase tracking-wide text-teal-800">
                                    Featured
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col px-5 py-5">
                            @if ($service->department)
                                <p class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-teal-700">
                                    {{ $service->department->name }}
                                </p>
                            @else
                                <p class="text-[0.7rem] font-semibold uppercase tracking-[0.12em] text-teal-700">
                                    Hospital care
                                </p>
                            @endif
                            <h3 class="mt-2 text-lg font-semibold tracking-tight text-navy-900">
                                {{ $service->title }}
                            </h3>
                            <p class="mt-2 flex-1 text-sm leading-6 text-ink-600">
                                {{ $service->summary }}
                            </p>
                            <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-700">
                                View details
                                <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if ($departments->isNotEmpty())
        <section class="bg-sand-50 py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">By department</p>
                    <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                        Find care in the right specialty
                    </h2>
                </div>

                <div class="mt-10 space-y-8">
                    @foreach ($departments as $department)
                        <div class="rounded-[1.5rem] bg-white px-5 py-6 ring-1 ring-teal-700/10 sm:px-7 sm:py-7">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-baseline sm:justify-between">
                                <h3 class="text-xl font-semibold tracking-tight text-navy-900">{{ $department->name }}</h3>
                                <p class="text-sm text-ink-600">{{ $department->services->count() }} services</p>
                            </div>
                            <ul class="mt-5 flex flex-wrap gap-2.5">
                                @foreach ($department->services as $service)
                                    <li>
                                        <a
                                            href="{{ route('services.show', $service) }}"
                                            class="inline-flex rounded-full border border-sand-100 bg-sand-50 px-4 py-2 text-sm font-medium text-ink-900 transition hover:border-teal-700/30 hover:bg-teal-50 hover:text-teal-800"
                                        >
                                            {{ $service->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="relative overflow-hidden text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-900 via-teal-800 to-navy-950"></div>
        <div class="relative mx-auto flex max-w-5xl flex-col items-start justify-between gap-6 px-6 py-14 sm:flex-row sm:items-center sm:px-8 sm:py-16">
            <div class="max-w-xl">
                <h2 class="text-[1.75rem] font-bold tracking-tight sm:text-3xl">
                    Need help choosing a service?
                </h2>
                <p class="mt-3 text-sm leading-7 text-white/80">
                    Our care desk can guide you to the right clinic, diagnostic test, or emergency pathway.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-ui.button href="{{ route('home') }}#appointment" variant="secondary">
                    Book Appointment
                </x-ui.button>
                @if ($hospital->get('phone') || $hospital->get('emergency_phone'))
                    <x-ui.button href="tel:{{ preg_replace('/\s+/', '', $hospital->get('emergency_phone') ?: $hospital->get('phone')) }}" variant="outline">
                        Call hospital
                    </x-ui.button>
                @endif
            </div>
        </div>
    </section>
@endsection
