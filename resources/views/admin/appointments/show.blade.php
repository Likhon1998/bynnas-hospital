@extends('layouts.admin')
@section('title', $appointment->appointment_no)
@section('heading', $appointment->appointment_no)
@section('content')
    <div class="form-workspace">
        <p class="form-lede">Review the visit first, then update the status so reception and billing stay aligned.</p>

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
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Doctor</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->doctor?->name }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->department?->name ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Schedule</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->appointment_date?->format('d M Y') }}</p>
                    <p class="text-sm text-ink-600">{{ substr((string) $appointment->start_time, 0, 5) }}–{{ substr((string) $appointment->end_time, 0, 5) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Status</p>
                    <div class="mt-2"><x-admin.status-pill :value="$appointment->status" /></div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">From</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->source === 'web' ? 'Website' : 'Desk' }}</p>
                </div>
                <div class="form-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Reason</p>
                    <p class="mt-1 text-navy-900">{{ $appointment->reason ?: '—' }}</p>
                </div>
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
                submit="Save status"
            >
                <x-admin.form-section title="Update this visit" note="Change the status after confirmation, completion, or a missed visit.">
                    <x-form.select name="status" label="Status" required>
                        @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No show'] as $status => $label)
                            <option value="{{ $status }}" @selected(old('status', $appointment->status) === $status)>{{ $label }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.input name="cancellation_reason" label="Cancellation reason" :value="$appointment->cancellation_reason" />
                    <div class="form-span-2">
                        <x-form.textarea name="notes" label="Internal notes" :value="$appointment->notes" />
                    </div>
                </x-admin.form-section>
            </x-admin.form-page>
        </div>
    @endcan
@endsection
