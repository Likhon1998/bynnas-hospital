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

    <section class="site-block">
        <div class="web-wrap grid items-start gap-10 lg:grid-cols-[1.35fr_0.65fr] lg:gap-16">
            <div class="min-w-0">
                <div class="site-section-head">
                    <h2>What to expect</h2>
                </div>
                <div class="prose-service text-[0.95rem] leading-7 text-[#475569]">
                    @if (filled($service->body))
                        {!! $service->body !!}
                    @else
                        <p>{{ $service->summary }}</p>
                        <p>Ask the desk how long the visit usually takes, and whether you need to come fasting. Write the doctor’s name down if they give you one.</p>
                    @endif
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <a href="{{ route('home', ['service' => $service->slug]) }}#appointment" class="site-btn site-btn--green">
                        Request a visit
                    </a>
                    <a href="{{ route('services') }}" class="text-sm font-semibold text-[#16a34a]">All services</a>
                </div>
            </div>

            <aside class="space-y-5">
                <div class="rounded-[1.35rem] border border-[#e2e8f0] bg-white p-5 shadow-[0_16px_34px_-32px_rgb(15_23_42_/_0.4)]">
                    <p class="text-base font-bold text-[#122447]">Before you come</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-[#64748b]">Service</dt>
                            <dd class="mt-0.5 font-medium text-[#122447]">{{ $service->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#64748b]">Department</dt>
                            <dd class="mt-0.5 font-medium text-[#122447]">{{ $service->department?->name ?: 'General hospital care' }}</dd>
                        </div>
                        @if ($phone)
                            <div>
                                <dt class="text-[#64748b]">Desk</dt>
                                <dd class="mt-0.5">
                                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="font-semibold text-[#16a34a]">{{ $phone }}</a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <img src="{{ $heroImage }}" alt="{{ $service->title }}" referrerpolicy="no-referrer" class="aspect-[5/4] w-full rounded-[1.35rem] bg-[#dbe4f0] object-cover shadow-[0_16px_34px_-32px_rgb(15_23_42_/_0.4)]">
            </aside>
        </div>
    </section>

    @if ($departmentDoctors->isNotEmpty())
        <section class="site-block site-block--soft">
            <div class="web-wrap">
                <div class="site-section-head">
                    <h2>Doctors in this department</h2>
                </div>
                <div class="site-doctors site-doctors--grid">
                    @foreach ($departmentDoctors as $doctor)
                        <article class="site-doctor">
                            <div class="site-doctor__photo">
                                <img
                                    src="{{ filled($doctor->photo) ? $doctor->photo : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80' }}"
                                    alt="Portrait of {{ $doctor->name }}"
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                >
                            </div>
                            <div class="site-doctor__card">
                                <h3>{{ $doctor->name }}</h3>
                                <p>{{ $doctor->designation ?: ($doctor->department?->name ?: 'Consultant') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($relatedServices->isNotEmpty())
        <section class="site-block">
            <div class="web-wrap">
                <div class="site-section-head">
                    <h2>Related care</h2>
                </div>
                <ul class="overflow-hidden rounded-[1.35rem] border border-[#e2e8f0] bg-white">
                    @foreach ($relatedServices as $related)
                        <li class="border-b border-[#e2e8f0] last:border-b-0">
                            <a href="{{ route('services.show', $related) }}" class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-baseline sm:justify-between sm:gap-8">
                                <span class="font-semibold text-[#122447]">{{ $related->title }}</span>
                                <span class="text-sm text-[#64748b] sm:max-w-md sm:text-right">{{ $related->summary }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
