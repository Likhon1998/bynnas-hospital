@extends('layouts.admin')
@section('title', 'Appointments')
@section('heading', 'Appointments')
@section('content')
    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by number, patient, or doctor" class="w-full max-w-md rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
        <select name="status" class="rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
            <option value="">All statuses</option>
            @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-full bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
    </form>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr>
                    <th class="px-4 py-3">Number</th>
                    <th class="px-4 py-3">Patient</th>
                    <th class="px-4 py-3">Doctor</th>
                    <th class="px-4 py-3">When</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $appointment->appointment_no }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $appointment->patient?->full_name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $appointment->doctor?->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $appointment->appointment_date?->format('d M Y') }} {{ substr((string) $appointment->start_time, 0, 5) }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$appointment->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="font-semibold text-teal-700">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-ink-600">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $appointments->links() }}</div>
@endsection
