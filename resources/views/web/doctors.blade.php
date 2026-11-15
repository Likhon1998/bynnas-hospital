@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $directoryView = $directoryView ?? 'department';
    $resultLabel = $specialty !== '' ? $specialty : ($searchedName !== '' ? $searchedName : null);
    $viewLabels = [
        'department' => 'Department Wise',
        'branch' => 'Branch Wise',
        'name' => 'Doctor Name Wise',
    ];
    $sortLabels = [
        'featured' => 'Featured first',
        'name' => 'Name A–Z',
        'experience' => 'Most experience',
        'fee' => 'Fee: low to high',
    ];
@endphp

@section('title', 'Find a doctor | '.$hospitalName)
@section('meta_description', 'Find a consultant at '.$hospitalName.'. Search by department, branch, or doctor name.')

@section('content')
    <section class="ch-clinics-hero ch-dir-hero">
        <div class="ch-wrap">
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Find a doctor</h1>
                    <p>Choose how you want to browse, then pick a consultant.</p>
                </div>
            </div>

            <nav class="ch-dir-modes" aria-label="Find a doctor by">
                @foreach ($viewLabels as $key => $label)
                    <a
                        href="{{ route('doctors', ['view' => $key]) }}"
                        @class(['ch-dir-mode', 'is-active' => $directoryView === $key])
                    >{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="ch-dir-search" id="directory-search">
        <div class="ch-wrap">
            <form class="ch-dir-search__bar" action="{{ route('doctors') }}" method="get" data-doctor-directory data-live-filter>
                <input type="hidden" name="view" value="{{ $directoryView }}">

                <label class="ch-dir-form__field ch-dir-form__field--grow">
                    <span>{{ $directoryView === 'name' ? 'Doctor name' : 'Search' }}</span>
                    <input
                        class="ch-input"
                        type="search"
                        name="q"
                        value="{{ $searchedName }}"
                        placeholder="{{ $directoryView === 'name' ? 'Type a doctor name' : 'Name or specialty' }}"
                        autocomplete="off"
                        data-doctor-search
                        @if ($directoryView === 'name') autofocus @endif
                    >
                </label>

                @if ($directoryView === 'department')
                    <label class="ch-dir-form__field">
                        <span>Department</span>
                        <select class="ch-input" name="specialty">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->name }}" @selected($specialty === $department->name)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endif

                @if ($directoryView === 'branch')
                    <label class="ch-dir-form__field">
                        <span>Branch</span>
                        <select class="ch-input" name="branch">
                            <option value="">All branches</option>
                            @foreach ($branches as $item)
                                <option value="{{ $item['key'] }}" @selected($branch === $item['key'])>{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <label class="ch-dir-form__field">
                    <span>Sort</span>
                    <select class="ch-input" name="sort">
                        @foreach ($sortLabels as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="ch-dir-search__actions">
                    <button type="submit" class="ch-btn ch-btn--solid ch-dir-form__go">Search</button>
                    @if ($isFiltered)
                        <a class="ch-btn ch-dir-clear" href="{{ route('doctors', ['view' => $directoryView]) }}">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <section class="ch-dir-results" id="doctor-results">
        <div class="ch-wrap">
            <div class="ch-dir-results__head">
                <h2>
                    @if ($isFiltered && $resultLabel)
                        Results for {{ $resultLabel }}
                    @elseif ($directoryView === 'name')
                        Doctors A–Z
                    @elseif ($directoryView === 'branch')
                        Doctors by branch
                    @else
                        All doctors
                    @endif
                </h2>
                <p>{{ $doctors->count() }} {{ \Illuminate\Support\Str::plural('doctor', $doctors->count()) }}</p>
            </div>

            @forelse ($groupedDoctors as $groupName => $groupDoctors)
                <div
                    class="ch-dir-group"
                    id="{{ $directoryView === 'name' ? 'letter-'.\Illuminate\Support\Str::slug($groupName) : 'clinic-'.\Illuminate\Support\Str::slug($groupName) }}"
                >
                    @if ($directoryView === 'name' || $directoryView === 'branch' || $specialty === '' || $groupedDoctors->count() > 1)
                        <div class="ch-dir-group__head">
                            <h3>{{ $groupName }}</h3>
                            <span>{{ $groupDoctors->count() }}</span>
                        </div>
                    @endif

                    <div class="ch-dir-list">
                        @foreach ($groupDoctors as $doctor)
                            @include('web.partials.doctor-directory-card', ['doctor' => $doctor, 'weekdayToday' => $weekdayToday])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="ch-dir-empty">
                    <h3>No doctors found</h3>
                    <p>
                        @if ($isFiltered)
                            Try another search or clear filters.
                        @else
                            No doctors listed yet.
                        @endif
                    </p>
                    @if ($isFiltered)
                        <a class="ch-btn ch-btn--solid" href="{{ route('doctors', ['view' => $directoryView]) }}">Show all doctors</a>
                    @endif
                </div>
            @endforelse
        </div>
    </section>

    @if ($phone)
        <section class="ch-dir-help">
            <div class="ch-wrap ch-dir-help__row">
                <p>Need help choosing a doctor? Call reception.</p>
                <a class="ch-btn ch-btn--solid" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
            </div>
        </section>
    @endif
@endsection
