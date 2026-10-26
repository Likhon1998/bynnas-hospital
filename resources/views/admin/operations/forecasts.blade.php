@extends('layouts.admin')

@section('title', 'Capacity Forecasts')
@section('heading', 'Capacity Forecasts')

@section('content')
    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--orange"><p class="mc-kpi__label">Total beds</p><p class="mc-kpi__value">{{ $totalBeds }}</p></article>
        <article class="mc-kpi mc-kpi--blue"><p class="mc-kpi__label">Occupied now</p><p class="mc-kpi__value">{{ $occupiedNow }}</p></article>
        <article class="mc-kpi mc-kpi--green"><p class="mc-kpi__label">Available now</p><p class="mc-kpi__value">{{ $availableNow }}</p></article>
        <article class="mc-kpi mc-kpi--navy">
            <p class="mc-kpi__label">Peak load day</p>
            <p class="mc-kpi__value">{{ $peakDay['load'] ?? 0 }}%</p>
            <p class="mc-kpi__meta">{{ $peakDay['day'] ?? '—' }}</p>
        </article>
    </section>

    <section class="mc-card mt-4">
        <div class="mc-card__head">
            <div>
                <h2>7-day capacity outlook</h2>
                <p>Based on current occupancy and booked visits</p>
            </div>
        </div>
        <div class="table-frame mc-table-frame">
            <table class="pro-table mc-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th class="col-num">Booked visits</th>
                        <th class="col-num">Projected occupied</th>
                        <th class="col-num">Projected free</th>
                        <th>Load</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($forecast as $day)
                        <tr>
                            <td class="col-primary">{{ $day['day'] }}</td>
                            <td class="col-num">{{ $day['booked'] }}</td>
                            <td class="col-num">{{ $day['projected_occupied'] }}</td>
                            <td class="col-num">{{ $day['projected_available'] }}</td>
                            <td>
                                <div class="mc-timeline__label"><strong>{{ $day['load'] }}%</strong></div>
                                <div class="mc-timeline__bar mc-timeline__bar--peach"><span style="width: {{ $day['load'] }}%"></span></div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
