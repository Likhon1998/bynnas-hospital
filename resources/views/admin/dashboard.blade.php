@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <section class="dash-head">
        <div>
            <p class="dash-head__kicker">Admin command desk</p>
            <h1>Welcome back, {{ $staffDisplayName }}</h1>
            <p class="dash-head__copy">{{ $deskSummary }}</p>
        </div>
        <div class="dash-head__meta">
            <span>{{ now()->format('l') }}</span>
            <strong>{{ now()->format('d M Y') }}</strong>
        </div>
        @if ($deskActions !== [])
            <div class="dash-head__actions">
                @foreach ($deskActions as $action)
                    <a href="{{ $action['href'] }}" @class(['dash-btn', 'dash-btn--primary' => $action['primary']])>
                        <x-admin.nav-icon :name="$action['icon']" />
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($stats !== [])
        <section class="dash-kpis">
            @foreach ($stats as $stat)
                <a href="{{ $stat['href'] }}" class="dash-kpi">
                    <span class="dash-kpi__icon dash-kpi__icon--{{ $stat['tone'] }}">
                        <x-admin.nav-icon :name="$stat['icon']" />
                    </span>
                    <span>
                        <span class="dash-kpi__value">{{ number_format($stat['value']) }}</span>
                        <span class="dash-kpi__label">{{ $stat['label'] }}</span>
                        <span class="dash-kpi__note">{{ $stat['note'] }}</span>
                    </span>
                </a>
            @endforeach
        </section>
    @endif

    @if ($needsYou !== [])
        <section class="dash-needs" aria-label="Needs attention">
            <p><x-admin.nav-icon name="clock" /> Needs attention</p>
            <div>
                @foreach ($needsYou as $item)
                    <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                @endforeach
            </div>
        </section>
    @endif

    @can('appointments.view')
        <section class="dash-card dash-card--primary">
            <div class="dash-card__head">
                <div>
                    <h2><x-admin.nav-icon name="calendar" /> Upcoming visits</h2>
                    <p>Confirm arrivals, check sources, and open visit details from one list.</p>
                </div>
                <a href="{{ route('admin.appointments.index') }}">All appointments</a>
            </div>
            <div class="table-frame">
                <table class="pro-table">
                    <thead>
                        <tr>
                            <th>Visit</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th class="col-when">When</th>
                            <th class="col-from">From</th>
                            <th class="col-status">Status</th>
                            <th class="col-open">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingAppointments as $appointment)
                            <tr>
                                <td>
                                    <span class="col-primary">{{ $appointment->appointment_no }}</span>
                                    <span class="cell-sub">{{ $appointment->reason ?: 'Visit' }}</span>
                                </td>
                                <td>
                                    <span class="col-primary">{{ $appointment->patient?->full_name }}</span>
                                    <span class="cell-sub">{{ $appointment->patient?->phone ?: '—' }}</span>
                                </td>
                                <td>
                                    <span>{{ $appointment->doctor?->name }}</span>
                                    <span class="cell-sub">{{ $appointment->department?->name ?: '—' }}</span>
                                </td>
                                <td class="col-when">
                                    <span>{{ $appointment->appointment_date?->format('d M Y') }}</span>
                                    <span class="cell-sub">{{ $appointment->start_time ? \Illuminate\Support\Carbon::parse((string) $appointment->start_time)->format('g:i A') : '—' }}</span>
                                </td>
                                <td class="col-from">{{ $appointment->source === 'web' ? 'Website' : 'Desk' }}</td>
                                <td class="col-status">
                                    @can('appointments.manage')
                                        <x-admin.status-select :appointment="$appointment" />
                                    @else
                                        <x-admin.status-pill :value="$appointment->status" />
                                    @endcan
                                </td>
                                <td class="col-open">
                                    <a href="{{ route('admin.appointments.show', $appointment) }}">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="is-empty"><td colspan="7">Nothing booked from today onward.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endcan

    @can('billing.manage')
        <section class="dash-card dash-billing">
            <div class="dash-card__head">
                <div>
                    <h2><x-admin.nav-icon name="receipt" /> Open bills</h2>
                    <p>Unpaid or draft bills that still need desk action.</p>
                </div>
                <div class="dash-card__links">
                    <a href="{{ route('admin.bills.index') }}">All bills</a>
                    <a href="{{ route('admin.bills.create') }}">New bill</a>
                </div>
            </div>
            <div class="dash-bill-list">
                @forelse ($openBills as $bill)
                    <a href="{{ route('admin.bills.show', $bill) }}" class="dash-bill">
                        <span>
                            <strong>{{ $bill->bill_no }}</strong>
                            <small>{{ $bill->patient?->full_name }} · {{ $bill->bill_date?->format('d M Y') }}</small>
                        </span>
                        <span>
                            <b>{{ number_format((float) $bill->total, 2) }}</b>
                            <x-admin.status-pill :value="$bill->status" />
                        </span>
                    </a>
                @empty
                    <p class="dash-empty">No unpaid bills.</p>
                @endforelse
            </div>
        </section>
    @endcan
@endsection
