@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $emergency = $hospital->get('emergency_phone') ?: $phone;
    $hours = $service->isUrgent()
        ? ($workingHours['emergency'] ?? '24/7')
        : ($workingHours['saturday'] ?? $workingHours['monday'] ?? 'Ask the desk');
    $primaryHref = $service->isUrgent()
        ? route('emergency')
        : route('home', ['service' => $service->slug]).'#appointment';
    $primaryLabel = $service->isUrgent() ? 'Go to emergency' : 'Request a visit';
@endphp

@section('title', $service->title.' | '.$hospitalName)
@section('meta_description', $service->summary ?: ($service->title.' at '.$hospitalName))

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">{{ $service->department?->name ?: ($service->isUrgent() ? 'Emergency desk' : 'Hospital service') }}</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>{{ $service->title }}</h1>
                    <p>{{ $service->summary ?: 'Ask the desk how this service runs and whether you need to book.' }}</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn" href="{{ route('services') }}">All services</a>
                    <a class="ch-btn ch-btn--solid" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-profile">
        <div class="ch-wrap ch-profile__grid">
            <aside class="ch-profile__card ch-profile__card--service">
                <img
                    class="ch-care-hero-photo"
                    src="{{ $service->publicImage() }}"
                    alt="{{ $service->title }} at {{ $hospitalName }}"
                    referrerpolicy="no-referrer"
                >
                <dl class="ch-profile__facts">
                    <div>
                        <dt>Service</dt>
                        <dd>{{ $service->title }}</dd>
                    </div>
                    <div>
                        <dt>Department</dt>
                        <dd>{{ $service->department?->name ?: 'General hospital care' }}</dd>
                    </div>
                    <div>
                        <dt>Hours</dt>
                        <dd>{{ $hours }}</dd>
                    </div>
                    @if ($phone)
                        <div>
                            <dt>{{ $service->isUrgent() ? 'Emergency' : 'Desk' }}</dt>
                            <dd>
                                <a href="tel:{{ preg_replace('/\s+/', '', $service->isUrgent() ? $emergency : $phone) }}">
                                    {{ $service->isUrgent() ? $emergency : $phone }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </aside>

            <div class="ch-profile__copy">
                <h2>What to expect</h2>
                <div class="ch-care-copy">
                    @if (filled($service->body))
                        {!! $service->body !!}
                    @else
                        <p>{{ $service->summary }}</p>
                        <p>Ask the desk how long the visit usually takes, and whether you need to come fasting. Write the doctor’s name down if they give you one.</p>
                    @endif
                </div>
                <div class="ch-profile__cta">
                    <a class="ch-btn ch-btn--solid" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
                    @if ($phone)
                        <a class="ch-btn" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($departmentDoctors->isNotEmpty())
        <section class="ch-docs-sec ch-docs-sec--page">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">{{ $service->department?->name }}</p>
                        <h2 class="ch-sec__title">Doctors in this department</h2>
                    </div>
                    <a class="ch-more" href="{{ route('doctors', ['specialty' => $service->department?->name]) }}">View all doctors →</a>
                </div>
                <div class="ch-docs ch-docs--all">
                    @foreach ($departmentDoctors as $doctor)
                        <article class="ch-doc">
                            <a class="ch-doc__profile" href="{{ route('doctors.show', $doctor) }}">
                                @if ($doctor->photo)
                                    <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}">
                                @else
                                    <span class="ch-doc__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
                                @endif
                                <strong>{{ $doctor->name }}</strong>
                                <em>{{ $doctor->specialization ?: $doctor->department?->name ?: 'Consultant' }}</em>
                            </a>
                            <div class="ch-doc__actions">
                                <a class="ch-doc__btn ch-doc__btn--ghost" href="{{ route('doctors.show', $doctor) }}">Profile</a>
                                <a class="ch-doc__btn ch-doc__btn--solid" href="{{ route('home', ['doctor' => $doctor->id]) }}#appointment">Book</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($relatedServices->isNotEmpty())
        <section class="ch-care ch-care--related">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <h2 class="ch-sec__title">Related care</h2>
                    </div>
                    <a class="ch-more" href="{{ route('services') }}">All services →</a>
                </div>
                <div class="ch-care__grid">
                    @foreach ($relatedServices as $related)
                        <a
                            href="{{ $related->isUrgent() ? route('emergency') : route('services.show', $related) }}"
                            @class(['ch-care-card', 'ch-care-card--urgent' => $related->isUrgent()])
                        >
                            <span class="ch-care-card__media">
                                <img src="{{ $related->publicImage() }}" alt="{{ $related->title }}" loading="lazy" referrerpolicy="no-referrer">
                            </span>
                            <span class="ch-care-card__body">
                                <span class="ch-care-card__meta">{{ $related->department?->name ?: ($related->isUrgent() ? 'Open 24 hours' : 'Patient service') }}</span>
                                <h2>{{ $related->title }}</h2>
                                <p>{{ $related->summary }}</p>
                                <span class="ch-care-card__go">{{ $related->isUrgent() ? 'Go to emergency →' : 'Learn more →' }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
