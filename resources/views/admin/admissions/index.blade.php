@extends('layouts.admin')
@section('title', 'Admissions')
@section('heading', 'Admissions')
@section('content')
    <p class="form-lede">One record from arrival to discharge. The bed is released only after authorization and cleaning.</p>

    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--blue">
            <p class="mc-kpi__label">Open</p>
            <p class="mc-kpi__value">{{ $counts['open'] }}</p>
            <p class="mc-kpi__meta">Still in hospital</p>
        </article>
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Ready to release</p>
            <p class="mc-kpi__value">{{ $counts['authorized'] }}</p>
            <p class="mc-kpi__meta">Discharge authorized</p>
        </article>
    </section>

    <div class="table-frame table-frame--fill mt-4">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $admissions->total() }} {{ \Illuminate\Support\Str::plural('admission', $admissions->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search patient or admission no">
                <select name="status">
                    <option value="">Open</option>
                    @foreach (\App\Models\Admission::STATUSES as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit">Filter</button>
                @can('admissions.admit')
                    <a href="{{ route('admin.admissions.create') }}" class="admin-action">Admit patient</a>
                @endcan
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Admission</th>
                        <th>Patient</th>
                        <th>Ward / bed</th>
                        <th>Source</th>
                        <th class="col-status">Status</th>
                        <th class="col-open">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admissions as $admission)
                        <tr>
                            <td>
                                <span class="col-primary">{{ $admission->admission_no }}</span>
                                <span class="cell-sub">{{ $admission->admitted_at?->format('d M H:i') }}</span>
                            </td>
                            <td>
                                <span class="col-primary">{{ $admission->patient?->full_name }}</span>
                                <span class="cell-sub">{{ $admission->attendingDoctor?->name ?: 'Unassigned doctor' }}</span>
                            </td>
                            <td>
                                <span>{{ $admission->bed?->bed_code ?: '—' }}</span>
                                <span class="cell-sub">{{ $admission->ward?->name ?: '—' }}</span>
                            </td>
                            <td>{{ $admission->sourceLabel() }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$admission->status" /></td>
                            <td class="col-open">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.admissions.show', $admission) }}">Open</a>
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No open admissions. Admit from an appointment, emergency, or the floor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $admissions->links() }}</div>
@endsection
