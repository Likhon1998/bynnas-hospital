@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'Departments | '.$hospitalName)
@section('meta_description', 'Specialist clinics at '.$hospitalName.'. Choose a department to see the consultants you can book.')

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">Specialist clinics</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>Our Departments</h1>
                    <p>Choose a specialty and we will show the doctors you can book — you do not need a name first.</p>
                </div>
            </div>
            <p class="ch-clinics-hero__count">
                {{ $departments->count() }} {{ \Illuminate\Support\Str::plural('clinic', $departments->count()) }}
                · {{ $consultantTotal }} {{ \Illuminate\Support\Str::plural('consultant', $consultantTotal) }}
            </p>
        </div>
    </section>

    <section class="ch-clinics" data-reveal-scope>
        <div class="ch-wrap">
            <div class="ch-clinics__grid">
                @forelse ($departments as $department)
                    <a
                        class="ch-clinic"
                        href="{{ route('doctors', ['specialty' => $department->name]) }}"
                        data-reveal
                        style="--reveal-delay: {{ min($loop->index * 0.07, 0.42) }}s"
                    >
                        <span class="ch-clinic__top">
                            <span class="ch-clinic__icon"><x-icon :name="$department->icon ?: 'hospital'" /></span>
                            @if ($department->is_featured)
                                <span class="ch-clinic__tag">Featured</span>
                            @endif
                        </span>
                        <h2>{{ $department->name }}</h2>
                        <p>{{ filled($department->description) ? str($department->description)->limit(110) : 'Specialist clinic care at '.$hospitalName.'.' }}</p>
                        <span class="ch-clinic__go">
                            @if ($department->consultants_count > 0)
                                {{ $department->consultants_count }} {{ \Illuminate\Support\Str::plural('consultant', $department->consultants_count) }} →
                            @else
                                Find doctors →
                            @endif
                        </span>
                    </a>
                @empty
                    <p class="ch-lead">Departments will appear here when the desk publishes them.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
