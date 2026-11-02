@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
@endphp

@section('title', 'Health packages | '.$hospitalName)
@section('meta_description', 'Screening packages published by '.$hospitalName.'.')

@section('content')
    <x-web.page-intro
        kicker="Screening"
        title="Health packages"
        note="Compare what the desk currently offers. Book a named consultant after you choose a package."
    />

    <section class="site-block">
        <div class="web-wrap">
            @if ($packages->isEmpty())
                <p class="ch-lead">Packages will appear here when the desk publishes them.</p>
            @else
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($packages as $package)
                        <x-package-card :package="$package" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
