@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $specialty = $doctor->specialization ?: $doctor->department?->name ?: 'Consultant';
    $summary = filled($doctor->biography)
        ? str($doctor->biography)->limit(160)
        : $specialty.' at '.$hospitalName.'.';
@endphp

@section('title', $doctor->name.' | '.$hospitalName)
@section('meta_description', $summary)

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">{{ $doctor->department?->name ?: 'Consultant' }}</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>{{ $doctor->name }}</h1>
                    <p>
                        {{ $doctor->designation ?: 'Consultant' }}
                        @if ($specialty)
                            · {{ $specialty }}
                        @endif
                    </p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn" href="{{ route('doctors') }}">All doctors</a>
                    <a class="ch-btn ch-btn--solid" href="{{ route('home', ['doctor' => $doctor->id]) }}#appointment">Book appointment</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-profile">
        <div class="ch-wrap ch-profile__grid">
            <aside class="ch-profile__card">
                @if ($doctor->photo)
                    <img class="ch-profile__photo" src="{{ $doctor->photo }}" alt="{{ $doctor->name }}">
                @else
                    <span class="ch-profile__photo ch-profile__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
                @endif
                <dl class="ch-profile__facts">
                    @if ($doctor->department)
                        <div>
                            <dt>Clinic</dt>
                            <dd>{{ $doctor->department->name }}</dd>
                        </div>
                    @endif
                    @if ($doctor->experience_years)
                        <div>
                            <dt>Experience</dt>
                            <dd>{{ $doctor->experience_years }} yrs</dd>
                        </div>
                    @endif
                    @if (filled($doctor->qualifications))
                        <div>
                            <dt>Qualifications</dt>
                            <dd>{{ $doctor->qualifications }}</dd>
                        </div>
                    @endif
                    @if (filled($doctor->registration_number))
                        <div>
                            <dt>Registration</dt>
                            <dd>{{ $doctor->registration_number }}</dd>
                        </div>
                    @endif
                    @if ((float) $doctor->consultation_fee > 0)
                        <div>
                            <dt>Consultation</dt>
                            <dd>৳{{ number_format((float) $doctor->consultation_fee) }}</dd>
                        </div>
                    @endif
                </dl>
            </aside>

            <div class="ch-profile__copy">
                <h2>About</h2>
                <p>
                    {{ filled($doctor->biography)
                        ? $doctor->biography
                        : $doctor->name.' sees patients in '.$specialty.' at '.$hospitalName.'. Ask the desk for the next free visit.' }}
                </p>

                <h2>Clinic hours</h2>
                @if ($hasClinicHours)
                    <ul class="ch-profile__hours">
                        @foreach ($clinicHours as $day)
                            <li @class(['is-open' => $day['open']])>
                                <span>{{ $day['label'] }}</span>
                                <strong>{{ $day['hours'] }}</strong>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>Ask the desk for this consultant’s clinic hours.</p>
                @endif

                <div class="ch-profile__cta">
                    <a class="ch-btn ch-btn--solid" href="{{ route('home', ['doctor' => $doctor->id]) }}#appointment">Book this doctor</a>
                    <a class="ch-btn" href="{{ route('home') }}#find-doctor">Find another doctor</a>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedDoctors->isNotEmpty())
        <section class="ch-docs-sec ch-docs-sec--page">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <h2 class="ch-sec__title">Other doctors in {{ $doctor->department?->name ?: 'this clinic' }}</h2>
                    </div>
                    <a class="ch-more" href="{{ route('doctors') }}">View all Doctors →</a>
                </div>
                <div class="ch-docs ch-docs--all">
                    @foreach ($relatedDoctors as $related)
                        <article class="ch-doc">
                            <a class="ch-doc__profile" href="{{ route('doctors.show', $related) }}">
                                @if ($related->photo)
                                    <img src="{{ $related->photo }}" alt="{{ $related->name }}">
                                @else
                                    <span class="ch-doc__initials" aria-hidden="true">{{ $related->initials() }}</span>
                                @endif
                                <strong>{{ $related->name }}</strong>
                                <em>{{ $related->specialization ?: $related->department?->name ?: 'Consultant' }}</em>
                            </a>
                            <a class="ch-doc__book" href="{{ route('home', ['doctor' => $related->id]) }}#appointment">Book</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
