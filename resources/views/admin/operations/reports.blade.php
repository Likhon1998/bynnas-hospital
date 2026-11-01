@extends('layouts.admin')

@section('title', 'Operational Reports')
@section('heading', 'Operational Reports')

@section('content')
    <section class="mc-card">
        <div class="mc-card__head mc-card__head--table">
            <div>
                <h2>Hospital snapshot</h2>
                <p>{{ $label }}</p>
            </div>
            <nav class="mc-status-chips" aria-label="Report period">
                <a href="{{ route('admin.reports', ['period' => 'today']) }}" @class(['is-active' => $period === 'today'])>Today</a>
                <a href="{{ route('admin.reports', ['period' => 'week']) }}" @class(['is-active' => $period === 'week'])>This week</a>
                <a href="{{ route('admin.reports', ['period' => '7d']) }}" @class(['is-active' => $period === '7d'])>Last 7 days</a>
            </nav>
        </div>
    </section>

    <section class="mc-kpis mt-4">
        @foreach ($kpis as $kpi)
            <a href="{{ $kpi['href'] }}" class="mc-kpi mc-kpi--{{ $kpi['tone'] }} mc-kpi--link">
                <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
            </a>
        @endforeach
    </section>

    <div class="mc-layout">
        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2>Bed census</h2>
                    <p>{{ $beds['admitted'] }} admissions in this period</p>
                </div>
                <a href="{{ route('admin.beds.index') }}">Bed board</a>
            </div>
            <div class="table-frame mc-table-frame">
                <table class="pro-table mc-table">
                    <thead>
                        <tr>
                            <th>Ward</th>
                            <th>Type</th>
                            <th class="col-num">Occupied</th>
                            <th class="col-num">Beds</th>
                            <th>Load</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($beds['wards'] as $ward)
                            <tr>
                                <td class="col-primary">{{ $ward['name'] }}</td>
                                <td>{{ $ward['type'] }}</td>
                                <td class="col-num">{{ $ward['occupied'] }}</td>
                                <td class="col-num">{{ $ward['total'] }}</td>
                                <td>
                                    <div class="mc-timeline__label"><strong>{{ $ward['percent'] }}%</strong></div>
                                    <div class="mc-timeline__bar mc-timeline__bar--peach"><span style="width: {{ $ward['percent'] }}%"></span></div>
                                </td>
                            </tr>
                        @empty
                            <tr class="is-empty"><td colspan="5">No active wards on file.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Visits</h2>
                        <p>{{ $from }} to {{ $to }}</p>
                    </div>
                    <a href="{{ route('admin.appointments.index') }}">Open</a>
                </div>
                <ul class="mc-stat-list">
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--blue"></span><span>Active visits</span></div>
                        <em>{{ $visits['total'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--orange"></span><span>Pending</span></div>
                        <em>{{ $visits['pending'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--green"></span><span>Confirmed</span></div>
                        <em>{{ $visits['confirmed'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--sand"></span><span>Completed</span></div>
                        <em>{{ $visits['completed'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--red"></span><span>Emergency desk</span></div>
                        <em>{{ $visits['emergency'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot"></span><span>Cancelled</span></div>
                        <em>{{ $visits['cancelled'] }}</em>
                    </li>
                </ul>
            </section>

            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Billing</h2>
                        <p>{{ $people['patients'] }} patients · {{ $people['doctors'] }} doctors</p>
                    </div>
                    <a href="{{ route('admin.bills.index') }}">Open</a>
                </div>
                <ul class="mc-stat-list">
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--navy"></span><span>Billed</span></div>
                        <em>{{ $billing['billed'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--green"></span><span>Collected</span></div>
                        <em>{{ $billing['collected'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--orange"></span><span>Unpaid now</span></div>
                        <em>{{ $billing['unpaid'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--blue"></span><span>Ready beds</span></div>
                        <em>{{ $beds['ready'] }}</em>
                    </li>
                    <li>
                        <div><span class="mc-stat-list__dot mc-stat-list__dot--sand"></span><span>Turnaround</span></div>
                        <em>{{ $beds['turnaround'] }}</em>
                    </li>
                </ul>
            </section>
        </aside>
    </div>
@endsection
