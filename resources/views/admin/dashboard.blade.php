@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Operations Desk')

@section('content')
    @php
        $chartMax = max(1, collect($weeklyStatus)->max('total') ?: 1);
        $tabTotal = ($statusTabs['pending'] ?? 0) + ($statusTabs['confirmed'] ?? 0) + ($statusTabs['completed'] ?? 0) + ($statusTabs['cancelled'] ?? 0);
    @endphp

    <p class="mc-subhead">{{ $deskSummary }}</p>

    @if ($kpis !== [])
        <section class="mc-kpis">
            @foreach ($kpis as $kpi)
                <a href="{{ $kpi['href'] ?: '#' }}" class="mc-kpi mc-kpi--{{ $kpi['tone'] }}">
                    <div class="mc-kpi__top">
                        <div>
                            <p class="mc-kpi__label">{{ $kpi['label'] }}</p>
                            <p class="mc-kpi__value">{{ $kpi['value'] }}</p>
                            <p class="mc-kpi__meta">{{ $kpi['meta'] }}</p>
                        </div>
                        @if ($kpi['chart'] === 'donut')
                            <span class="mc-donut" style="--p: {{ $kpi['percent'] }}">
                                <em>{{ $kpi['percent'] }}%</em>
                            </span>
                        @elseif ($kpi['chart'] === 'bar')
                            <span class="mc-kpi__pct">{{ $kpi['percent'] }}%</span>
                        @endif
                    </div>
                    @if ($kpi['chart'] === 'bar')
                        <div class="mc-progress"><span style="width: {{ $kpi['percent'] }}%"></span></div>
                    @elseif ($kpi['chart'] === 'none')
                        <div class="mc-progress"><span style="width: {{ $kpi['percent'] }}%"></span></div>
                    @endif
                    <p @class(['mc-kpi__trend', 'is-up' => $kpi['trendUp'], 'is-down' => ! $kpi['trendUp']])>{{ $kpi['trend'] }}</p>
                </a>
            @endforeach
        </section>
    @endif

    <div class="mc-layout">
        <div class="mc-main">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Visit status</h2>
                        <p>Weekly appointments by status</p>
                    </div>
                    <div class="mc-seg">
                        <span class="is-active">Weekly</span>
                        <span>Daily</span>
                    </div>
                </div>

                <div class="mc-legend">
                    <span><i class="is-pending"></i> Pending</span>
                    <span><i class="is-confirmed"></i> Confirmed</span>
                    <span><i class="is-completed"></i> Completed</span>
                    <span><i class="is-cancelled"></i> Cancelled</span>
                </div>

                <div class="mc-chart">
                    @foreach ($weeklyStatus as $day)
                        @php $height = (int) max(8, round(($day['total'] / $chartMax) * 100)); @endphp
                        <div class="mc-chart__col">
                            <div class="mc-chart__stack" style="height: {{ $height }}%">
                                @for ($i = 0; $i < min(8, $day['pending']); $i++)
                                    <span class="is-pending"></span>
                                @endfor
                                @for ($i = 0; $i < min(8, $day['confirmed']); $i++)
                                    <span class="is-confirmed"></span>
                                @endfor
                                @for ($i = 0; $i < min(8, $day['completed']); $i++)
                                    <span class="is-completed"></span>
                                @endfor
                                @for ($i = 0; $i < min(8, $day['cancelled']); $i++)
                                    <span class="is-cancelled"></span>
                                @endfor
                                @if ($day['total'] === 0)
                                    <span class="is-empty"></span>
                                @endif
                            </div>
                            <strong>{{ $day['day'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>

            @can('appointments.view')
                <section class="mc-card">
                    <div class="mc-card__head mc-card__head--table">
                        <div>
                            <h2>Upcoming appointments</h2>
                            <p>Live desk queue</p>
                        </div>
                        <div class="mc-table-tools">
                            <a href="{{ route('admin.appointments.index') }}" class="mc-filter-btn">View all</a>
                        </div>
                    </div>

                    <div class="mc-tabs">
                        <a href="{{ route('admin.appointments.index') }}" class="is-active">All ({{ $tabTotal }})</a>
                        <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">Pending ({{ $statusTabs['pending'] ?? 0 }})</a>
                        <a href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}">Confirmed ({{ $statusTabs['confirmed'] ?? 0 }})</a>
                        <a href="{{ route('admin.appointments.index', ['status' => 'completed']) }}">Completed ({{ $statusTabs['completed'] ?? 0 }})</a>
                    </div>

                    <div class="table-frame mc-table-frame">
                        <table class="pro-table mc-table">
                            <thead>
                                <tr>
                                    <th>Visit ID</th>
                                    <th>Patient</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-when">When</th>
                                    <th>Department</th>
                                    <th class="col-open">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($upcomingAppointments as $appointment)
                                    <tr>
                                        <td class="col-primary">{{ $appointment->appointment_no }}</td>
                                        <td>
                                            <span class="col-primary">{{ $appointment->patient?->full_name }}</span>
                                            <span class="cell-sub">{{ $appointment->patient?->phone ?: '—' }}</span>
                                        </td>
                                        <td class="col-status">
                                            @can('appointments.manage')
                                                <x-admin.status-select :appointment="$appointment" />
                                            @else
                                                <x-admin.status-pill :value="$appointment->status" />
                                            @endcan
                                        </td>
                                        <td class="col-when">
                                            <span>{{ $appointment->appointment_date?->format('d M Y') }}</span>
                                            <span class="cell-sub">{{ $appointment->start_time ? \Illuminate\Support\Carbon::parse((string) $appointment->start_time)->format('g:i A') : '—' }}</span>
                                        </td>
                                        <td>{{ $appointment->department?->name ?: '—' }}</td>
                                        <td class="col-open">
                                            <a href="{{ route('admin.appointments.show', $appointment) }}">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="is-empty"><td colspan="6">No upcoming appointments.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endcan

            @can('billing.manage')
                <section class="mc-card">
                    <div class="mc-card__head">
                        <div>
                            <h2>Patient billing</h2>
                            <p>Recent bills</p>
                        </div>
                        <div class="mc-table-tools">
                            <a href="{{ route('admin.bills.index') }}">All bills</a>
                            <a href="{{ route('admin.bills.create') }}" class="mc-filter-btn">Generate bill</a>
                        </div>
                    </div>
                    <div class="table-frame mc-table-frame">
                        <table class="pro-table mc-table">
                            <thead>
                                <tr>
                                    <th>Bill</th>
                                    <th>Patient</th>
                                    <th class="col-num">Amount</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-when">Date</th>
                                    <th class="col-open">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentBills as $bill)
                                    <tr>
                                        <td class="col-primary">{{ $bill->bill_no }}</td>
                                        <td>{{ $bill->patient?->full_name }}</td>
                                        <td class="col-num">{{ number_format((float) $bill->total, 2) }}</td>
                                        <td class="col-status"><x-admin.status-pill :value="$bill->status" /></td>
                                        <td class="col-when">{{ $bill->bill_date?->format('d M Y') }}</td>
                                        <td class="col-open"><a href="{{ route('admin.bills.show', $bill) }}">View</a></td>
                                    </tr>
                                @empty
                                    <tr class="is-empty"><td colspan="6">No bills yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endcan
        </div>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Visit mix</h2>
                        <p>Status share</p>
                    </div>
                </div>
                <div class="mc-bubbles">
                    @foreach ($statusMix as $index => $item)
                        <span class="mc-bubble mc-bubble--{{ $item['tone'] }} mc-bubble--{{ $index === 0 ? 'lg' : ($index === 1 ? 'md' : 'sm') }}">
                            {{ $item['percent'] }}%
                        </span>
                    @endforeach
                </div>
                <ul class="mc-mix-list">
                    @foreach ($statusMix as $item)
                        <li>
                            <span><i class="is-{{ $item['tone'] }}"></i>{{ $item['label'] }}</span>
                            <strong>{{ $item['percent'] }}%</strong>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($careTimeline !== [])
                <section class="mc-card">
                    <div class="mc-card__head">
                        <div>
                            <h2>Care timeline</h2>
                            <p>Desk progress</p>
                        </div>
                    </div>
                    <div class="mc-timeline">
                        @foreach ($careTimeline as $item)
                            <div>
                                <div class="mc-timeline__label">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ $item['percent'] }}%</strong>
                                </div>
                                <div class="mc-timeline__bar mc-timeline__bar--{{ $item['tone'] }}">
                                    <span style="width: {{ $item['percent'] }}%"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @canany(['appointments.view', 'patients.view', 'doctors.manage', 'billing.manage'])
                <section class="mc-card">
                    <div class="mc-card__head">
                        <div>
                            <h2>Open</h2>
                            <p>Shortcuts</p>
                        </div>
                    </div>
                    <nav class="mc-links">
                        @can('appointments.view')
                            <a href="{{ route('admin.appointments.index') }}">Appointments</a>
                        @endcan
                        @can('patients.view')
                            <a href="{{ route('admin.patients.index') }}">Patients</a>
                        @endcan
                        @can('doctors.manage')
                            <a href="{{ route('admin.doctors.create') }}">Add doctor</a>
                        @endcan
                        @can('billing.manage')
                            <a href="{{ route('admin.bills.create') }}">Generate bill</a>
                        @endcan
                    </nav>
                </section>
            @endcanany
        </aside>
    </div>
@endsection
