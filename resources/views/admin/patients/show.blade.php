@extends('layouts.admin')
@section('title', $patient->full_name)
@section('heading', $patient->full_name)
@section('content')
    <div class="grid gap-6 xl:grid-cols-[20rem_1fr]">
        <aside class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <p class="text-sm text-ink-600">Phone</p>
            <p class="font-medium text-navy-900">{{ $patient->phone }}</p>
            <p class="mt-4 text-sm text-ink-600">Email</p>
            <p class="font-medium text-navy-900">{{ $patient->email ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Gender</p>
            <p class="font-medium text-navy-900">{{ $patient->gender ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Address</p>
            <p class="font-medium text-navy-900">{{ $patient->address ?: '—' }}</p>
            @can('patients.manage')
                <a href="{{ route('admin.patients.edit', $patient) }}" class="mt-6 inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Edit patient</a>
            @endcan
        </aside>
        <section class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <h2 class="font-display text-xl text-navy-900">Appointments</h2>
            <ul class="mt-4 divide-y divide-sand-100">
                @forelse ($patient->appointments as $appointment)
                    <li class="flex items-center justify-between gap-3 py-3 text-sm">
                        <span>{{ $appointment->appointment_no }} · {{ $appointment->doctor?->name }}</span>
                        @can('appointments.view')
                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="font-semibold text-teal-700">Open</a>
                        @else
                            <span class="text-ink-400">{{ $appointment->status }}</span>
                        @endcan
                    </li>
                @empty
                    <li class="py-6 text-ink-600">No appointments for this patient.</li>
                @endforelse
            </ul>
        </section>
    </div>

    @can('billing.manage')
        <section class="mt-6 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl text-navy-900">Bills</h2>
                <a href="{{ route('admin.bills.create', ['patient_id' => $patient->id]) }}" class="text-sm font-semibold text-teal-700">Generate bill</a>
            </div>
            <ul class="mt-4 divide-y divide-sand-100">
                @forelse ($patient->bills as $bill)
                    <li class="flex items-center justify-between gap-3 py-3 text-sm">
                        <span>{{ $bill->bill_no }} · {{ number_format((float) $bill->total, 2) }} · {{ str_replace('_', ' ', $bill->status) }}</span>
                        <a href="{{ route('admin.bills.show', $bill) }}" class="font-semibold text-teal-700">Open</a>
                    </li>
                @empty
                    <li class="py-6 text-ink-600">No bills for this patient.</li>
                @endforelse
            </ul>
        </section>
    @endcan
@endsection
