@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'Departments | '.$hospitalName)
@section('meta_description', 'Specialist clinics at '.$hospitalName.'. Choose a department to see bookable consultants.')

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Departments</h1>
                    <p>Choose a specialty to see bookable doctors.</p>
                </div>
            </div>
            <p class="ch-clinics-hero__count">
                {{ $departments->count() }} {{ \Illuminate\Support\Str::plural('clinic', $departments->count()) }}
                · {{ $consultantTotal }} {{ \Illuminate\Support\Str::plural('consultant', $consultantTotal) }}
            </p>
        </div>
    </section>

    <section class="ch-clinics">
        <div class="ch-wrap">
            <div class="ch-clinics__grid">
                @forelse ($departments as $department)
                    <a
                        class="ch-clinic"
                        href="{{ route('doctors', ['specialty' => $department->name]) }}"
                    >
                        <span class="ch-clinic__top">
                            <span class="ch-clinic__icon"><x-icon :name="$department->icon ?: 'hospital'" /></span>
                            @if ($department->is_featured)
                                <span class="ch-clinic__tag">Featured</span>
                            @endif
                        </span>
                        <h2>{{ $department->name }}</h2>
                        @if (filled($department->description))
                            <p>{{ str($department->description)->limit(110) }}</p>
                        @endif
                        <span class="ch-clinic__go">
                            @if ($department->consultants_count > 0)
                                {{ $department->consultants_count }} {{ \Illuminate\Support\Str::plural('consultant', $department->consultants_count) }} →
                            @else
                                Find doctors →
                            @endif
                        </span>
                    </a>
                @empty
                    <p class="ch-lead">No departments listed yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
