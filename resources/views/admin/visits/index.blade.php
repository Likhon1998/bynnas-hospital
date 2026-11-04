@extends('layouts.admin')
@section('title', 'Visits')
@section('heading', 'Visit desk')
@section('content')
    @if ($errors->any())
        <p class="admin-flash admin-flash--error">{{ $errors->first() }}</p>
    @endif

    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Waiting</p>
            <p class="mc-kpi__value">{{ $counts['waiting'] }}</p>
            <p class="mc-kpi__meta">Checked in</p>
        </article>
        <article class="mc-kpi mc-kpi--blue">
            <p class="mc-kpi__label">Triage</p>
            <p class="mc-kpi__value">{{ $counts['triage'] }}</p>
            <p class="mc-kpi__meta">Vitals recorded</p>
        </article>
        <article class="mc-kpi mc-kpi--green">
            <p class="mc-kpi__label">Consultation</p>
            <p class="mc-kpi__value">{{ $counts['consultation'] }}</p>
            <p class="mc-kpi__meta">With the doctor</p>
        </article>
    </section>

    <div class="table-frame table-frame--fill mt-4">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $visits->total() }} {{ \Illuminate\Support\Str::plural('encounter', $visits->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search patient or visit no">
                <select name="status">
                    <option value="">Open + today</option>
                    @foreach (\App\Models\Visit::STATUSES as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit">Filter</button>
                @can('visits.checkin')
                    <a href="{{ route('admin.visits.create') }}" class="admin-action">Check in walk-in</a>
                @endcan
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Visit</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th class="col-when">Arrived</th>
                        <th class="col-status">Stage</th>
                        <th class="col-open">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr>
                            <td>
                                <span class="col-primary">{{ $visit->visit_no }}</span>
                                <span class="cell-sub">{{ $visit->chief_complaint ?: 'Encounter' }}</span>
                            </td>
                            <td>
                                <span class="col-primary">{{ $visit->patient?->full_name }}</span>
                                <span class="cell-sub">{{ $visit->patient?->phone }}</span>
                            </td>
                            <td>
                                <span>{{ $visit->doctor?->name ?: 'Unassigned' }}</span>
                                <span class="cell-sub">{{ $visit->department?->name ?: '—' }}</span>
                            </td>
                            <td class="col-when">{{ $visit->arrived_at?->format('H:i') ?: '—' }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$visit->status" /></td>
                            <td class="col-open">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.visits.show', $visit) }}">Open</a>
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No open encounters. Check in a booked appointment or a walk-in.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $visits->links() }}</div>
@endsection
