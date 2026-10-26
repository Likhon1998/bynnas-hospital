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
        <section class="site-block">
            <div class="web-wrap">
                <div class="site-section-head">
                    <h2>Services families ask for first</h2>
                </div>
                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($featuredServices->take(4) as $service)
                        <a href="{{ route('services.show', $service) }}" class="grid overflow-hidden rounded-[1.35rem] border border-[#e2e8f0] bg-white shadow-[0_16px_34px_-32px_rgb(15_23_42_/_0.4)] sm:grid-cols-[9rem_1fr]">
                            <img
                                src="{{ $service->image }}"
                                alt="{{ $service->title }}"
                                referrerpolicy="no-referrer"
                                class="h-40 w-full bg-[#dbe4f0] object-cover sm:h-full"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            >
                            <div class="flex flex-col justify-center px-5 py-4">
                                <h3 class="text-lg font-bold text-[#122447]">{{ $service->title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#64748b]">{{ $service->summary }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="site-block site-block--soft">
        <div class="web-wrap">
            <div class="site-section-head">
                <h2>If you have not been here before</h2>
            </div>
            <div class="grid gap-8 sm:grid-cols-3 sm:gap-10">
                @foreach ($steps as $step)
                    <div class="rounded-[1.35rem] border border-[#e2e8f0] bg-white p-5 shadow-[0_16px_34px_-32px_rgb(15_23_42_/_0.4)]">
                        <h3 class="text-base font-bold text-[#122447]">{{ $step['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-[#64748b]">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-block">
        <div class="web-wrap">
            <div class="site-section-head">
                <h2>Complete hospital support</h2>
            </div>
            <div class="site-depts">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service) }}" class="site-dept block transition hover:-translate-y-0.5">
                        @if ($service->department)
                            <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[#16a34a]">{{ $service->department->name }}</p>
                        @endif
                        <h3 @class(['text-[1.05rem] font-bold text-[#122447]', 'mt-2' => (bool) $service->department])>{{ $service->title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-[#64748b]">{{ $service->summary }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if ($departments->isNotEmpty())
        <section class="site-block site-block--soft">
            <div class="web-wrap">
                <div class="site-section-head">
                    <h2>By department</h2>
                </div>
                <div class="space-y-8">
                    @foreach ($departments as $department)
                        <div class="rounded-[1.35rem] border border-[#e2e8f0] bg-white p-5 shadow-[0_16px_34px_-32px_rgb(15_23_42_/_0.4)]">
                            <div class="flex items-baseline justify-between gap-4 border-b border-[#e2e8f0] pb-2">
                                <h3 class="text-base font-bold text-[#122447]">{{ $department->name }}</h3>
                                <p class="shrink-0 text-sm text-[#64748b]">{{ $department->services->count() }} services</p>
                            </div>
                            <ul class="mt-3 flex flex-wrap gap-x-5 gap-y-2">
                                @foreach ($department->services as $service)
                                    <li>
                                        <a href="{{ route('services.show', $service) }}" class="text-sm font-semibold text-[#16a34a] hover:underline">
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
