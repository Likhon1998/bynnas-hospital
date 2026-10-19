@extends('layouts.admin')
@section('title', $appointment->appointment_no)
@section('heading', $appointment->appointment_no)
@section('content')
    <div class="grid gap-6 xl:grid-cols-2">
        <article class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <p class="text-sm text-ink-600">Patient</p>
            <p class="font-medium text-navy-900">{{ $appointment->patient?->full_name }}</p>
            <p class="mt-4 text-sm text-ink-600">Doctor</p>
            <p class="font-medium text-navy-900">{{ $appointment->doctor?->name }} · {{ $appointment->department?->name }}</p>
            <p class="mt-4 text-sm text-ink-600">Schedule</p>
            <p class="font-medium text-navy-900">{{ $appointment->appointment_date?->format('d M Y') }} · {{ substr((string) $appointment->start_time, 0, 5) }}–{{ substr((string) $appointment->end_time, 0, 5) }}</p>
            <p class="mt-4 text-sm text-ink-600">Reason</p>
            <p class="font-medium text-navy-900">{{ $appointment->reason ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Current status</p>
            <div class="mt-2"><x-admin.status-pill :value="$appointment->status" /></div>
        </article>

        @can('appointments.manage')
            <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
                @csrf
                @method('PUT')
                <h2 class="font-display text-xl text-navy-900">Update status</h2>
                <div class="mt-4">
                    <label for="status" class="text-sm font-medium text-navy-900">Status</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
                        @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $appointment->status) === $status)>{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <x-form.textarea name="notes" label="Internal notes" :value="$appointment->notes" />
                <x-form.input name="cancellation_reason" label="Cancellation reason" :value="$appointment->cancellation_reason" />
                <button type="submit" class="mt-4 rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save status</button>
            </form>
        @endcan
    </div>

    @can('billing.manage')
        <p class="mt-6">
            <a href="{{ route('admin.bills.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Generate bill for this visit</a>
        </p>
    @endcan
@endsection
