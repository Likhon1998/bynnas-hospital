@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $emergency = $hospital->get('emergency_phone') ?: $phone;
    $deskPhone = $service->isUrgent() ? $emergency : $phone;
    $hours = $service->isUrgent()
        ? ($workingHours['emergency'] ?? '24/7')
        : ($workingHours['saturday'] ?? $workingHours['monday'] ?? 'Call reception');
    $primaryHref = $service->isUrgent()
        ? route('emergency')
        : route('home', ['service' => $service->slug]).'#appointment';
    $primaryLabel = $service->isUrgent() ? 'Go to emergency' : 'Request a visit';
    $departmentName = $service->department?->name;
    $prepTips = $service->isUrgent()
        ? [
            'Come straight to the emergency entrance — do not wait for an appointment.',
            'Bring any current medicines and recent reports if you can.',
            'Someone should stay with the patient when possible.',
        ]
        : [
            'Bring a photo ID and previous prescriptions or reports.',
            'Ask reception about fasting, timing, or special preparation.',
            'Arrive a little early for registration on your first visit.',
        ];
@endphp

@section('title', $service->title.' | '.$hospitalName)
@section('meta_description', $service->summary ?: ($service->title.' at '.$hospitalName))

@section('content')
    <section class="ch-svc-hero ch-svc-hero--detail">
        <div class="ch-svc-hero__glow" aria-hidden="true"></div>
        <div class="ch-wrap">
            <nav class="ch-svc-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('services') }}">Services</a>
                @if ($departmentName)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('services', ['department' => $departmentName]) }}">{{ $departmentName }}</a>
                @endif
                <span aria-hidden="true">/</span>
                <span>{{ $service->title }}</span>
            </nav>

            <p class="ch-find__kicker">
                {{ $departmentName ?: ($service->isUrgent() ? 'Emergency desk' : 'Patient service') }}
            </p>

            <div class="ch-clinics-hero__row ch-svc-hero__row">
                <div>
                    <h1>{{ $service->title }}</h1>
                    @if (filled($service->summary))
                        <p>{{ $service->summary }}</p>
                    @endif
                    <ul class="ch-svc-pills">
                        @if ($service->isUrgent())
                            <li class="ch-svc-pills__item ch-svc-pills__item--urgent">Open 24 hours</li>
                        @elseif ($service->is_featured)
                            <li class="ch-svc-pills__item">Featured</li>
                        @endif
                        <li class="ch-svc-pills__item">Hours · {{ $hours }}</li>
                        @if ($departmentName)
                            <li class="ch-svc-pills__item">{{ $departmentName }}</li>
                        @endif
                    </ul>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn" href="{{ route('services') }}">All services</a>
                    <a class="ch-btn ch-btn--solid" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-profile ch-svc-detail">
        <div class="ch-wrap ch-profile__grid ch-svc-detail__grid">
            <aside class="ch-profile__card ch-profile__card--service ch-svc-side">
                <div class="ch-svc-side__media">
                    <img
                        class="ch-care-hero-photo"
                        src="{{ $service->publicImage() }}"
                        alt="{{ $service->title }} at {{ $hospitalName }}"
                        referrerpolicy="no-referrer"
                    >
                    <span class="ch-svc-side__icon" aria-hidden="true">
                        <x-icon :name="$service->publicIcon()" />
                    </span>
                </div>

                <dl class="ch-profile__facts">
                    <div>
                        <dt>Service</dt>
                        <dd>{{ $service->title }}</dd>
                    </div>
                    <div>
                        <dt>Department</dt>
                        <dd>
                            @if ($departmentName)
                                <a href="{{ route('services', ['department' => $departmentName]) }}">{{ $departmentName }}</a>
                            @else
                                General
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Hours</dt>
                        <dd>{{ $hours }}</dd>
                    </div>
                    @if ($deskPhone)
                        <div>
                            <dt>{{ $service->isUrgent() ? 'Emergency' : 'Desk' }}</dt>
                            <dd>
                                <a href="tel:{{ preg_replace('/\s+/', '', $deskPhone) }}">{{ $deskPhone }}</a>
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="ch-svc-side__cta">
                    <a class="ch-btn ch-btn--solid ch-btn--wide" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
                    @if ($deskPhone)
                        <a class="ch-btn ch-btn--wide" href="tel:{{ preg_replace('/\s+/', '', $deskPhone) }}">
                            Call {{ $deskPhone }}
                        </a>
                    @endif
                </div>
            </aside>

            <div class="ch-profile__copy ch-svc-copy">
                <h2>What to expect</h2>
                <div class="ch-care-copy">
                    @if (filled($service->body))
                        {!! $service->body !!}
                    @else
                        <p>{{ $service->summary ?: ($service->title.' is available at '.$hospitalName.'.') }}</p>
                        <p>Ask reception about duration, preparation, and whether a named consultant is required.</p>
                    @endif
                </div>

                <div class="ch-svc-prep">
                    <h2>Before you visit</h2>
                    <ul class="ch-svc-prep__list">
                        @foreach ($prepTips as $tip)
                            <li>{{ $tip }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="ch-profile__cta">
                    <a class="ch-btn ch-btn--solid" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
                    @if ($phone)
                        <a class="ch-btn" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
                    @endif
                    @if ($departmentName)
                        <a class="ch-btn" href="{{ route('doctors', ['specialty' => $departmentName]) }}">
                            Doctors in {{ $departmentName }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($departmentDoctors->isNotEmpty())
        <section class="ch-docs-sec ch-docs-sec--page ch-svc-doctors">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Consultants</p>
                        <h2 class="ch-sec__title">Doctors in this department</h2>
                    </div>
                    <a class="ch-more" href="{{ route('doctors', ['specialty' => $departmentName]) }}">View all doctors →</a>
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
        <section class="ch-care ch-care--related ch-care--page">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Also available</p>
                        <h2 class="ch-sec__title">Related services</h2>
                    </div>
                    <a class="ch-more" href="{{ route('services') }}">All services →</a>
                </div>
                <div class="ch-care__grid">
                    @foreach ($relatedServices as $related)
                        <a
                            href="{{ $related->isUrgent() ? route('emergency') : route('services.show', $related) }}"
                            @class([
                                'ch-care-card',
                                'ch-care-card--page',
                                'ch-care-card--urgent' => $related->isUrgent(),
                            ])
                        >
                            <span class="ch-care-card__media">
                                <img src="{{ $related->publicImage() }}" alt="{{ $related->title }}" loading="lazy" referrerpolicy="no-referrer">
                            </span>
                            <span class="ch-care-card__body">
                                <span class="ch-care-card__meta">
                                    {{ $related->department?->name ?: ($related->isUrgent() ? 'Open 24 hours' : 'Patient service') }}
                                </span>
                                <h2>{{ $related->title }}</h2>
                                @if (filled($related->summary))
                                    <p>{{ $related->summary }}</p>
                                @endif
                                <span class="ch-care-card__go">
                                    {{ $related->isUrgent() ? 'Go to emergency' : 'View details' }}
                                    <span aria-hidden="true">→</span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
