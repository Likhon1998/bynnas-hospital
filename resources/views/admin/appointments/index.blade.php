@extends('layouts.admin')
@section('title', 'Appointments')
@section('heading', 'Appointments')
@section('content')
    @if ($errors->any())
        <p class="admin-flash admin-flash--error">{{ $errors->first() }}</p>
    @endif

    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar" data-live-filter>
            <p class="table-count">
                {{ $appointments->total() }} {{ \Illuminate\Support\Str::plural('visit', $appointments->total()) }}
                @if ($selectedDoctor)
                    · {{ $selectedDoctor->name }}
                @endif
                @if ($selectedDate)
                    · {{ $selectedDate->format('d M Y') }}
                @endif
            </p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search appointments">
                <select name="doctor" aria-label="Filter by doctor">
                    <option value="">All doctors</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((int) request('doctor') === $doctor->id)>{{ $doctor->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" value="{{ $selectedDate?->toDateString() }}" aria-label="Filter by date">
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Appointment::STATUSES as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="source">
                    <option value="">All sources</option>
                    @foreach (\App\Models\Appointment::SOURCES as $source => $label)
                        <option value="{{ $source }}" @selected(request('source') === $source)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit">Filter</button>
                @can('appointments.manage')
                    <a href="{{ route('admin.appointments.create') }}" class="admin-action">Take appointment</a>
                @endcan
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
                        <th class="col-open">Action</th>
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
                                @if ($appointment->doctor)
                                    <a href="{{ route('admin.appointments.index', array_filter(['doctor' => $appointment->doctor_id, 'date' => $selectedDate?->toDateString()])) }}">{{ $appointment->doctor->name }}</a>
                                @else
                                    <span>—</span>
                                @endif
                                <span class="cell-sub">{{ $appointment->department?->name ?: '—' }}</span>
                            </td>
                            <td class="col-when">
                                <span>{{ $appointment->appointment_date?->format('d M Y') }}</span>
                                <span class="cell-sub">{{ $appointment->timeRangeLabel() }}</span>
                            </td>
                            <td class="col-from">{{ $appointment->sourceLabel() }}</td>
                            <td class="col-status">
                                @can('appointments.manage')
                                    <x-admin.status-select :appointment="$appointment" />
                                @else
                                    <x-admin.status-pill :value="$appointment->status" />
                                @endcan
                            </td>
                            <td class="col-open">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.appointments.show', $appointment) }}">Open</a>
                                    @if (! empty($whatsappUrls[$appointment->id] ?? null))
                                        <a href="{{ $whatsappUrls[$appointment->id] }}" target="_blank" rel="noopener">WhatsApp</a>
                                    @endif
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty">
                            <td colspan="8">
                                @if ($selectedDoctor && $selectedDate)
                                    No appointments found for {{ $selectedDoctor->name }} on {{ $selectedDate->format('d M Y') }}.
                                @elseif ($selectedDoctor)
                                    No appointments found for {{ $selectedDoctor->name }}.
                                @elseif ($selectedDate)
                                    No appointments found on {{ $selectedDate->format('d M Y') }}.
                                @else
                                    No appointments found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $appointments->links() }}</div>
@endsection
