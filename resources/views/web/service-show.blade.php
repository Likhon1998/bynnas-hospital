@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $heroImage = $service->image
        ?: 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=1600&q=80';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
@endphp

@section('title', $service->title.' | '.$hospitalName)
@section('meta_description', $service->summary ?: ($service->title.' at '.$hospitalName))

@section('content')
    <x-web.page-intro
        :kicker="$service->department?->name ?: 'Hospital service'"
        :title="$service->title"
        :note="$service->summary"
    />

    <section class="bg-[#f7f5ef]">
        <div class="web-wrap web-section grid items-start gap-10 lg:grid-cols-[1.35fr_0.65fr] lg:gap-16">
            <div class="min-w-0">
                <h2 class="web-h2">What to expect</h2>
                <div class="prose-service web-copy mt-5">
                    @if (filled($service->body))
                        {!! $service->body !!}
                    @else
                        <p>{{ $service->summary }}</p>
                        <p>Ask the desk how long the visit usually takes, and whether you need to come fasting. Write the doctor’s name down if they give you one.</p>
                    @endif
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <x-ui.button href="{{ route('home', ['service' => $service->slug]) }}#appointment" variant="outline-teal" class="!rounded">
                        Request a visit
                    </x-ui.button>
                    <a href="{{ route('services') }}" class="web-link">All services</a>
                </div>
            </div>

            <aside class="space-y-5">
                <div class="border border-[#ddd8cc] bg-[#fbfaf6] p-5">
                    <p class="web-h3">Before you come</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-[#2f433c]">Service</dt>
                            <dd class="mt-0.5 text-[#1c332c]">{{ $service->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#2f433c]">Department</dt>
                            <dd class="mt-0.5 text-[#1c332c]">{{ $service->department?->name ?: 'General hospital care' }}</dd>
                        </div>
                        @if ($phone)
                            <div>
                                <dt class="text-[#2f433c]">Desk</dt>
                                <dd class="mt-0.5">
                                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="web-link">{{ $phone }}</a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <img src="{{ $heroImage }}" alt="{{ $service->title }}" referrerpolicy="no-referrer" class="aspect-[5/4] w-full border border-[#ddd8cc] bg-[#c5cdc8] object-cover">
            </aside>
        </div>
    </section>

    @if ($departmentDoctors->isNotEmpty())
        <section class="border-t border-[#ddd8cc] bg-[#fbfaf6]">
            <div class="web-wrap web-section">
                <h2 class="web-h2">Doctors in this department</h2>
                <div class="mt-8 grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($departmentDoctors as $doctor)
                        <article class="web-person">
                            <img
                                src="{{ filled($doctor->photo) ? $doctor->photo : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80' }}"
                                alt="Portrait of {{ $doctor->name }}"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                class="aspect-[4/5] w-full border border-[#ddd8cc] bg-[#c5cdc8] object-cover object-top"
                            >
                            <h3 class="web-h3">{{ $doctor->name }}</h3>
                            <p class="web-muted">
                                {{ $doctor->designation ?: ($doctor->department?->name ?: 'Consultant') }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($relatedServices->isNotEmpty())
        <section class="border-t border-[#ddd8cc] bg-[#f7f5ef]">
            <div class="web-wrap web-section">
                <h2 class="web-h2">Related care</h2>
                <ul class="mt-6 divide-y divide-[#ddd8cc] border-y border-[#ddd8cc]">
                    @foreach ($relatedServices as $related)
                        <li>
                            <a href="{{ route('services.show', $related) }}" class="flex flex-col gap-1 py-4 sm:flex-row sm:items-baseline sm:justify-between sm:gap-8">
                                <span class="font-semibold text-[#1c332c]">{{ $related->title }}</span>
                                <span class="web-muted sm:max-w-md sm:text-right">{{ $related->summary }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
