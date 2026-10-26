@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $heroImage = $service->image
        ?: 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=2000&q=80';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
@endphp

@section('title', $service->title.' | '.$hospitalName)
@section('meta_description', $service->summary ?: ($service->title.' at '.$hospitalName))

@section('content')
    <section class="relative bg-white">
        <div class="hero-shell relative min-h-[20rem] overflow-hidden sm:min-h-[24rem]">
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="hero-teal-overlay absolute inset-0"></div>

            <div class="relative z-10 mx-auto max-w-4xl px-6 pb-16 pt-32 text-center sm:pb-20 sm:pt-36">
                <p class="reveal text-xs font-semibold uppercase tracking-[0.18em] text-white/75">
                    {{ $service->department?->name ?: 'Hospital service' }}
                </p>
                <h1 class="reveal reveal-delay-1 mt-3 text-[2.2rem] font-bold leading-[1.12] tracking-tight text-white sm:text-5xl">
                    {{ $service->title }}
                </h1>
                @if ($service->summary)
                    <p class="reveal reveal-delay-2 mx-auto mt-4 max-w-2xl text-[0.95rem] leading-7 text-white/85">
                        {{ $service->summary }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto grid max-w-6xl gap-10 px-6 sm:px-8 lg:grid-cols-[1.35fr_0.65fr] lg:gap-14">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">About this service</p>
                <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                    What to expect
                </h2>

                <div class="prose-service mt-6 space-y-4 text-sm leading-7 text-ink-600 sm:text-[0.95rem]">
                    @if (filled($service->body))
                        {!! $service->body !!}
                    @else
                        <p>{{ $service->summary }}</p>
                        <p>
                            Our clinical team guides you from assessment through treatment and follow-up, with clear communication at every step.
                        </p>
                    @endif
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <x-ui.button href="{{ route('home') }}#appointment" variant="outline-teal">
                        Book Appointment
                    </x-ui.button>
                    <a href="{{ route('services') }}" class="text-sm font-semibold text-teal-700 transition hover:text-teal-800">
                        ← All services
                    </a>
                </div>
            </div>

            <aside class="space-y-5">
                <div class="booking-bar rounded-[1.5rem] px-5 py-5 sm:px-6 sm:py-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Quick details</p>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="font-semibold text-ink-400">Service</dt>
                            <dd class="mt-1 font-medium text-navy-900">{{ $service->title }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-ink-400">Department</dt>
                            <dd class="mt-1 font-medium text-navy-900">{{ $service->department?->name ?: 'General hospital care' }}</dd>
                        </div>
                        @if ($phone)
                            <div>
                                <dt class="font-semibold text-ink-400">Contact</dt>
                                <dd class="mt-1">
                                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="font-medium text-teal-700 transition hover:text-teal-800">
                                        {{ $phone }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                    <div class="mt-6">
                        <x-ui.button href="{{ route('home') }}#appointment" variant="navy" class="w-full">
                            Book this service
                        </x-ui.button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[1.5rem]">
                    <img
                        src="{{ $heroImage }}"
                        alt="{{ $service->title }}"
                        class="aspect-[5/4] w-full object-cover"
                    >
                </div>
            </aside>
        </div>
    </section>

    @if ($departmentDoctors->isNotEmpty())
        <section class="bg-sand-50 py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Specialists</p>
                    <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                        Doctors related to this care
                    </h2>
                </div>

                <div class="mt-10 grid gap-7 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($departmentDoctors as $doctor)
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
                                {{ $doctor->designation ?: ($doctor->department?->name ?: 'Consultant') }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($relatedServices->isNotEmpty())
        <section class="bg-white py-14 sm:py-16">
            <div class="mx-auto max-w-6xl px-6 sm:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-700">Related care</p>
                        <h2 class="mt-3 text-[1.75rem] font-bold tracking-tight text-navy-900 sm:text-3xl">
                            You may also need
                        </h2>
                    </div>
                    <a href="{{ route('services') }}" class="text-sm font-semibold text-teal-700 transition hover:text-teal-800">
                        View all services →
                    </a>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-3">
                    @foreach ($relatedServices as $related)
                        <a
                            href="{{ route('services.show', $related) }}"
                            class="service-panel group flex flex-col overflow-hidden rounded-[1.5rem] transition duration-300 hover:-translate-y-1"
                        >
                            <div class="relative aspect-[16/10] overflow-hidden">
                                <img
                                    src="{{ $related->image }}"
                                    alt="{{ $related->title }}"
                                    class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                    loading="lazy"
                                >
                            </div>
                            <div class="flex flex-1 flex-col px-5 py-5">
                                <h3 class="text-lg font-semibold tracking-tight text-navy-900">{{ $related->title }}</h3>
                                <p class="mt-2 flex-1 text-sm leading-6 text-ink-600">{{ $related->summary }}</p>
                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-teal-700">
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

    <section class="relative overflow-hidden text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-teal-900 via-teal-800 to-navy-950"></div>
        <div class="relative mx-auto flex max-w-5xl flex-col items-start justify-between gap-6 px-6 py-14 sm:flex-row sm:items-center sm:px-8 sm:py-16">
            <div class="max-w-xl">
                <h2 class="text-[1.75rem] font-bold tracking-tight sm:text-3xl">
                    Ready to book {{ $service->title }}?
                </h2>
                <p class="mt-3 text-sm leading-7 text-white/80">
                    Share your preferred date and our care desk will confirm your appointment.
                </p>
            </div>
            <x-ui.button href="{{ route('home') }}#appointment" variant="secondary" class="shrink-0">
                Book Appointment
            </x-ui.button>
        </div>
    </section>
@endsection
