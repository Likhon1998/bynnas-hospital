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
                    <h2 class="mc-card__title">
                        <x-admin.nav-icon name="ambulance" class="h-4 w-4" />
                        <span>Emergency visits</span>
                    </h2>
                    <p>Today’s urgent desk queue</p>
                </div>
                <a href="{{ route('admin.appointments.index') }}">All appointments</a>
            </div>
            <div class="table-frame table-frame--fit mc-table-frame">
                <table class="pro-table mc-table">
                    <thead>
                        <tr>
                            <th>Visit</th>
                            <th>Patient</th>
                            <th>Why</th>
                            <th class="col-status">Status</th>
                            <th class="col-pair-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emergencyVisits as $visit)
                            <tr>
                                <td class="col-primary">
                                    <strong>{{ $visit->appointment_no }}</strong>
                                    <span class="mc-cell-sub">{{ \Illuminate\Support\Str::of((string) $visit->start_time)->substr(0, 5) }} · {{ $visit->department?->name ?: 'Emergency' }}</span>
                                </td>
                                <td>{{ $visit->patient?->full_name }}</td>
                                <td>{{ $visit->reason ?: 'Walk-in emergency' }}</td>
                                <td class="col-status"><x-admin.status-pill :value="$visit->status" /></td>
                                <td class="col-pair-actions">
                                    <div class="table-actions">
                                        <a href="{{ route('admin.appointments.show', $visit) }}" class="mc-btn mc-btn--ghost">Open</a>
                                        @can('admissions.admit')
                                            <a href="{{ route('admin.admissions.create', ['source' => 'emergency', 'appointment_id' => $visit->id]) }}" class="mc-btn mc-btn--primary">Admit</a>
                                        @endcan
                                    </div>
                                </td>
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
                        <h2 class="mc-card__title">
                            <x-admin.nav-icon name="bed" class="h-4 w-4" />
                            <span>Ready beds</span>
                        </h2>
                        <p>Assign from floor board</p>
                    </div>
                    <a href="{{ route('admin.beds.index') }}">Bed &amp; Wards</a>
                </div>
                <nav class="mc-bed-list">
                    @forelse ($freeEmergencyBeds as $bed)
                        <a href="{{ route('admin.beds.index', ['q' => $bed->bed_code]) }}" class="mc-bed-link">
                            <strong>{{ $bed->bed_code }}</strong>
                            <span>{{ $bed->ward?->name ?: 'Ward' }}</span>
                        </a>
                    @empty
                        <p class="mc-empty">No free beds in emergency-capable wards.</p>
                    @endforelse
                </nav>
            </section>
        </aside>
    </div>
@endsection
