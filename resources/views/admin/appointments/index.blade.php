@extends('layouts.admin')
@section('title', 'Appointments')
@section('heading', 'Appointments')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $appointments->total() }} {{ \Illuminate\Support\Str::plural('visit', $appointments->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search appointments">
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No show'] as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="source">
                    <option value="">All sources</option>
                    <option value="web" @selected(request('source') === 'web')>Website</option>
                    <option value="admin" @selected(request('source') === 'admin')>Desk</option>
                </select>
                <button type="submit">Filter</button>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Visit</th>
                        <th>Patient</th>
                        <th>Clinician</th>
                        <th class="col-when">When</th>
                        <th class="col-from">From</th>
                        <th class="col-status">Status</th>
                        <th class="col-open">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td class="col-idx">{{ ($appointments->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $appointment->appointment_no }}</span>
                                <span class="cell-sub">{{ $appointment->reason ?: 'Visit request' }}</span>
                            </td>
                            <td>
                                <span class="col-primary">{{ $appointment->patient?->full_name }}</span>
                                <span class="cell-sub">{{ $appointment->patient?->phone }}</span>
                            </td>
                            <td>
                                <span>{{ $appointment->doctor?->name }}</span>
                                <span class="cell-sub">{{ $appointment->department?->name ?: '—' }}</span>
                            </td>
                            <td class="col-when">
                                <span>{{ $appointment->appointment_date?->format('d M Y') }}</span>
                                <span class="cell-sub">{{ substr((string) $appointment->start_time, 0, 5) }}–{{ substr((string) $appointment->end_time, 0, 5) }}</span>
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
                        <tr class="is-empty"><td colspan="8">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $appointments->links() }}</div>
@endsection
