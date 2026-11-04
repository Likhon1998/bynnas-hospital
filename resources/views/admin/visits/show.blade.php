@extends('layouts.admin')
@section('title', $visit->visit_no)
@section('heading', $visit->visit_no)
@section('content')
    @php
        $actorDoctorId = auth()->user()?->doctor?->id;
        $canEditClinical = ! auth()->user()?->hasRole('doctor')
            || $actorDoctorId === null
            || $visit->doctor_id === null
            || (int) $visit->doctor_id === (int) $actorDoctorId;
    @endphp

    <div class="visit-desk">
        <section class="visit-desk__header">
            <div class="visit-desk__identity">
                <div>
                    <p class="visit-desk__label">Patient</p>
                    <p class="visit-desk__value">{{ $visit->patient?->full_name }}</p>
                    <p class="visit-desk__sub">{{ $visit->patient?->phone ?: '—' }}</p>
                </div>
                <div>
                    <p class="visit-desk__label">Doctor</p>
                    <p class="visit-desk__value">{{ $visit->doctor?->name ?: 'Unassigned' }}</p>
                    <p class="visit-desk__sub">{{ $visit->department?->name ?: '—' }}</p>
                </div>
                <div>
                    <p class="visit-desk__label">Stage</p>
                    <div class="visit-desk__pill"><x-admin.status-pill :value="$visit->status" /></div>
                    <p class="visit-desk__sub">Arrived {{ $visit->arrived_at?->format('d M Y H:i') ?: '—' }}</p>
                </div>
                <div class="visit-desk__complaint">
                    <p class="visit-desk__label">Complaint</p>
                    <p class="visit-desk__value">{{ $visit->chief_complaint ?: '—' }}</p>
                </div>
            </div>

            <x-admin.row-menu label="Actions" class="visit-desk__actions">
                @can('patients.view')
                    @if ($visit->patient)
                        <a href="{{ route('admin.patients.show', $visit->patient) }}">Patient history</a>
                    @endif
                @endcan
                @if ($visit->appointment)
                    <a href="{{ route('admin.appointments.show', $visit->appointment) }}">Linked appointment</a>
                @endif
                @can('lab.order')
                    @if ($visit->status !== 'cancelled')
                        <a href="{{ route('admin.labs.create', ['visit_id' => $visit->id]) }}">Order lab test</a>
                    @endif
                @endcan
                @can('admissions.admit')
                    <a href="{{ route('admin.admissions.create', ['source' => 'emergency', 'visit_id' => $visit->id, 'patient_id' => $visit->patient_id]) }}">Admit to bed</a>
                @endcan
                @can('appointments.manage')
                    @if ($visit->patient_id && filled($visit->follow_up_instructions))
                        <a href="{{ route('admin.appointments.create', ['patient_id' => $visit->patient_id]) }}">Schedule follow-up</a>
                    @endif
                @endcan
                @if ($visit->status === 'completed' || filled($visit->diagnosis) || filled($visit->prescriptions))
                    <button type="button" data-rx-preview-open>Preview prescription</button>
                    <a href="{{ route('admin.visits.prescription', ['visit' => $visit, 'print' => 1]) }}" target="_blank" rel="noopener">Print prescription</a>
                @endif
                <a href="{{ route('admin.visits.index') }}">Back to check-in</a>
            </x-admin.row-menu>
        </section>

        <div class="visit-desk__grid">
            <div class="visit-desk__main">
                @can('visits.triage')
                    @if ($visit->isOpen())
                        <section class="visit-desk__panel">
                            <header class="visit-desk__panel-head">
                                <h2>Nurse triage</h2>
                                <p>Record vitals for this encounter</p>
                            </header>
                            <form method="POST" action="{{ route('admin.visits.vitals', $visit) }}" class="visit-desk__form">
                                @csrf
                                <div class="visit-desk__fields">
                                    <x-form.input name="blood_pressure" label="BP" :value="old('blood_pressure', $visit->blood_pressure)" placeholder="120/80" required />
                                    <x-form.input name="temperature" label="Temp °C" type="number" :value="old('temperature', $visit->temperature)" step="0.1" required />
                                    <x-form.input name="pulse" label="Pulse" type="number" :value="old('pulse', $visit->pulse)" required />
                                    <x-form.input name="oxygen_saturation" label="SpO₂ %" type="number" :value="old('oxygen_saturation', $visit->oxygen_saturation)" required />
                                    <x-form.input name="weight" label="Weight kg" type="number" :value="old('weight', $visit->weight)" step="0.1" required />
                                    <x-form.input name="pain_score" label="Pain 0–10" type="number" :value="old('pain_score', $visit->pain_score)" required />
                                </div>
                                <div class="visit-desk__form-bar">
                                    <button type="submit" class="bill-desk__btn bill-desk__btn--primary">Save vitals</button>
                                </div>
                            </form>
                        </section>
                    @endif
                @endcan

                @if ($visit->hasVitals())
                    <section class="visit-desk__panel">
                        <header class="visit-desk__panel-head">
                            <h2>Recorded vitals</h2>
                            <p>{{ $visit->vitals_recorded_at?->format('d M Y H:i') }} · {{ $visit->vitalsRecordedBy?->name ?: 'Nurse' }}</p>
                        </header>
                        <div class="table-frame">
                            <table class="pro-table visit-desk__table">
                                <thead>
                                    <tr>
                                        <th>BP</th>
                                        <th>Temp</th>
                                        <th>Pulse</th>
                                        <th>SpO₂</th>
                                        <th>Weight</th>
                                        <th>Pain</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $visit->blood_pressure }}</td>
                                        <td>{{ $visit->temperature }} °C</td>
                                        <td>{{ $visit->pulse }}</td>
                                        <td>{{ $visit->oxygen_saturation }}%</td>
                                        <td>{{ $visit->weight }} kg</td>
                                        <td>{{ $visit->pain_score }}/10</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @can('visits.clinical')
                    @if ($visit->isOpen() && $canEditClinical)
                        <section class="visit-desk__panel">
                            <header class="visit-desk__panel-head">
                                <h2>Write prescription</h2>
                                <p>Do: pick findings → add medicines → complete · Don’t: invent doses in free text</p>
                            </header>
                            <form method="POST" action="{{ route('admin.visits.clinical', $visit) }}" class="visit-desk__form">
                                @csrf

                                @unless ($visit->hasVitals())
                                    <div class="visit-desk__banner" role="status">
                                        <strong>Waiting for vitals.</strong>
                                        A nurse must save vitals first. You can draft notes now, but Complete stays locked until triage is done.
                                    </div>
                                @endunless

                                @error('diagnosis')
                                    <p class="visit-desk__banner visit-desk__banner--error">{{ $message }}</p>
                                @enderror

                                <div class="rx-desk">
                                    <section class="rx-desk__step">
                                        <header class="rx-desk__step-head">
                                            <span>1</span>
                                            <div>
                                                <h3>Clinical findings</h3>
                                                <p>Required before complete: symptoms, diagnosis, advice</p>
                                            </div>
                                        </header>
                                        <div class="rx-desk__findings">
                                            <x-admin.clinical-pick
                                                name="symptoms"
                                                label="Symptoms"
                                                :options="\App\Support\ClinicalNoteOptions::symptoms()"
                                                :value="old('symptoms', $visit->symptoms)"
                                                required
                                            />
                                            <x-admin.clinical-pick
                                                name="examination_notes"
                                                label="Examination"
                                                :options="\App\Support\ClinicalNoteOptions::examinationNotes()"
                                                :value="old('examination_notes', $visit->examination_notes)"
                                            />
                                            <x-admin.clinical-pick
                                                name="diagnosis"
                                                label="Diagnosis"
                                                :options="\App\Support\ClinicalNoteOptions::diagnoses()"
                                                :value="old('diagnosis', $visit->diagnosis)"
                                                required
                                            />
                                        </div>
                                    </section>

                                    <section class="rx-desk__step">
                                        <header class="rx-desk__step-head">
                                            <span>2</span>
                                            <div>
                                                <h3>Advice &amp; follow-up</h3>
                                                <p>Rest advice and when to return</p>
                                            </div>
                                        </header>
                                        <div class="rx-desk__advice">
                                            <x-admin.clinical-pick
                                                name="treatment_plan"
                                                label="Treatment / rest advice"
                                                :options="\App\Support\ClinicalNoteOptions::treatmentPlans()"
                                                :value="old('treatment_plan', $visit->treatment_plan)"
                                                required
                                            />
                                            <x-admin.clinical-pick
                                                name="follow_up_instructions"
                                                label="Next check"
                                                :options="\App\Support\ClinicalNoteOptions::followUps()"
                                                :value="old('follow_up_instructions', $visit->follow_up_instructions)"
                                            />
                                        </div>
                                    </section>

                                    <section class="rx-desk__step">
                                        <header class="rx-desk__step-head">
                                            <span>3</span>
                                            <div>
                                                <h3>Prescribe</h3>
                                                <p>Build each line: medicine + dose + frequency + duration</p>
                                            </div>
                                        </header>
                                        <x-admin.rx-builder :value="old('prescriptions', $visit->prescriptions)" />
                                    </section>
                                </div>

                                <div class="visit-desk__form-bar">
                                    <button type="submit" name="complete" value="0" class="bill-desk__btn bill-desk__btn--ghost">Save draft</button>
                                    <button
                                        type="submit"
                                        name="complete"
                                        value="1"
                                        class="bill-desk__btn bill-desk__btn--primary"
                                        @disabled(! $visit->hasVitals())
                                        title="{{ $visit->hasVitals() ? 'Finish encounter and print prescription' : 'Nurse must record vitals first' }}"
                                    >Complete encounter</button>
                                </div>
                            </form>
                        </section>
                    @elseif ($visit->isOpen() && ! $canEditClinical)
                        <p class="visit-desk__notice">This encounter is assigned to another doctor. You can view notes but cannot edit their prescription.</p>
                    @endif
                @endcan

                @if ($visit->status === 'completed')
                    <section class="visit-desk__panel">
                        <header class="visit-desk__panel-head">
                            <h2>Completed encounter</h2>
                            <p>{{ $visit->completed_at?->format('d M Y H:i') }} · {{ $visit->clinicallyRecordedBy?->name ?: 'Doctor' }}</p>
                        </header>
                        <div class="table-frame">
                            <table class="pro-table visit-desk__table visit-desk__table--notes">
                                <tbody>
                                    <tr><th scope="row">Symptoms</th><td>{{ $visit->symptoms ?: '—' }}</td></tr>
                                    <tr><th scope="row">Examination</th><td>{{ $visit->examination_notes ?: '—' }}</td></tr>
                                    <tr><th scope="row">Diagnosis</th><td>{{ $visit->diagnosis ?: '—' }}</td></tr>
                                    <tr><th scope="row">Treatment</th><td>{{ $visit->treatment_plan ?: '—' }}</td></tr>
                                    <tr><th scope="row">Prescriptions</th><td>{{ $visit->prescriptions ?: '—' }}</td></tr>
                                    <tr><th scope="row">Follow-up</th><td>{{ $visit->follow_up_instructions ?: '—' }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="visit-desk__form-bar" style="padding:0 0.75rem 0.75rem">
                            <button type="button" class="bill-desk__btn bill-desk__btn--ghost" data-rx-preview-open>Preview prescription</button>
                            <a href="{{ route('admin.visits.prescription', ['visit' => $visit, 'print' => 1]) }}" target="_blank" rel="noopener" class="bill-desk__btn bill-desk__btn--primary">Print prescription</a>
                        </div>
                    </section>
                @endif

                @can('lab.order')
                    @if ($visit->status !== 'cancelled' && $labTests->isNotEmpty())
                        <section class="visit-desk__panel">
                            <header class="visit-desk__panel-head">
                                <h2>Order laboratory test</h2>
                                <p>Charged once on the patient bill</p>
                            </header>
                            <form method="POST" action="{{ route('admin.labs.store') }}" class="visit-desk__form">
                                @csrf
                                <input type="hidden" name="visit_id" value="{{ $visit->id }}">
                                <div class="visit-desk__lab-fields">
                                    <x-form.select name="lab_test_id" label="Test" required>
                                        <option value="">Select test</option>
                                        @foreach ($labTests as $test)
                                            <option value="{{ $test->id }}" @selected((string) old('lab_test_id') === (string) $test->id)>
                                                {{ $test->name }} · {{ number_format((float) $test->price, 2) }}
                                            </option>
                                        @endforeach
                                    </x-form.select>
                                    <x-form.input name="clinical_notes" label="Clinical notes" :value="old('clinical_notes', $visit->chief_complaint)" />
                                </div>
                                <div class="visit-desk__form-bar">
                                    <button type="submit" class="bill-desk__btn bill-desk__btn--primary">Order lab test</button>
                                </div>
                            </form>
                        </section>
                    @endif
                @endcan

                @if ($visit->labOrders->isNotEmpty())
                    <section class="visit-desk__panel">
                        <header class="visit-desk__panel-head">
                            <h2>Laboratory results</h2>
                            <p>{{ $visit->labOrders->count() }} {{ \Illuminate\Support\Str::plural('order', $visit->labOrders->count()) }}</p>
                        </header>
                        <div class="table-frame">
                            <table class="pro-table visit-desk__table">
                                <thead>
                                    <tr>
                                        <th>Test</th>
                                        <th>Order</th>
                                        <th class="col-status">Status</th>
                                        <th>Result</th>
                                        <th class="col-open">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($visit->labOrders as $order)
                                        <tr>
                                            <td>
                                                <span class="col-primary">{{ $order->test?->name }}</span>
                                                @if ($order->is_critical)
                                                    <span class="cell-sub">Critical</span>
                                                @endif
                                            </td>
                                            <td>{{ $order->order_no }}</td>
                                            <td class="col-status"><x-admin.status-pill :value="$order->status" /></td>
                                            <td>
                                                @if ($order->result?->isPublished())
                                                    <span class="col-primary">{{ $order->result->displayValue() }}</span>
                                                    <span class="cell-sub">Range {{ $order->result->reference_range ?: '—' }} · {{ $order->result->published_at?->format('d M Y H:i') }}</span>
                                                @elseif ($order->result)
                                                    <span class="cell-sub">Preliminary result awaiting pathologist approval — not yet published.</span>
                                                @else
                                                    <span class="cell-sub">{{ $order->clinical_notes ?: 'Awaiting result' }}</span>
                                                @endif
                                            </td>
                                            <td class="col-open">
                                                @can('lab.view')
                                                    <x-admin.row-menu>
                                                        <a href="{{ route('admin.labs.show', $order) }}">Open</a>
                                                    </x-admin.row-menu>
                                                @else
                                                    —
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="visit-desk__side">
                <section class="visit-desk__panel visit-desk__panel--side">
                    <header class="visit-desk__panel-head">
                        <h2>Encounter history</h2>
                        <p>Steps on this visit</p>
                    </header>
                    <ul class="visit-desk__timeline">
                        @forelse ($visit->events as $event)
                            <li>
                                <div>
                                    <strong>{{ $event->label() }}</strong>
                                    @if ($event->notes)
                                        <span>{{ $event->notes }}</span>
                                    @endif
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
    </div>

    @if ($visit->status === 'completed' || filled($visit->diagnosis) || filled($visit->prescriptions))
        <dialog class="rx-preview" data-rx-preview>
            <div class="rx-preview__bar">
                <div>
                    <p class="rx-preview__title">Prescription preview</p>
                    <p class="rx-preview__sub">{{ $visit->visit_no }} · {{ $visit->patient?->full_name }}</p>
                </div>
                <div class="rx-preview__actions">
                    <a href="{{ route('admin.visits.prescription', ['visit' => $visit, 'print' => 1]) }}" target="_blank" rel="noopener" class="bill-desk__btn bill-desk__btn--primary">Print</a>
                    <a href="{{ route('admin.visits.prescription', $visit) }}" target="_blank" rel="noopener" class="bill-desk__btn bill-desk__btn--ghost">Open full page</a>
                    <button type="button" class="bill-desk__btn bill-desk__btn--ghost" data-rx-preview-close>Close</button>
                </div>
            </div>
            <iframe
                class="rx-preview__frame"
                title="Prescription preview"
                data-rx-preview-frame
                src="{{ route('admin.visits.prescription', ['visit' => $visit, 'embed' => 1]) }}"
            ></iframe>
        </dialog>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const dialog = document.querySelector('[data-rx-preview]');
                if (! dialog) return;

                document.querySelectorAll('[data-rx-preview-open]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (typeof dialog.showModal === 'function') {
                            dialog.showModal();
                        } else {
                            window.open(@json(route('admin.visits.prescription', $visit)), '_blank');
                        }
                    });
                });

                dialog.querySelectorAll('[data-rx-preview-close]').forEach((button) => {
                    button.addEventListener('click', () => dialog.close());
                });

                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) {
                        dialog.close();
                    }
                });
            });
        </script>
    @endif
@endsection
