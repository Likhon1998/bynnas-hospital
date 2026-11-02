@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $resultLabel = $specialty !== '' ? $specialty : ($searchedName !== '' ? $searchedName : null);
@endphp

@section('title', 'Doctors | '.$hospitalName)
@section('meta_description', 'Meet the consultants at '.$hospitalName.'. Choose a specialty or a named doctor, then book a visit.')

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Consultant directory</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Our Doctors</h1>
                    <p>
                        @if ($isFiltered && $resultLabel)
                            Showing consultants for {{ $resultLabel }}.
                        @else
                            Every active consultant at {{ $hospitalName }}. Pick a specialty if you do not yet have a name.
                        @endif
                    </p>
                </div>
                <div class="ch-clinics-hero__actions">
                    @if ($isFiltered)
                        <a class="ch-btn" href="{{ route('doctors') }}">Clear search</a>
                    @endif
                    <a class="ch-btn ch-btn--solid" href="{{ route('home') }}#appointment">Book appointment</a>
                </div>
            </div>
            <p class="ch-clinics-hero__count">
                {{ $doctors->count() }} {{ \Illuminate\Support\Str::plural('consultant', $doctors->count()) }}
            </p>
            <form class="ch-search ch-search--page" action="{{ route('doctors') }}" method="get" data-doctor-filter>
                <label class="ch-search__field">
                    <span>Specialty</span>
                    <select class="ch-input" name="specialty" data-doctor-specialty>
                        <option value="">All specialties</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->name }}" @selected($specialty === $department->name)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="ch-search__field">
                    <span>Choose a doctor</span>
                    <select class="ch-input" name="q" data-doctor-list>
                        <option value="">Browse consultants</option>
                        @foreach ($directoryDoctors as $doctor)
                            <option
                                value="{{ $doctor->name }}"
                                data-specialty="{{ $doctor->department?->name }}"
                                @selected($searchedName === $doctor->name)
                            >{{ $doctor->name }}@if ($doctor->specialization || $doctor->department) — {{ $doctor->specialization ?: $doctor->department?->name }}@endif</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="ch-btn ch-btn--solid ch-search__go">Show doctors</button>
            </form>
            <div class="ch-find__chips">
                @foreach ($departments as $department)
                    <a href="{{ route('doctors', ['specialty' => $department->name]) }}" @class(['is-active' => $specialty === $department->name])>{{ $department->name }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ch-docs-sec ch-docs-sec--page">
        <div class="ch-wrap">
            <div class="ch-docs ch-docs--all">
                @forelse ($doctors as $doctor)
                    <article class="ch-doc">
                        <a class="ch-doc__profile" href="{{ route('doctors.show', $doctor) }}">
                            @if ($doctor->photo)
                                <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}">
                            @else
                                <span class="ch-doc__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
                            @endif
                            <strong>{{ $doctor->name }}</strong>
                            <em>{{ $doctor->specialization ?: $doctor->department?->name ?: 'Consultant' }}</em>
                            @if ($doctor->experience_years)
                                <p class="ch-doc__meta">{{ $doctor->experience_years }} yrs experience</p>
                            @endif
                        </a>
                        <div class="ch-doc__actions">
                            <a class="ch-doc__book" href="{{ route('doctors.show', $doctor) }}">Profile</a>
                            <a class="ch-doc__book" href="{{ route('home', ['doctor' => $doctor->id]) }}#appointment">Book</a>
                        </div>
                    </article>
                @empty
                    <p class="ch-lead">
                        @if ($isFiltered)
                            No consultants in this clinic yet. Pick another specialty or browse all doctors.
                        @else
                            Consultants will appear here when the desk publishes them.
                        @endif
                    </p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
