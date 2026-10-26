@extends('layouts.admin')

@section('title', 'Operational Reports')
@section('heading', 'Operational Reports')

@section('content')
    <section class="mc-kpis">
        @foreach ($stats as $stat)
            <article class="mc-kpi mc-kpi--navy">
                <p class="mc-kpi__label">{{ $stat['label'] }}</p>
                <p class="mc-kpi__value">{{ $stat['value'] }}</p>
            </article>
        @endforeach
    </section>
@endsection
