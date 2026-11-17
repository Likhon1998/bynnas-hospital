@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $summary = filled($department->description)
        ? \Illuminate\Support\Str::limit(strip_tags((string) $department->description), 160)
        : 'Specialist clinics and bookable consultants at '.$hospitalName.'.';
    $image = \App\Support\PublicPhoto::usable($department->image)
        ?? \App\Support\PublicPhoto::campus(max(0, (int) $department->id - 1));
@endphp

@section('title', $department->name.' | '.$hospitalName)
@section('meta_description', $summary)

@section('content')
    <section class="ch-svc-hero ch-svc-hero--detail">
        <div class="ch-svc-hero__glow" aria-hidden="true"></div>
        <div class="ch-wrap">
            <nav class="ch-svc-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('departments') }}">Departments</a>
                <span aria-hidden="true">/</span>
                <span>{{ $department->name }}</span>
            </nav>

            <p class="ch-find__kicker">Department</p>

            <div class="ch-clinics-hero__row ch-svc-hero__row">
                <div>
                    <h1>{{ $department->name }}</h1>
                    <p>{{ $summary }}</p>
                    <ul class="ch-svc-pills">
                        @if ($department->is_featured)
                            <li class="ch-svc-pills__item">Featured</li>
                        @endif
                        <li class="ch-svc-pills__item">
                            {{ $doctors->count() }} {{ \Illuminate\Support\Str::plural('consultant', $doctors->count()) }}
                        </li>
                        @if ($services->isNotEmpty())
                            <li class="ch-svc-pills__item">
                                {{ $services->count() }} {{ \Illuminate\Support\Str::plural('service', $services->count()) }}
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn" href="{{ route('departments') }}">All departments</a>
                    <a class="ch-btn ch-btn--solid" href="{{ route('doctors', ['specialty' => $department->name]) }}">
                        Find doctors
                    </a>
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
                        src="{{ $image }}"
                        alt="{{ $department->name }} at {{ $hospitalName }}"
                        referrerpolicy="no-referrer"
                    >
                    <span class="ch-svc-side__icon" aria-hidden="true">
                        <x-icon :name="$department->icon ?: 'hospital'" />
                    </span>
                </div>

                <dl class="ch-profile__facts">
                    <div>
                        <dt>Department</dt>
                        <dd>{{ $department->name }}</dd>
                    </div>
                    <div>
                        <dt>Consultants</dt>
                        <dd>{{ $doctors->count() }}</dd>
                    </div>
                    @if ($services->isNotEmpty())
                        <div>
                            <dt>Services</dt>
                            <dd>{{ $services->count() }}</dd>
                        </div>
                    @endif
                    @if ($phone)
                        <div>
                            <dt>Desk</dt>
                            <dd>
                                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="ch-svc-side__cta">
                    <a class="ch-btn ch-btn--solid ch-btn--wide" href="{{ route('doctors', ['specialty' => $department->name]) }}">
                        Find doctors in {{ $department->name }}
                    </a>
                    <a class="ch-btn ch-btn--wide" href="{{ route('home') }}?book=1">Book a visit</a>
                </div>
            </aside>

            <div class="ch-profile__copy ch-svc-copy">
                <h2>About this department</h2>
                <div class="ch-care-copy">
                    @if (filled($department->description))
                        <p>{{ $department->description }}</p>
                    @else
                        <p>{{ $department->name }} clinics are available at {{ $hospitalName }}.</p>
                        <p>Ask reception about consultants, clinic hours, and how to book a visit.</p>
                    @endif
                </div>

                <div class="ch-svc-prep">
                    <h2>Before you visit</h2>
                    <ul class="ch-svc-prep__list">
                        <li>Bring a photo ID and previous prescriptions or reports.</li>
                        <li>Ask reception about fasting, timing, or special preparation.</li>
                        <li>Arrive a little early for registration on your first visit.</li>
                    </ul>
                </div>

                <div class="ch-profile__cta">
                    <a class="ch-btn ch-btn--solid" href="{{ route('doctors', ['specialty' => $department->name]) }}">
                        Doctors in {{ $department->name }}
                    </a>
                    @if ($phone)
                        <a class="ch-btn" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
                    @endif
                    <a class="ch-btn" href="{{ route('home') }}?book=1">Book a visit</a>
                </div>
            </div>
        </div>
    </section>

    @if ($doctors->isNotEmpty())
        <section class="ch-docs-sec ch-docs-sec--page ch-svc-doctors">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Consultants</p>
                        <h2 class="ch-sec__title">Doctors in {{ $department->name }}</h2>
                    </div>
                    <a class="ch-more" href="{{ route('doctors', ['specialty' => $department->name]) }}">View all doctors →</a>
                </div>
                <div class="ch-docs ch-docs--all">
                    @foreach ($doctors->take(8) as $doctor)
                        <article class="ch-doc">
                            <a class="ch-doc__profile" href="{{ route('doctors.show', $doctor) }}">
                                @if ($doctor->photo)
                                    <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}">
                                @else
                                    <span class="ch-doc__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
                                @endif
                                <strong>{{ $doctor->name }}</strong>
                                <em>{{ $doctor->specialization ?: $department->name }}</em>
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

    @if ($services->isNotEmpty())
        <section class="ch-care ch-care--related ch-care--page">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Care options</p>
                        <h2 class="ch-sec__title">Services in this department</h2>
                    </div>
                    <a class="ch-more" href="{{ route('services', ['department' => $department->name]) }}">All services →</a>
                </div>
                <div class="ch-care__grid">
                    @foreach ($services as $service)
                        <a
                            href="{{ $service->isUrgent() ? route('emergency') : route('services.show', $service) }}"
                            @class([
                                'ch-care-card',
                                'ch-care-card--page',
                                'ch-care-card--urgent' => $service->isUrgent(),
                            ])
                        >
                            <span class="ch-care-card__media">
                                <img src="{{ $service->publicImage() }}" alt="{{ $service->title }}" loading="lazy" referrerpolicy="no-referrer">
                            </span>
                            <span class="ch-care-card__body">
                                <span class="ch-care-card__meta">{{ $department->name }}</span>
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
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($relatedDepartments->isNotEmpty())
        <section class="ch-clinics">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">More clinics</p>
                        <h2 class="ch-sec__title">Other departments</h2>
                    </div>
                    <a class="ch-more" href="{{ route('departments') }}">All departments →</a>
                </div>
                <div class="ch-clinics__grid">
                    @foreach ($relatedDepartments as $related)
                        <a class="ch-clinic" href="{{ route('departments.show', $related) }}">
                            <span class="ch-clinic__top">
                                <span class="ch-clinic__icon"><x-icon :name="$related->icon ?: 'hospital'" /></span>
                                @if ($related->is_featured)
                                    <span class="ch-clinic__tag">Featured</span>
                                @endif
                            </span>
                            <h2>{{ $related->name }}</h2>
                            @if (filled($related->description))
                                <p>{{ str($related->description)->limit(110) }}</p>
                            @endif
                            <span class="ch-clinic__go">
                                @if ($related->consultants_count > 0)
                                    {{ $related->consultants_count }} {{ \Illuminate\Support\Str::plural('consultant', $related->consultants_count) }} →
                                @else
                                    View department →
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
