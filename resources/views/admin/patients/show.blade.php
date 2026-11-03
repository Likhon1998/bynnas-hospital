@extends('layouts.admin')
@section('title', $patient->full_name)
@section('heading', $patient->full_name)
@section('content')
    <div class="grid gap-6 xl:grid-cols-[20rem_1fr]">
        <aside class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <p class="text-sm text-ink-600">Patient ID</p>
            <p class="font-medium text-navy-900">{{ $patient->patient_code ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Age</p>
            <p class="font-medium text-navy-900">{{ $patient->ageLabel() }}</p>
            <p class="mt-4 text-sm text-ink-600">Phone</p>
            <p class="font-medium text-navy-900">{{ $patient->phone }}</p>
            <p class="mt-4 text-sm text-ink-600">Email</p>
            <p class="font-medium text-navy-900">{{ $patient->email ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Gender</p>
            <p class="font-medium text-navy-900">{{ $patient->gender ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Address</p>
            <p class="font-medium text-navy-900">{{ $patient->address ?: '—' }}</p>
            <p class="mt-4 text-sm text-ink-600">Care alerts</p>
            @if ($patient->careFlagLabels() !== [])
                <ul class="mt-1 flex flex-wrap gap-1.5">
                    @foreach ($patient->careFlagLabels() as $label)
                        <li class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-800 ring-1 ring-teal-100">{{ $label }}</li>
                    @endforeach
                </ul>
            @else
                <p class="font-medium text-navy-900">—</p>
            @endif
            <p class="mt-4 text-sm text-ink-600">Allergies</p>
            <p class="font-medium text-navy-900">{{ $patient->allergyLabel() }}</p>
            @can('admissions.view')
                @if ($patient->currentAdmission)
                    <a href="{{ route('admin.admissions.show', $patient->currentAdmission) }}" class="mt-6 inline-flex rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Open admission</a>
                @elseif ($patient->bed?->status !== 'occupied')
                    @can('admissions.admit')
                        <a href="{{ route('admin.admissions.create', ['patient_id' => $patient->id]) }}" class="mt-6 inline-flex rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Admit to bed</a>
                    @endcan
                @endif
            @endcan
            @can('nursing.view')
                @if ($patient->bed?->status === 'occupied')
                    <a href="{{ route('admin.nursing.patients.show', $patient) }}" class="mt-6 inline-flex rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Open nursing chart</a>
                @endif
            @endcan
            <a href="{{ route('admin.patients.print', $patient) }}" class="mt-6 inline-flex rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white" target="_blank">Print patient slip</a>
            @can('patients.manage')
                <a href="{{ route('admin.patients.edit', $patient) }}" class="mt-3 inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Edit patient</a>
            @endcan
        </aside>
        <div>
        <section class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl text-navy-900">Encounter history</h2>
                @can('visits.checkin')
                    <a href="{{ route('admin.visits.create', ['patient_id' => $patient->id]) }}" class="text-sm font-semibold text-teal-700">Check in</a>
                @endcan
            </div>
            <ul class="mt-4 divide-y divide-sand-100">
                @forelse ($patient->visits as $visit)
                    <li class="py-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium text-navy-900">{{ $visit->visit_no }} · {{ $visit->statusLabel() }}</span>
                            @can('visits.view')
                                <a href="{{ route('admin.visits.show', $visit) }}" class="font-semibold text-teal-700">Open</a>
                            @endcan
                        </div>
                        <p class="mt-1 text-ink-600">{{ $visit->arrived_at?->format('d M Y H:i') }} · {{ $visit->doctor?->name ?: 'Unassigned' }}</p>
                        @if ($visit->events->isNotEmpty())
                            <p class="mt-1 text-xs text-ink-500">{{ $visit->events->map->label()->implode(' → ') }}</p>
                        @endif
                    </li>
                @empty
                    <li class="py-6 text-ink-600">No clinical encounters yet.</li>
                @endforelse
            </ul>
        </section>

        @can('lab.view')
            <section class="mt-6 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-xl text-navy-900">Laboratory</h2>
                    <a href="{{ route('admin.labs.index') }}" class="text-sm font-semibold text-teal-700">Lab desk</a>
                </div>
                <ul class="mt-4 divide-y divide-sand-100">
                    @forelse ($patient->labOrders as $order)
                        <li class="py-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-navy-900">{{ $order->test?->name }} · {{ $order->order_no }}</span>
                                <a href="{{ route('admin.labs.show', $order) }}" class="font-semibold text-teal-700">Open</a>
                            </div>
                            <p class="mt-1 text-ink-600">
                                {{ $order->visit?->visit_no }} · {{ $order->statusLabel() }}
                                @if ($order->result)
                                    · {{ $order->result->displayValue() }}
                                @endif
                            </p>
                        </li>
                    @empty
                        <li class="py-6 text-ink-600">No laboratory orders yet.</li>
                    @endforelse
                </ul>
            </section>
        @endcan

        <section class="mt-6 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl text-navy-900">Appointments</h2>
                @can('appointments.manage')
                    <a href="{{ route('admin.appointments.create', ['patient_id' => $patient->id]) }}" class="text-sm font-semibold text-teal-700">Take appointment</a>
                @endcan
            </div>
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
