@extends('layouts.admin')
@section('title', $appointment->appointment_no)
@section('heading', $appointment->appointment_no)
@section('content')
    <div class="form-workspace">
        <p class="form-lede">
            @if ($appointment->source === 'web' && $appointment->status === 'pending')
                Website request — this time is already held for the patient. Confirm it, move it, or cancel if they do not need the slot.
            @else
                Review the visit, set the time if needed, then update the status so reception and billing stay aligned.
            @endif
        </p>

        <section class="form-card">
            <header class="form-card__head">
                <h2>Visit</h2>
                <p>Who is coming, who will see them, and when.</p>
            </header>
            <div class="form-card__body form-grid">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->patient?->full_name }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->patient?->phone }}</p>
                    @can('patients.view')
                        @if ($appointment->patient)
                            <a href="{{ route('admin.patients.show', $appointment->patient) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700">Open record</a>
                        @endif
                    @endcan
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Doctor</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->doctor?->name ?: '—' }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->department?->name ?: '—' }}</p>
                    @if ($appointment->doctor)
                        <a href="{{ route('admin.appointments.index', ['doctor' => $appointment->doctor_id]) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700">All visits</a>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Schedule</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->appointment_date?->format('d M Y') }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->timeRangeLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Status</p>
                    <div class="mt-2"><x-admin.status-pill :value="$appointment->status" /></div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Serial</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->serialLabel() }}</p>
                    <a href="{{ route('admin.appointments.print', $appointment) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700" target="_blank">Print OPD ticket</a>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">From</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->sourceLabel() }}</p>
                </div>
                <div class="form-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Reason</p>
                    <p class="mt-1 text-navy-900">{{ $appointment->reason ?: '—' }}</p>
                </div>
                @php
                    $linkedVisit = $appointment->visits->first(fn ($visit) => $visit->status !== 'cancelled');
                @endphp
                @can('visits.checkin')
                    @if (! $linkedVisit && ! in_array($appointment->status, ['cancelled', 'no_show'], true))
                        <div class="form-span-2">
                            <form method="POST" action="{{ route('admin.appointments.check-in', $appointment) }}">
                                @csrf
                                <button type="submit" class="form-btn form-btn--primary">Check in patient</button>
                            </form>
                        </div>
                    @endif
                @endcan
                @if ($linkedVisit)
                    <div class="form-span-2">
                        <a href="{{ route('admin.visits.show', $linkedVisit) }}" class="form-btn form-btn--navy">Open encounter {{ $linkedVisit->visit_no }}</a>
                    </div>
                @endif
                @can('admissions.admit')
                    @if ($appointment->patient && ! in_array($appointment->status, ['cancelled', 'no_show'], true))
                        <div class="form-span-2">
                            <a href="{{ route('admin.admissions.create', ['source' => $appointment->source === 'emergency' ? 'emergency' : 'appointment', 'appointment_id' => $appointment->id]) }}" class="form-btn form-btn--navy">Admit to bed</a>
                        </div>
                    @endif
                @endcan
                @can('billing.manage')
                    <div class="form-span-2">
                        <a href="{{ route('admin.bills.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" class="form-btn form-btn--navy">Generate bill for this visit</a>
                    </div>
                @endcan
            </div>
        </section>
    </div>

    @can('appointments.manage')
        <div class="mt-4">
            <x-admin.form-page
                :action="route('admin.appointments.update', $appointment)"
                method="PUT"
                :cancel="route('admin.appointments.index')"
                submit="Save visit"
            >
                <x-admin.form-section title="Update this visit" note="Set the confirmed time, then change the status after the patient arrives or misses the slot.">
                    <x-form.input name="appointment_date" label="Date" type="date" :value="old('appointment_date', $appointment->appointment_date?->toDateString())" required />
                    <x-form.input name="start_time" label="Start time" type="time" :value="old('start_time', $appointment->timeInputValue())" required />
                    <x-form.select name="status" label="Status" required>
                        @foreach (\App\Models\Appointment::STATUSES as $status => $label)
                            <option value="{{ $status }}" @selected(old('status', $appointment->status) === $status)>{{ $label }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.input name="reason" label="Reason" :value="old('reason', $appointment->reason)" />
                    <x-form.input name="cancellation_reason" label="Cancellation reason" :value="old('cancellation_reason', $appointment->cancellation_reason)" />
                    <div class="form-span-2">
                        <x-form.textarea name="notes" label="Internal notes" :value="old('notes', $appointment->notes)" />
                    </div>
                </x-admin.form-section>
            </x-admin.form-page>
        </div>
    @endcan
@endsection
