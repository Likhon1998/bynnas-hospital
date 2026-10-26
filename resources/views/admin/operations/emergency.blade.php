@extends('layouts.admin')

@section('title', 'Emergency Flow')
@section('heading', 'Emergency Flow')

@section('content')
    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Pending emergency queue</p>
            <p class="mc-kpi__value">{{ $pendingCount }}</p>
            <p class="mc-kpi__trend is-up">Needs triage today</p>
        </article>
        <article class="mc-kpi mc-kpi--green">
            <p class="mc-kpi__label">Open beds ready</p>
            <p class="mc-kpi__value">{{ $availableBeds }}</p>
            <p class="mc-kpi__trend is-up">ICU / general / isolation</p>
        </article>
    </section>

    <div class="mc-layout">
        <section class="mc-card">
            <div class="mc-card__head">
                <div>
                    <h2>Emergency visits</h2>
                    <p>Today’s urgent desk queue</p>
                </div>
                <a href="{{ route('admin.appointments.index') }}">All appointments</a>
            </div>
            <div class="table-frame mc-table-frame">
                <table class="pro-table mc-table">
                    <thead>
                        <tr>
                            <th>Visit</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th class="col-status">Status</th>
                            <th class="col-open">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emergencyVisits as $visit)
                            <tr>
                                <td class="col-primary">{{ $visit->appointment_no }}</td>
                                <td>{{ $visit->patient?->full_name }}</td>
                                <td>{{ $visit->doctor?->name ?: '—' }}</td>
                                <td class="col-status"><x-admin.status-pill :value="$visit->status" /></td>
                                <td class="col-open"><a href="{{ route('admin.appointments.show', $visit) }}">Open</a></td>
                            </tr>
                        @empty
                            <tr class="is-empty"><td colspan="5">No emergency queue items for today.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Ready beds</h2>
                        <p>Assign from floor board</p>
                    </div>
                    <a href="{{ route('admin.beds.index') }}">Bed & Wards</a>
                </div>
                <nav class="mc-links">
                    @forelse ($freeEmergencyBeds as $bed)
                        <a href="{{ route('admin.beds.index', ['q' => $bed->bed_code]) }}">{{ $bed->bed_code }} · {{ $bed->ward?->name }}</a>
                    @empty
                        <p class="mc-empty">No free beds in emergency-capable wards.</p>
                    @endforelse
                </nav>
            </section>
        </aside>
    </div>
@endsection
