@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $heroImage = 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=2000&q=80';
    $servicesImage = 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=2000&q=80';
    $serviceList = $services->isNotEmpty()
        ? $services
        : collect();
    $activeServiceIndex = min(1, max(0, $serviceList->count() - 1));
@endphp

@section('title', $hospitalName.' | Care for Yourself')
@section('meta_description', 'A great place to care for yourself. Medical recovery focused on helping you feel your best.')

@section('content')
    <section id="about" class="relative bg-white pb-10">
        <div class="hero-shell relative min-h-[34rem] overflow-hidden sm:min-h-[40rem]">
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="hero-teal-overlay absolute inset-0"></div>

            <div class="relative z-10 mx-auto flex max-w-3xl flex-col items-center px-6 pb-24 pt-36 text-center sm:pb-28 sm:pt-40">
                <h1 class="text-[2.35rem] font-bold leading-[1.15] tracking-tight text-white sm:text-5xl lg:text-[3.5rem]">
                    A Great Place<br>
                    care for yourself
                </h1>
                <p class="mt-5 max-w-sm text-[0.95rem] leading-7 text-white/85">
                    Medical recover is most focused in helping you discover your most beautiful smile.
                </p>
            </div>
        </div>

        <div class="relative z-30 px-4 pt-10 sm:px-6 sm:pt-12">
            <form
                id="appointment"
                action="#appointment"
                method="get"
                class="booking-bar mx-auto grid max-w-5xl gap-0 rounded-[2rem] px-4 py-5 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-center sm:rounded-full sm:px-6 sm:py-5"
            >
                <label class="flex min-w-0 flex-col gap-1 border-b border-sand-100 px-3 py-3 sm:border-b-0 sm:border-r sm:py-1">
                    <span class="text-[0.7rem] font-semibold uppercase tracking-wide text-ink-400">Choose Services</span>
                    <span class="relative">
                        <select name="service" class="w-full appearance-none bg-transparent py-1 pr-7 text-sm font-semibold text-ink-900 outline-none">
                            <option value="">Select a service</option>
                            @foreach ($serviceList as $serviceItem)
                                <option value="{{ $serviceItem->slug }}">{{ $serviceItem->title }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    </span>
                </label>

                <label class="flex min-w-0 flex-col gap-1 border-b border-sand-100 px-3 py-3 sm:border-b-0 sm:border-r sm:py-1">
                    <span class="text-[0.7rem] font-semibold uppercase tracking-wide text-ink-400">Choose Date</span>
                    <span class="relative">
                        <input
                            type="text"
                            name="date"
                            placeholder="DD/MM/YYYY"
                            onfocus="this.type='date'"
                            onblur="if(!this.value)this.type='text'"
                            class="w-full bg-transparent py-1 pr-7 text-sm font-semibold text-ink-900 outline-none placeholder:text-ink-900"
                        >
                        <svg class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M6 21h12a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                </label>

                <label class="flex min-w-0 flex-col gap-1 border-b border-sand-100 px-3 py-3 sm:border-b-0 sm:border-r sm:py-1">
                    <span class="text-[0.7rem] font-semibold uppercase tracking-wide text-ink-400">Contact Number</span>
                    <span class="relative">
                        <input
                            type="tel"
                            name="phone"
                            placeholder="+91 958 727 9122"
                            class="w-full bg-transparent py-1 pr-7 text-sm font-semibold text-ink-900 outline-none placeholder:text-ink-900"
                        >
                        <svg class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.2a1 1 0 01.95.68l1.1 3.2a1 1 0 01-.27 1.1L7.9 9.9a12 12 0 006.2 6.2l1.92-1.08a1 1 0 011.1-.27l3.2 1.1a1 1 0 01.68.95V19a2 2 0 01-2 2h-.2C10.4 21 3 13.6 3 5.2V5z"/></svg>
                    </span>
                </label>

                <div class="flex items-center justify-center px-2 py-3 sm:py-0 sm:pl-4">
                    <x-ui.button type="submit" variant="outline-teal" class="w-full whitespace-nowrap sm:w-auto">
                        Book Appointment
                    </x-ui.button>
                </div>
            </form>
        </div>
    </section>

    <section id="services" class="relative overflow-hidden text-white">
        <img src="{{ $servicesImage }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="services-teal-overlay absolute inset-0"></div>

        <div class="relative mx-auto grid max-w-6xl gap-12 px-6 py-20 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16 lg:py-24">
            <div class="max-w-md">
                <h2 class="text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-[2.75rem] lg:leading-[1.2]">
                    See what we provide to keep you healthy
                </h2>
                <p class="mt-6 text-sm leading-7 text-white/80 sm:text-[0.95rem]">
                    Medical recover is most focused in helping you discover your most beautiful smile and restoring confidence through complete hospital care.
                </p>
            </div>

            <div class="relative pr-6">
                <span class="service-rail" aria-hidden="true"></span>
                <ul>
                    @forelse ($serviceList as $index => $serviceItem)
                        <li class="relative border-b border-white/20 last:border-b-0">
                            <a
                                href="{{ route('services.show', $serviceItem) }}"
                                @class([
                                    'block py-3.5 text-[1.05rem] transition sm:text-lg',
                                    'font-semibold text-white underline decoration-white decoration-1 underline-offset-8' => $index === $activeServiceIndex,
                                    'font-medium text-white/85 hover:text-white' => $index !== $activeServiceIndex,
                                ])
                            >
                                {{ $serviceItem->title }}
                            </a>
                            @if ($index === $activeServiceIndex)
                                <span class="service-dot" aria-hidden="true"></span>
                            @endif
                        </li>
                    @empty
                        <li class="relative border-b border-white/20 py-3.5">
                            <a href="{{ route('services') }}" class="text-[1.05rem] font-medium text-white/85 transition hover:text-white sm:text-lg">
                                View all services
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="relative flex justify-center pb-10">
            <a href="#stats" class="text-white/85 transition hover:text-white" aria-label="Scroll to statistics">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.35">
                    <rect x="9" y="2.5" width="6" height="11" rx="3"></rect>
                    <path stroke-linecap="round" d="M12 5.5v2.5"></path>
                </svg>
            </a>
        </div>
    </section>

    <section id="stats" class="bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-5xl gap-14 px-6 sm:grid-cols-3 sm:gap-10">
            @php
                $statItems = [
                    ['value' => '67+', 'label' => 'Qualified Doctors', 'caption' => 'Experienced consultants across every major specialty.', 'progress' => 0.67],
                    ['value' => '99%', 'label' => 'Recover Patients', 'caption' => 'Care pathways designed for faster, safer recovery.', 'progress' => 0.99],
                    ['value' => '98%', 'label' => 'Satisfaction Rate', 'caption' => 'Families who trust our doctors and hospital team.', 'progress' => 0.98],
                ];
            @endphp

            @foreach ($statItems as $stat)
                <div class="flex flex-col items-center text-center">
                    <div class="stat-ring relative flex items-center justify-center" style="--progress: {{ $stat['progress'] }}">
                        <svg viewBox="0 0 120 120" class="h-full w-full" aria-hidden="true">
                            <circle class="stat-ring__track" cx="60" cy="60" r="48"></circle>
                            <circle class="stat-ring__value" cx="60" cy="60" r="48"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center px-4">
                            <p class="text-[2rem] font-bold tracking-tight text-teal-700">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-[0.7rem] font-semibold leading-4 text-ink-600">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                    <p class="mt-5 max-w-[13rem] text-xs leading-5 text-ink-400">{{ $stat['caption'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
