@extends('layouts.admin')
@section('title', $visit->visit_no)
@section('heading', $visit->visit_no)
@section('content')
    <div class="form-workspace">
        <p class="form-lede">
            Check-in, triage, consultation, then complete. Every step stays on this encounter and on the patient history.
        </p>

        <section class="form-card">
            <header class="form-card__head">
                <h2>Encounter</h2>
                <p>{{ $visit->statusLabel() }} · arrived {{ $visit->arrived_at?->format('d M Y H:i') ?: '—' }}</p>
            </header>
            <div class="form-card__body form-grid">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $visit->patient?->full_name }}</p>
                    <p class="text-sm text-ink-600">{{ $visit->patient?->phone }}</p>
                    @can('patients.view')
                        @if ($visit->patient)
                            <a href="{{ route('admin.patients.show', $visit->patient) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700">Patient history</a>
                        @endif
                    @endcan
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Doctor</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $visit->doctor?->name ?: 'Unassigned' }}</p>
                    <p class="text-sm text-ink-600">{{ $visit->department?->name ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Stage</p>
                    <div class="mt-2"><x-admin.status-pill :value="$visit->status" /></div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Complaint</p>
                    <p class="mt-1 text-navy-900">{{ $visit->chief_complaint ?: '—' }}</p>
                </div>
                @if ($visit->appointment)
                    <div class="form-span-2">
                        <a href="{{ route('admin.appointments.show', $visit->appointment) }}" class="text-sm font-semibold text-teal-700">Linked appointment {{ $visit->appointment->appointment_no }}</a>
                    </div>
                @endif
                @can('lab.order')
                    <div class="form-span-2">
                        <a href="{{ route('admin.labs.create', ['visit_id' => $visit->id]) }}" class="text-sm font-semibold text-teal-700">Order lab test</a>
                    </div>
                @endcan
                @can('admissions.admit')
                    <div class="form-span-2">
                        <a href="{{ route('admin.admissions.create', ['source' => 'emergency', 'visit_id' => $visit->id, 'patient_id' => $visit->patient_id]) }}" class="text-sm font-semibold text-teal-700">Admit to bed</a>
                    </div>
                @endcan
            </div>
        </section>
    </div>

    <div class="mc-layout">
        <div class="mc-main">
            @can('visits.triage')
                @if ($visit->isOpen())
                    <x-admin.form-page
                        :action="route('admin.visits.vitals', $visit)"
                        method="POST"
                        :cancel="route('admin.visits.index')"
                        submit="Save vitals"
                    >
                        <x-admin.form-section title="Nurse triage" note="Blood pressure, temperature, pulse, oxygen, weight, and pain score.">
                            <x-form.input name="blood_pressure" label="Blood pressure" :value="old('blood_pressure', $visit->blood_pressure)" placeholder="120/80" required />
                            <x-form.input name="temperature" label="Temperature (°C)" type="number" :value="old('temperature', $visit->temperature)" step="0.1" required />
                            <x-form.input name="pulse" label="Pulse (bpm)" type="number" :value="old('pulse', $visit->pulse)" required />
                            <x-form.input name="oxygen_saturation" label="Oxygen saturation (%)" type="number" :value="old('oxygen_saturation', $visit->oxygen_saturation)" required />
                            <x-form.input name="weight" label="Weight (kg)" type="number" :value="old('weight', $visit->weight)" step="0.1" required />
                            <x-form.input name="pain_score" label="Pain score (0–10)" type="number" :value="old('pain_score', $visit->pain_score)" required />
                        </x-admin.form-section>
                    </x-admin.form-page>
                @endif
            @endcan

            @if ($visit->hasVitals())
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Recorded vitals</h2>
                        <p>{{ $visit->vitals_recorded_at?->format('d M Y H:i') }} · {{ $visit->vitalsRecordedBy?->name ?: 'Nurse' }}</p>
                    </header>
                    <div class="form-card__body form-grid">
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">BP</p><p class="mt-1 font-semibold text-navy-900">{{ $visit->blood_pressure }}</p></div>
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Temp</p><p class="mt-1 font-semibold text-navy-900">{{ $visit->temperature }} °C</p></div>
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Pulse</p><p class="mt-1 font-semibold text-navy-900">{{ $visit->pulse }}</p></div>
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">SpO2</p><p class="mt-1 font-semibold text-navy-900">{{ $visit->oxygen_saturation }}%</p></div>
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Weight</p><p class="mt-1 font-semibold text-navy-900">{{ $visit->weight }} kg</p></div>
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Pain</p><p class="mt-1 font-semibold text-navy-900">{{ $visit->pain_score }}/10</p></div>
                    </div>
                </section>
            @endif

            @can('visits.clinical')
                @if ($visit->isOpen())
                    <div class="mt-4">
                        <form method="POST" action="{{ route('admin.visits.clinical', $visit) }}" class="form-workspace">
                            @csrf
                            <x-admin.form-section title="Doctor notes" note="Symptoms, exam, diagnosis, treatment, prescriptions, and follow-up.">
                                <div class="form-span-2">
                                    <x-form.textarea name="symptoms" label="Symptoms" :value="old('symptoms', $visit->symptoms)" />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="examination_notes" label="Examination notes" :value="old('examination_notes', $visit->examination_notes)" />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="diagnosis" label="Diagnosis" :value="old('diagnosis', $visit->diagnosis)" />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="treatment_plan" label="Treatment plan" :value="old('treatment_plan', $visit->treatment_plan)" />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="prescriptions" label="Prescriptions" :value="old('prescriptions', $visit->prescriptions)" />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="follow_up_instructions" label="Follow-up instructions" :value="old('follow_up_instructions', $visit->follow_up_instructions)" />
                                </div>
                            </x-admin.form-section>
                            <div class="form-bar">
                                <a href="{{ route('admin.visits.index') }}" class="form-btn form-btn--ghost">Back to desk</a>
                                <button type="submit" name="complete" value="0" class="form-btn form-btn--ghost">Save notes</button>
                                <button type="submit" name="complete" value="1" class="form-btn form-btn--primary">Complete encounter</button>
                            </div>
                        </form>
                    </div>
                @endif
            @endcan

            @can('lab.order')
                @if ($visit->status !== 'cancelled' && $labTests->isNotEmpty())
                    <div class="mt-4">
                        <x-admin.form-page
                            :action="route('admin.labs.store')"
                            method="POST"
                            :cancel="route('admin.visits.index')"
                            submit="Order lab test"
                        >
                            <input type="hidden" name="visit_id" value="{{ $visit->id }}">
                            <x-admin.form-section title="Laboratory" note="The test is charged once on the patient bill.">
                                <x-form.select name="lab_test_id" label="Test" required>
                                    <option value="">Select test</option>
                                    @foreach ($labTests as $test)
                                        <option value="{{ $test->id }}" @selected((string) old('lab_test_id') === (string) $test->id)>
                                            {{ $test->name }} · {{ number_format((float) $test->price, 2) }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                                <x-form.input name="clinical_notes" label="Clinical notes" :value="old('clinical_notes', $visit->chief_complaint)" />
                            </x-admin.form-section>
                        </x-admin.form-page>
                    </div>
                @endif
            @endcan

            @if ($visit->labOrders->isNotEmpty())
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Laboratory results</h2>
                        <p>Orders and verified results for this encounter</p>
                    </header>
                    <div class="form-card__body">
                        <ul class="divide-y divide-sand-100">
                            @foreach ($visit->labOrders as $order)
                                <li class="py-3 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-semibold text-navy-900">{{ $order->test?->name }} · {{ $order->order_no }}</p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-admin.status-pill :value="$order->status" />
                                            @if ($order->is_critical)
                                                <x-admin.status-pill value="critical" />
                                            @endif
                                            @can('lab.view')
                                                <a href="{{ route('admin.labs.show', $order) }}" class="font-semibold text-teal-700">Open</a>
                                            @endcan
                                        </div>
                                    </div>
                                    @if ($order->result)
                                        <p class="mt-1 text-navy-900">{{ $order->result->displayValue() }} · range {{ $order->result->reference_range ?: '—' }}</p>
                                        <p class="text-ink-600">Verified {{ $order->result->verified_at?->format('d M Y H:i') }}</p>
                                    @else
                                        <p class="mt-1 text-ink-600">{{ $order->clinical_notes ?: 'Awaiting result' }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if ($visit->status === 'completed')
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Completed encounter</h2>
                        <p>{{ $visit->completed_at?->format('d M Y H:i') }} · {{ $visit->clinicallyRecordedBy?->name ?: 'Doctor' }}</p>
                    </header>
                    <div class="form-card__body form-grid">
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Symptoms</p><p class="mt-1 text-navy-900">{{ $visit->symptoms ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Examination</p><p class="mt-1 text-navy-900">{{ $visit->examination_notes ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Diagnosis</p><p class="mt-1 text-navy-900">{{ $visit->diagnosis ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Treatment</p><p class="mt-1 text-navy-900">{{ $visit->treatment_plan ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Prescriptions</p><p class="mt-1 text-navy-900">{{ $visit->prescriptions ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Follow-up</p><p class="mt-1 text-navy-900">{{ $visit->follow_up_instructions ?: '—' }}</p></div>
                    </div>
                </section>
            @endif
        </div>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Encounter history</h2>
                        <p>Every step on this visit</p>
                    </div>
                </div>
                <ul class="mc-stat-list">
                    @forelse ($visit->events as $event)
                        <li>
                            <div>
                                <span class="mc-stat-list__dot mc-stat-list__dot--blue"></span>
                                <span>{{ $event->label() }}{{ $event->notes ? ' · '.$event->notes : '' }}</span>
                            </div>
                            <em>{{ $event->created_at?->format('H:i') }}</em>
                        </li>
                    @empty
                        <li><span>No steps recorded yet.</span></li>
                    @endforelse
                </ul>
            </section>
        </aside>
    </div>
@endsection
