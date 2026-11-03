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

        @if ($appointment->serial_no !== null)
            <section class="form-card appointment-serial-card">
                <header class="form-card__head">
                    <h2>Patient serial</h2>
                    <p>Share this number with the patient so they know their place in today’s queue.</p>
                </header>
                <div class="form-card__body">
                    <div class="appointment-serial-card__row">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Serial</p>
                            <p class="appointment-serial-card__value">{{ $appointment->serialLabel() }}</p>
                            <p class="mt-1 text-sm text-ink-600">
                                {{ $appointment->doctor?->name ?: 'Doctor' }}
                                · {{ $appointment->appointment_date?->format('d M Y') }}
                                · {{ $appointment->timeRangeLabel() }}
                            </p>
                        </div>
                        <div class="appointment-serial-card__actions">
                            <a href="{{ route('admin.appointments.print', $appointment) }}" class="form-btn form-btn--ghost" target="_blank" rel="noopener">Print OPD ticket</a>
                            @if ($whatsappSerialUrl)
                                <a
                                    href="{{ $whatsappSerialUrl }}"
                                    class="form-btn form-btn--whatsapp"
                                    target="_blank"
                                    rel="noopener"
                                    data-whatsapp-serial
                                >Send serial on WhatsApp</a>
                            @elseif ($appointment->patient)
                                @can('patients.manage')
                                    <a href="{{ route('admin.patients.edit', $appointment->patient) }}" class="form-btn form-btn--navy">Add patient phone</a>
                                @endcan
                            @endif
                        </div>
                    </div>

                    @if ($whatsappSerialMessage)
                        <details class="appointment-serial-card__preview">
                            <summary>Preview WhatsApp message</summary>
                            <pre>{{ $whatsappSerialMessage }}</pre>
                        </details>
                    @endif
                </div>
            </section>
        @endif

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
                    <div class="form-span-2 flex flex-wrap gap-2">
                        @if ($linkedBill)
                            <a href="{{ route('admin.bills.show', $linkedBill) }}" class="form-btn form-btn--primary">
                                Open consultation bill {{ $linkedBill->bill_no }}
                            </a>
                        @else
                            <a href="{{ route('admin.bills.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" class="form-btn form-btn--navy">Generate bill for this visit</a>
                        @endif
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

    @if (session('open_whatsapp') && session('whatsapp_serial_url'))
        <script>
            (() => {
                const url = @json(session('whatsapp_serial_url'));
                if (!url) return;
                window.setTimeout(() => window.open(url, '_blank', 'noopener'), 350);
            })();
        </script>
    @endif
@endsection
