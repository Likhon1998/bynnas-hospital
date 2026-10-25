@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $address = $hospital->get('address');
    $phone = $hospital->get('phone');
    $emergency = $hospital->get('emergency_phone');
    $heroImage = 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1600&q=80';
    $serviceList = $services->isNotEmpty() ? $services : collect();
    $bookableList = isset($bookableServices) && $bookableServices->isNotEmpty() ? $bookableServices : $serviceList;
@endphp

@section('title', $hospitalName)
@section('meta_description', $hospitalName.' in Gulshan, Dhaka. Clinics, diagnostics, and a 24-hour emergency desk.')

@section('content')
    <section class="bg-[#f7f5ef]">
        <div class="web-wrap grid items-center gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
            <div class="max-w-xl">
                @if ($address)
                    <p class="web-kicker">{{ $address }}</p>
                @endif
                <h1 class="web-title mt-3">{{ $hospitalName }}</h1>
                <p class="web-lead mt-5">
                    A hospital in Gulshan for clinic visits, tests, and emergency. If you are not sure which doctor to see, call the OPD desk first. They will point you to the right room.
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <x-ui.button href="#appointment" variant="navy" class="!rounded">
                        Request a visit
                    </x-ui.button>
                    @if ($emergency)
                        <a href="tel:{{ preg_replace('/\s+/', '', $emergency) }}" class="text-sm font-semibold text-[#9a3b32]">
                            Emergency {{ $emergency }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="min-h-[16rem] overflow-hidden border border-[#ddd8cc] bg-[#c5cdc8] lg:min-h-[22rem]">
                <img
                    src="{{ $heroImage }}"
                    alt="Ward corridor at {{ $hospitalName }}"
                    referrerpolicy="no-referrer"
                    class="h-full min-h-[16rem] w-full object-cover lg:min-h-[22rem]"
                >
            </div>
        </div>
    </section>

    <section id="appointment" class="border-y border-[#ddd8cc] bg-[#fbfaf6]">
        <div class="web-wrap web-section--tight">
            <div class="max-w-2xl">
                <h2 class="web-h2">Ask the desk for a slot</h2>
                <p class="web-copy mt-3">
                    Leave your name, a service, a date, and a number. Reception sees this request in the desk list and will call you back. This is not an instant confirmation.
                </p>
            </div>
            <form action="{{ route('appointment-requests.store') }}" method="POST" class="mt-6 grid gap-4 border border-[#ddd8cc] bg-white p-5 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                @csrf
                <label class="web-field">
                    <span>Your name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="is-error">{{ $message }}</span> @enderror
                </label>
                <label class="web-field">
                    <span>Phone</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                    @error('phone') <span class="is-error">{{ $message }}</span> @enderror
                </label>
                <label class="web-field">
                    <span>Service</span>
                    <select name="service" required>
                        <option value="">Choose one</option>
                        @foreach ($bookableList as $serviceItem)
                            <option value="{{ $serviceItem->slug }}" @selected(old('service', request('service')) === $serviceItem->slug)>{{ $serviceItem->title }}</option>
                        @endforeach
                    </select>
                    @error('service') <span class="is-error">{{ $message }}</span> @enderror
                </label>
                <label class="web-field">
                    <span>Preferred date</span>
                    <input type="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" required>
                    @error('date') <span class="is-error">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="web-submit w-full lg:w-auto">Send request</button>
            </form>
        </div>
    </section>

    <section id="services" class="bg-[#f7f5ef]">
        <div class="web-wrap web-section grid items-start gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:gap-16">
            <div class="max-w-sm">
                <h2 class="web-h2">What we do here</h2>
                <p class="web-copy mt-4">
                    Most people come for a consultant, a lab test, or the emergency desk. Click a line to read how that service works.
                </p>
                <a href="{{ route('services') }}" class="web-link mt-5 inline-block">See every service</a>
            </div>
            <ul class="border-t border-[#ddd8cc]">
                @forelse ($serviceList as $serviceItem)
                    <li class="border-b border-[#ddd8cc]">
                        <a href="{{ route('services.show', $serviceItem) }}" class="flex items-baseline justify-between gap-6 py-3.5 text-[#1c332c] hover:text-[#0a5858]">
                            <span class="min-w-0">{{ $serviceItem->title }}</span>
                            <span class="shrink-0 text-sm text-[#2f433c]">{{ $serviceItem->department?->name }}</span>
                        </a>
                    </li>
                @empty
                    <li class="py-3.5">
                        <a href="{{ route('services') }}" class="web-link">View all services</a>
                    </li>
                @endforelse
            </ul>
        </div>
    </section>

    <section class="border-y border-[#ddd8cc] bg-[#fbfaf6]">
        <div class="web-wrap web-section--tight grid grid-cols-2 gap-x-8 gap-y-6 sm:grid-cols-4">
            <p class="web-stat">
                <strong>{{ number_format($stats['doctors'] ?? 0) }}</strong>
                <span>Doctors on the roster</span>
            </p>
            <p class="web-stat">
                <strong>{{ number_format($stats['departments'] ?? 0) }}</strong>
                <span>Departments</span>
            </p>
            <p class="web-stat">
                <strong>{{ number_format($stats['services'] ?? 0) }}</strong>
                <span>Hospital services</span>
            </p>
            <p class="web-stat">
                <strong>24/7</strong>
                <span>Emergency desk</span>
            </p>
        </div>
    </section>

    @if ($doctors->isNotEmpty())
        <section class="bg-[#f7f5ef]">
            <div class="web-wrap web-section">
                <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
                    <h2 class="web-h2">Some of our consultants</h2>
                    <a href="{{ route('about') }}" class="web-link">About the hospital</a>
                </div>
                <div class="mt-8 grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($doctors->take(4) as $doctor)
                        <article class="web-person">
                            <img
                                src="{{ filled($doctor->photo) ? $doctor->photo : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80' }}"
                                alt="{{ $doctor->name }}"
                                referrerpolicy="no-referrer"
                                class="aspect-[4/5] w-full border border-[#ddd8cc] bg-[#c5cdc8] object-cover object-top"
                                loading="lazy"
                            >
                            <h3 class="web-h3">{{ $doctor->name }}</h3>
                            <p class="web-muted">{{ $doctor->department?->name ?: $doctor->designation }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($phone)
        <section class="border-t border-[#ddd8cc] bg-[#efeae0]">
            <div class="web-wrap web-section--tight flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="web-copy max-w-2xl">
                    For a regular appointment call {{ $phone }}. For an accident or sudden illness, go straight to emergency.
                </p>
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="web-link shrink-0">
                    Call the OPD desk
                </a>
            </div>
        </section>
    @endif
@endsection
