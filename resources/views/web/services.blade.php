@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $emergency = $hospital->get('emergency_phone') ?: $phone;
    $isFiltered = $selectedDepartment !== '';
    $visitSteps = [
        ['icon' => 'hospital', 'title' => 'Choose a service', 'text' => 'Pick emergency, a clinic, lab, or pharmacy. Call reception if you are unsure.'],
        ['icon' => 'calendar', 'title' => 'Book a time', 'text' => 'Request a visit online or by phone. Reception confirms your slot.'],
        ['icon' => 'clock', 'title' => 'Arrive prepared', 'text' => 'Bring ID, old prescriptions, and recent reports. First visits take a little longer.'],
    ];
@endphp

@section('title', 'Services | '.$hospitalName)
@section('meta_description', 'Clinics, diagnostics, and emergency care at '.$hospitalName.'. Browse every patient service in one place.')

@section('content')
    <section class="ch-svc-hero">
        <div class="ch-svc-hero__glow" aria-hidden="true"></div>
        <div class="ch-wrap">
            <p class="ch-find__kicker">Patient care</p>
            <div class="ch-clinics-hero__row ch-svc-hero__row">
                <div>
                    <h1>Our Services</h1>
                    <p>
                        @if ($isFiltered)
                            Services in {{ $selectedDepartment }}. Clear options for clinics, diagnostics, and urgent care.
                        @else
                            Emergency, diagnostics, pharmacy, and outpatient clinics — organised so you can find the right desk quickly.
                        @endif
                    </p>
                </div>
                <div class="ch-clinics-hero__actions">
                    @if ($isFiltered)
                        <a class="ch-btn" href="{{ route('services') }}">All services</a>
                    @endif
                    <a class="ch-btn ch-btn--solid" href="{{ route('home') }}#appointment">Book a visit</a>
                    @if ($emergency)
                        <a class="ch-btn ch-btn--ghost" href="{{ route('emergency') }}">Emergency</a>
                    @endif
                </div>
            </div>

            <div class="ch-svc-hero__meta">
                <p class="ch-clinics-hero__count">
                    {{ $services->count() }} {{ \Illuminate\Support\Str::plural('service', $services->count()) }}
                    @if ($isFiltered)
                        · in {{ $selectedDepartment }}
                    @else
                        · open for patients
                    @endif
                </p>
                @if ($phone)
                    <a class="ch-svc-hero__call" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">
                        Call reception {{ $phone }}
                    </a>
                @endif
            </div>

            @if ($departments->isNotEmpty())
                <div class="ch-svc-filters" role="navigation" aria-label="Filter services by department">
                    <p class="ch-svc-filters__label">Browse by department</p>
                    <div class="ch-find__chips ch-svc-filters__chips">
                        <a href="{{ route('services') }}" @class(['is-active' => ! $isFiltered])>All services</a>
                        @foreach ($departments as $department)
                            <a
                                href="{{ route('services', ['department' => $department->name]) }}"
                                @class(['is-active' => $selectedDepartment === $department->name])
                            >{{ $department->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="ch-care ch-care--page">
        <div class="ch-wrap">
            <div class="ch-care__grid">
                @forelse ($services as $service)
                    <a
                        href="{{ $service->isUrgent() ? route('emergency') : route('services.show', $service) }}"
                        @class([
                            'ch-care-card',
                            'ch-care-card--page',
                            'ch-care-card--urgent' => $service->isUrgent(),
                        ])
                    >
                        <span class="ch-care-card__media">
                            <img
                                src="{{ $service->publicImage() }}"
                                alt="{{ $service->title }} at {{ $hospitalName }}"
                                loading="{{ $loop->index < 3 ? 'eager' : 'lazy' }}"
                                referrerpolicy="no-referrer"
                            >
                            @if ($service->isUrgent() || $service->is_featured)
                                <span @class(['ch-care-card__badge', 'ch-care-card__badge--urgent' => $service->isUrgent()])>
                                    {{ $service->isUrgent() ? 'Open 24 hours' : 'Featured' }}
                                </span>
                            @endif
                        </span>
                        <span class="ch-care-card__body">
                            <span class="ch-care-card__top">
                                <span class="ch-care-card__icon"><x-icon :name="$service->publicIcon()" /></span>
                            </span>
                            <span class="ch-care-card__meta">
                                {{ $service->department?->name ?: ($service->isUrgent() ? 'Open 24 hours' : 'Patient service') }}
                            </span>
                            <h2>{{ $service->title }}</h2>
                            @if (filled($service->summary))
                                <p>{{ $service->summary }}</p>
                            @endif
                            <span class="ch-care-card__go">
                                {{ $service->isUrgent() ? 'Go to emergency' : 'View details' }}
                                <span aria-hidden="true">→</span>
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="ch-svc-empty">
                        <h2>
                            @if ($isFiltered)
                                No services in this clinic yet
                            @else
                                No services listed yet
                            @endif
                        </h2>
                        <p>
                            @if ($isFiltered)
                                Try another department, or browse every desk.
                            @else
                                Reception can guide you while the list is updated.
                            @endif
                        </p>
                        <div class="ch-svc-empty__actions">
                            @if ($isFiltered)
                                <a class="ch-btn ch-btn--solid" href="{{ route('services') }}">All services</a>
                            @endif
                            <a class="ch-btn" href="{{ route('home') }}#appointment">Book a visit</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="ch-svc-help">
        <div class="ch-wrap ch-svc-help__row">
            <div>
                <p class="ch-find__kicker">Need a hand?</p>
                <h2>Not sure which service fits?</h2>
                <p>Tell reception your symptoms or reports — they will point you to the right desk or consultant.</p>
            </div>
            <div class="ch-svc-help__actions">
                @if ($phone)
                    <a class="ch-btn ch-btn--solid" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
                @endif
                <a class="ch-btn" href="{{ route('doctors') }}">Find a doctor</a>
                <a class="ch-btn" href="{{ route('home') }}#appointment">Book a visit</a>
            </div>
        </div>
    </section>

    <section class="ch-about-values ch-svc-steps">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">Simple path</p>
                    <h2 class="ch-sec__title">How a visit works</h2>
                </div>
            </div>
            <div class="ch-values">
                @foreach ($visitSteps as $index => $step)
                    <article class="ch-value ch-svc-step">
                        <span class="ch-svc-step__num" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="ch-value__icon"><x-icon :name="$step['icon']" /></span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
