@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $isFiltered = $selectedDepartment !== '';
    $visitSteps = [
        ['icon' => 'hospital', 'title' => 'Pick the desk', 'text' => 'Emergency, a clinic, or a test. If you are unsure, call reception and describe the problem.'],
        ['icon' => 'calendar', 'title' => 'Ask for a time', 'text' => 'Choose a named consultant when you can. Someone from the desk will confirm the slot.'],
        ['icon' => 'clock', 'title' => 'Come with papers', 'text' => 'Bring old prescriptions and reports if you have them. The first visit takes a little longer.'],
    ];
@endphp

@section('title', 'Services | '.$hospitalName)
@section('meta_description', 'Clinics, diagnostics, and emergency care at '.$hospitalName.'.')

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Patient services</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Our Services</h1>
                    <p>
                        @if ($isFiltered)
                            Desks in {{ $selectedDepartment }}. Open another clinic if this is not the right floor.
                        @else
                            Emergency, diagnostics, pharmacy, and weekday clinics — pick a desk, then book or walk in.
                        @endif
                    </p>
                </div>
                <div class="ch-clinics-hero__actions">
                    @if ($isFiltered)
                        <a class="ch-btn" href="{{ route('services') }}">All services</a>
                    @endif
                    <a class="ch-btn" href="{{ route('home') }}#find-doctor">Find a doctor</a>
                    <a class="ch-btn ch-btn--solid" href="{{ route('home') }}#appointment">Book appointment</a>
                </div>
            </div>
            <p class="ch-clinics-hero__count">
                {{ $services->count() }} {{ \Illuminate\Support\Str::plural('service', $services->count()) }}
                @if ($isFiltered)
                    · in {{ $selectedDepartment }}
                @endif
            </p>
            @if ($departments->isNotEmpty())
                <div class="ch-find__chips">
                    <a href="{{ route('services') }}" @class(['is-active' => ! $isFiltered])>All desks</a>
                    @foreach ($departments as $department)
                        <a href="{{ route('services', ['department' => $department->name]) }}" @class(['is-active' => $selectedDepartment === $department->name])>{{ $department->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="ch-care">
        <div class="ch-wrap">
            <div class="ch-care__grid">
                @forelse ($services as $service)
                    <a
                        href="{{ $service->isUrgent() ? route('emergency') : route('services.show', $service) }}"
                        @class(['ch-care-card', 'ch-care-card--urgent' => $service->isUrgent()])
                    >
                        <span class="ch-care-card__media">
                            <img
                                src="{{ $service->publicImage() }}"
                                alt="{{ $service->title }} at {{ $hospitalName }}"
                                loading="{{ $loop->index < 3 ? 'eager' : 'lazy' }}"
                                referrerpolicy="no-referrer"
                            >
                        </span>
                        <span class="ch-care-card__body">
                            <span class="ch-care-card__top">
                                <span class="ch-care-card__icon"><x-icon :name="$service->publicIcon()" /></span>
                                @if ($service->is_featured)
                                    <span class="ch-clinic__tag">{{ $service->isUrgent() ? '24/7' : 'Featured' }}</span>
                                @endif
                            </span>
                            <span class="ch-care-card__meta">{{ $service->department?->name ?: ($service->isUrgent() ? 'Open 24 hours' : 'Patient service') }}</span>
                            <h2>{{ $service->title }}</h2>
                            <p>{{ $service->summary ?: 'Ask the desk how this service runs and whether you need to book.' }}</p>
                            <span class="ch-care-card__go">{{ $service->isUrgent() ? 'Go to emergency →' : 'Learn more →' }}</span>
                        </span>
                    </a>
                @empty
                    <p class="ch-lead">
                        @if ($isFiltered)
                            No services in this clinic yet. Pick another department or browse every desk.
                        @else
                            Services will appear here when the desk publishes them.
                        @endif
                    </p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="ch-about-values">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">Before you arrive</p>
                    <h2 class="ch-sec__title">How a visit works</h2>
                </div>
            </div>
            <div class="ch-values">
                @foreach ($visitSteps as $step)
                    <article class="ch-value">
                        <span class="ch-value__icon"><x-icon :name="$step['icon']" /></span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
