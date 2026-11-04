@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $resultLabel = $specialty !== '' ? $specialty : ($searchedName !== '' ? $searchedName : null);
    $sortLabels = [
        'featured' => 'Featured first',
        'name' => 'Name A–Z',
        'experience' => 'Most experience',
        'fee' => 'Fee: low to high',
    ];
@endphp

@section('title', 'Find a doctor | '.$hospitalName)
@section('meta_description', 'Find a consultant at '.$hospitalName.'. Search by name or specialty, then open a profile and book a visit.')

@section('content')
    {{-- 1. Hero --}}
    <section class="ch-clinics-hero ch-dir-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Consultant directory</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Find a doctor</h1>
                    <p>Search by name or specialty, open a consultant profile, then book a free slot at {{ $hospitalName }}.</p>
                </div>
            </div>
            <div class="ch-dir-hero__stats" aria-label="Directory summary">
                <p><strong>{{ $totalConsultants }}</strong> {{ \Illuminate\Support\Str::plural('consultant', $totalConsultants) }}</p>
                <p><strong>{{ $clinicCount }}</strong> {{ \Illuminate\Support\Str::plural('clinic', $clinicCount) }}</p>
                <p><strong>{{ $doctors->count() }}</strong> showing now</p>
            </div>
        </div>
    </section>

    {{-- 2. Search desk --}}
    <section class="ch-dir-search" id="directory-search">
        <div class="ch-wrap">
            <div class="ch-dir-search__panel">
                <div class="ch-dir-search__head">
                    <div>
                        <p class="ch-find__kicker">Search desk</p>
                        <h2 class="ch-sec__title">Narrow the roster</h2>
                        <p class="ch-depts-intro">Type a name, pick a specialty, or sort the list. Filters update the results below.</p>
                    </div>
                    @if ($isFiltered)
                        <a class="ch-btn" href="{{ route('doctors') }}">Clear all filters</a>
                    @endif
                </div>

                <form class="ch-dir-form" action="{{ route('doctors') }}" method="get" data-doctor-directory>
                    <label class="ch-dir-form__field ch-dir-form__field--grow">
                        <span>Doctor name</span>
                        <input
                            class="ch-input"
                            type="search"
                            name="q"
                            value="{{ $searchedName }}"
                            placeholder="e.g. Hamida, Cardiology"
                            autocomplete="off"
                        >
                    </label>

                    <label class="ch-dir-form__field">
                        <span>Specialty</span>
                        <select class="ch-input" name="specialty" data-doctor-specialty>
                            <option value="">All specialties</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->name }}" @selected($specialty === $department->name)>
                                    {{ $department->name }}@if ($department->consultants_count) ({{ $department->consultants_count }})@endif
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="ch-dir-form__field">
                        <span>Sort by</span>
                        <select class="ch-input" name="sort">
                            @foreach ($sortLabels as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="ch-dir-form__check">
                        <input type="checkbox" name="featured" value="1" @checked($featuredOnly)>
                        <span>Featured only</span>
                    </label>

                    <button type="submit" class="ch-btn ch-btn--solid ch-dir-form__go">Show doctors</button>
                </form>

                @if ($isFiltered)
                    <div class="ch-dir-active" aria-live="polite">
                        <span>Active:</span>
                        @if ($searchedName !== '')
                            <span class="ch-dir-active__chip">Name: {{ $searchedName }}</span>
                        @endif
                        @if ($specialty !== '')
                            <span class="ch-dir-active__chip">Clinic: {{ $specialty }}</span>
                        @endif
                        @if ($featuredOnly)
                            <span class="ch-dir-active__chip">Featured</span>
                        @endif
                        <span class="ch-dir-active__chip">{{ $sortLabels[$sort] ?? 'Featured first' }}</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- 3. Browse by clinic --}}
    @if ($departments->isNotEmpty())
        <section class="ch-dir-clinics" id="browse-clinics">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Browse by clinic</p>
                        <h2 class="ch-sec__title">Choose a specialty</h2>
                        <p class="ch-depts-intro">Jump straight to consultants in one department.</p>
                    </div>
                    <a class="ch-more" href="{{ route('departments') }}">All departments →</a>
                </div>

                <div class="ch-dir-clinics__grid">
                    <a
                        href="{{ route('doctors', array_filter(['q' => $searchedName ?: null, 'sort' => $sort !== 'featured' ? $sort : null, 'featured' => $featuredOnly ? 1 : null])) }}"
                        @class(['ch-dir-clinic', 'is-active' => $specialty === ''])
                    >
                        <strong>All clinics</strong>
                        <span>{{ $totalConsultants }} consultants</span>
                    </a>
                    @foreach ($departments as $department)
                        <a
                            href="{{ route('doctors', array_filter([
                                'specialty' => $department->name,
                                'q' => $searchedName ?: null,
                                'sort' => $sort !== 'featured' ? $sort : null,
                                'featured' => $featuredOnly ? 1 : null,
                            ])) }}"
                            @class(['ch-dir-clinic', 'is-active' => $specialty === $department->name])
                        >
                            <span class="ch-dir-clinic__icon"><x-icon :name="$department->icon ?: 'hospital'" /></span>
                            <strong>{{ $department->name }}</strong>
                            <span>{{ $department->consultants_count }} {{ \Illuminate\Support\Str::plural('consultant', $department->consultants_count) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Featured (browse mode only) --}}
    @if (($featuredDoctors ?? collect())->isNotEmpty())
        <section class="ch-dir-featured" id="featured-consultants">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Highlighted roster</p>
                        <h2 class="ch-sec__title">Featured consultants</h2>
                        <p class="ch-depts-intro">Names the desk keeps at the front of the directory.</p>
                    </div>
                    <a class="ch-more" href="{{ route('doctors', ['featured' => 1]) }}">View featured only →</a>
                </div>

                <div class="ch-dir-grid">
                    @foreach ($featuredDoctors as $doctor)
                        @include('web.partials.doctor-directory-card', ['doctor' => $doctor, 'weekdayToday' => $weekdayToday])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 5. Results --}}
    <section class="ch-dir-results" id="doctor-results">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">Directory results</p>
                    <h2 class="ch-sec__title">
                        @if ($isFiltered && $resultLabel)
                            Showing consultants for {{ $resultLabel }}
                        @elseif ($featuredOnly)
                            Featured consultants
                        @else
                            All consultants
                        @endif
                    </h2>
                    <p class="ch-depts-intro">
                        {{ $doctors->count() }} {{ \Illuminate\Support\Str::plural('match', $doctors->count()) }}
                        · sorted by {{ strtolower($sortLabels[$sort] ?? 'featured first') }}
                    </p>
                </div>
            </div>

            @forelse ($groupedDoctors as $groupName => $groupDoctors)
                <div class="ch-dir-group" id="clinic-{{ \Illuminate\Support\Str::slug($groupName) }}">
                    @if (! $isFiltered || $specialty === '' || $groupedDoctors->count() > 1)
                        <div class="ch-dir-group__head">
                            <h3>{{ $groupName }}</h3>
                            <span>{{ $groupDoctors->count() }} {{ \Illuminate\Support\Str::plural('consultant', $groupDoctors->count()) }}</span>
                        </div>
                    @endif

                    <div class="ch-dir-grid">
                        @foreach ($groupDoctors as $doctor)
                            @include('web.partials.doctor-directory-card', ['doctor' => $doctor, 'weekdayToday' => $weekdayToday])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="ch-dir-empty">
                    <h3>No consultants in this clinic yet</h3>
                    <p>
                        @if ($isFiltered)
                            Pick another specialty, clear the name search, or browse all doctors.
                        @else
                            Consultants will appear here when the desk publishes them.
                        @endif
                    </p>
                    @if ($isFiltered)
                        <a class="ch-btn ch-btn--solid" href="{{ route('doctors') }}">Browse all doctors</a>
                    @endif
                </div>
            @endforelse
        </div>
    </section>

    {{-- 6. Help --}}
    <section class="ch-dir-help">
        <div class="ch-wrap ch-dir-help__row">
            <div>
                <p class="ch-find__kicker">Need a hand</p>
                <h2 class="ch-sec__title">Not sure which consultant to pick?</h2>
                <p class="ch-depts-intro">Call the desk with your symptoms or papers. Reception can point you to the right clinic and the next free slot.</p>
            </div>
            <div class="ch-dir-help__actions">
                @if ($phone)
                    <a class="ch-btn ch-btn--solid" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
                @endif
                <a class="ch-btn" href="{{ route('emergency') }}">Emergency desk</a>
            </div>
        </div>
    </section>
@endsection
