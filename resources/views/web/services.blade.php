@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $steps = [
        ['title' => 'Pick the service', 'text' => 'Emergency, a clinic, or a test. If you are unsure, call the desk and describe the problem.'],
        ['title' => 'Ask for a time', 'text' => 'Give a date and a phone number. Someone from reception will call you back.'],
        ['title' => 'Come with papers', 'text' => 'Bring old prescriptions if you have them. The first visit takes a little longer.'],
    ];
@endphp

@section('title', 'Services | '.$hospitalName)
@section('meta_description', 'Clinics, diagnostics, and emergency care at '.$hospitalName.'.')

@section('content')
    <x-web.page-intro
        kicker="Clinical care"
        title="Our services"
        note="Complete hospital support, from the night emergency desk to weekday clinics. Read a service before you book if you want the details."
    />

    @if ($featuredServices->isNotEmpty())
        <section class="bg-[#f7f5ef]">
            <div class="web-wrap web-section">
                <h2 class="web-h2">Services families ask for first</h2>
                <div class="mt-8 grid gap-5 lg:grid-cols-2">
                    @foreach ($featuredServices->take(4) as $service)
                        <a href="{{ route('services.show', $service) }}" class="grid overflow-hidden border border-[#ddd8cc] bg-[#fbfaf6] sm:grid-cols-[9rem_1fr]">
                            <img
                                src="{{ $service->image }}"
                                alt="{{ $service->title }}"
                                referrerpolicy="no-referrer"
                                class="h-40 w-full bg-[#c5cdc8] object-cover sm:h-full"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            >
                            <div class="flex flex-col justify-center px-5 py-4">
                                <h3 class="web-h3">{{ $service->title }}</h3>
                                <p class="web-copy mt-2">{{ $service->summary }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="border-y border-[#ddd8cc] bg-[#fbfaf6]">
        <div class="web-wrap web-section">
            <h2 class="web-h2">If you have not been here before</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-3 sm:gap-10">
                @foreach ($steps as $step)
                    <div class="border-t border-[#c9c2b2] pt-4">
                        <h3 class="web-h3">{{ $step['title'] }}</h3>
                        <p class="web-copy mt-3">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#f7f5ef]">
        <div class="web-wrap web-section">
            <h2 class="web-h2">Complete hospital support</h2>
            <div class="mt-8 grid gap-px bg-[#ddd8cc] sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service) }}" class="bg-[#f7f5ef] p-5 hover:bg-[#fbfaf6]">
                        @if ($service->department)
                            <p class="web-kicker">{{ $service->department->name }}</p>
                        @endif
                        <h3 @class(['web-h3', 'mt-2' => (bool) $service->department])>{{ $service->title }}</h3>
                        <p class="web-copy mt-2">{{ $service->summary }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if ($departments->isNotEmpty())
        <section class="border-t border-[#ddd8cc] bg-[#fbfaf6]">
            <div class="web-wrap web-section">
                <h2 class="web-h2">By department</h2>
                <div class="mt-8 space-y-8">
                    @foreach ($departments as $department)
                        <div>
                            <div class="flex items-baseline justify-between gap-4 border-b border-[#ddd8cc] pb-2">
                                <h3 class="web-h3">{{ $department->name }}</h3>
                                <p class="web-muted shrink-0">{{ $department->services->count() }} services</p>
                            </div>
                            <ul class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                                @foreach ($department->services as $service)
                                    <li>
                                        <a href="{{ route('services.show', $service) }}" class="web-link">
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
@endsection
