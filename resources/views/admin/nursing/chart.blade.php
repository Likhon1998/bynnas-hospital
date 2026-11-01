@extends('layouts.admin')
@section('title', $patient->full_name)
@section('heading', $patient->full_name)
@section('content')
    <div class="form-workspace">
        <p class="form-lede">
            {{ $bed->ward?->name }} · {{ $bed->bed_code }} · admitted {{ $bed->admitted_at?->format('d M Y H:i') ?: '—' }}
        </p>

        @if ($errors->any())
            <p class="admin-flash admin-flash--error">{{ $errors->first() }}</p>
        @endif

        <section class="form-card">
            <header class="form-card__head">
                <h2>Admission</h2>
                <p>{{ $openTaskCount }} open {{ \Illuminate\Support\Str::plural('task', $openTaskCount) }}</p>
            </header>
            <div class="form-card__body form-grid">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Care flags</p>
                    <p class="mt-1 text-navy-900">{{ $patient->careFlagLabels() !== [] ? implode(' · ', $patient->careFlagLabels()) : 'None' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Allergies</p>
                    <p class="mt-1 text-navy-900">{{ $patient->allergyLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Diagnosis</p>
                    <p class="mt-1 text-navy-900">{{ $patient->currentDiagnosis($bed->admission_reason) ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Reason / stay</p>
                    <p class="mt-1 text-navy-900">{{ $bed->admission_reason ?: '—' }} · {{ $bed->lengthOfStayLabel() }}</p>
                </div>
                <div class="form-span-2">
                    <a href="{{ route('admin.nursing.wards.show', $bed->ward) }}" class="text-sm font-semibold text-teal-700">Back to {{ $bed->ward?->name }}</a>
                </div>
            </div>
        </section>
    </div>

    <div class="mc-layout mt-4">
        <div class="mc-main">
            @can('nursing.chart')
                <x-admin.form-page
                    :action="route('admin.nursing.observations.store', $patient)"
                    method="POST"
                    :cancel="route('admin.nursing.wards.show', $bed->ward)"
                    submit="Save observation"
                >
                    <x-admin.form-section title="Record observation" note="Vitals are stored with the nurse and the exact time. Leave notes only if this is a general observation.">
                        <x-form.select name="observation_type" label="Type" required>
                            @foreach (\App\Models\NursingObservation::TYPES as $value => $label)
                                <option value="{{ $value }}" @selected(old('observation_type', 'vitals') === $value)>{{ $label }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input name="blood_pressure" label="Blood pressure" :value="old('blood_pressure')" placeholder="120/80" />
                        <x-form.input name="temperature" label="Temperature (°C)" type="number" :value="old('temperature')" step="0.1" />
                        <x-form.input name="pulse" label="Pulse (bpm)" type="number" :value="old('pulse')" />
                        <x-form.input name="oxygen_saturation" label="Oxygen saturation (%)" type="number" :value="old('oxygen_saturation')" />
                        <x-form.input name="weight" label="Weight (kg)" type="number" :value="old('weight')" step="0.1" />
                        <x-form.input name="pain_score" label="Pain score (0–10)" type="number" :value="old('pain_score')" />
                        <div class="form-span-2">
                            <x-form.textarea name="notes" label="Observation notes" :value="old('notes')" />
                        </div>
                    </x-admin.form-section>
                </x-admin.form-page>
            @endcan

            @if ($observations->isNotEmpty())
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Vital trend</h2>
                        <p>Latest {{ $observations->count() }} readings, newest first</p>
                    </header>
                    <div class="form-card__body">
                        <div class="table-scroll">
                            <table class="pro-table">
                                <thead>
                                    <tr>
                                        <th>When</th>
                                        <th>BP</th>
                                        <th>Temp</th>
                                        <th>Pulse</th>
                                        <th>SpO2</th>
                                        <th>Pain</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($observations as $observation)
                                        <tr>
                                            <td>{{ $observation->recorded_at?->format('d M H:i') }}</td>
                                            <td>{{ $observation->blood_pressure ?: '—' }}</td>
                                            <td>{{ $observation->temperature ? $observation->temperature.' °C' : '—' }}</td>
                                            <td>{{ $observation->pulse ?: '—' }}</td>
                                            <td>{{ $observation->oxygen_saturation ? $observation->oxygen_saturation.'%' : '—' }}</td>
                                            <td>{{ $observation->pain_score !== null ? $observation->pain_score.'/10' : '—' }}</td>
                                            <td>{{ $observation->recordedBy?->name ?: '—' }}</td>
                                        </tr>
                                        @if ($observation->notes)
                                            <tr>
                                                <td colspan="7" class="text-sm text-ink-600">{{ $observation->typeLabel() }} · {{ $observation->notes }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            @can('nursing.chart')
                <div class="mt-4">
                    <x-admin.form-page
                        :action="route('admin.nursing.tasks.store', $patient)"
                        method="POST"
                        :cancel="route('admin.nursing.wards.show', $bed->ward)"
                        submit="Add task"
                    >
                        <x-admin.form-section title="Nursing task" note="Observations, wound care, fluid monitoring, or other prescribed care.">
                            <x-form.select name="task_type" label="Type" required>
                                @foreach (\App\Models\NursingTask::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('task_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.input name="title" label="Task" :value="old('title')" required />
                            <x-form.input name="due_at" label="Due" type="datetime-local" :value="old('due_at')" />
                            <div class="form-span-2">
                                <x-form.textarea name="instructions" label="Instructions" :value="old('instructions')" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                </div>
            @endcan

            <section class="form-card mt-4">
                <header class="form-card__head">
                    <h2>Tasks</h2>
                    <p>Pending, in progress, completed, or overdue</p>
                </header>
                <div class="form-card__body">
                    <ul class="divide-y divide-sand-100">
                        @forelse ($tasks as $task)
                            <li class="py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-navy-900">{{ $task->title }}</p>
                                        <p class="text-sm text-ink-600">{{ $task->typeLabel() }}@if ($task->due_at) · due {{ $task->due_at->format('d M H:i') }}@endif</p>
                                        @if ($task->instructions)
                                            <p class="mt-1 text-sm text-ink-500">{{ $task->instructions }}</p>
                                        @endif
                                    </div>
                                    <x-admin.status-pill :value="$task->displayStatus()" />
                                </div>
                                @can('nursing.chart')
                                    @if ($task->isOpen())
                                        <form method="POST" action="{{ route('admin.nursing.tasks.update', $task) }}" class="mt-2 flex flex-wrap gap-2">
                                            @csrf
                                            @method('PATCH')
                                            @if ($task->status === 'pending')
                                                <button type="submit" name="status" value="in_progress" class="form-btn form-btn--ghost">Start</button>
                                            @endif
                                            <button type="submit" name="status" value="completed" class="form-btn form-btn--primary">Complete</button>
                                        </form>
                                    @endif
                                @endcan
                            </li>
                        @empty
                            <li class="py-6 text-ink-600">No nursing tasks yet.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            @can('nursing.meds')
                <div class="mt-4">
                    <x-admin.form-page
                        :action="route('admin.nursing.medications.store', $patient)"
                        method="POST"
                        :cancel="route('admin.nursing.wards.show', $bed->ward)"
                        submit="Record administration"
                    >
                        <x-admin.form-section title="Medication administration" note="Each dose is stamped with the nurse and the time. This record cannot be edited after save.">
                            <x-form.input name="medication_name" label="Medication" :value="old('medication_name')" required />
                            <x-form.input name="dose" label="Dose" :value="old('dose')" required />
                            <x-form.select name="route" label="Route" required>
                                @foreach (\App\Models\MedicationAdministration::ROUTES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('route') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.select name="status" label="Result" required>
                                @foreach (\App\Models\MedicationAdministration::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'given') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <div class="form-span-2">
                                <x-form.textarea name="notes" label="Notes" :value="old('notes')" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                </div>
            @endcan

            @if ($medications->isNotEmpty())
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Medication record</h2>
                        <p>Auditable administration history</p>
                    </header>
                    <div class="form-card__body">
                        <ul class="divide-y divide-sand-100">
                            @foreach ($medications as $medication)
                                <li class="flex items-start justify-between gap-3 py-3 text-sm">
                                    <div>
                                        <p class="font-semibold text-navy-900">{{ $medication->medication_name }} · {{ $medication->dose }}</p>
                                        <p class="text-ink-600">{{ $medication->routeLabel() }} · {{ $medication->administeredBy?->name }} · {{ $medication->administered_at?->format('d M Y H:i') }}</p>
                                    </div>
                                    <x-admin.status-pill :value="$medication->status" />
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif
        </div>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Clinical audit</h2>
                        <p>Who recorded what, and when</p>
                    </div>
                </div>
                <ul class="mc-stat-list">
                    @forelse ($auditEvents as $event)
                        <li>
                            <div>
                                <span class="mc-stat-list__dot mc-stat-list__dot--blue"></span>
                                <span>{{ $event->summary }}</span>
                            </div>
                            <em>{{ $event->created_at?->format('H:i') }}</em>
                        </li>
                    @empty
                        <li><span>No clinical entries yet.</span></li>
                    @endforelse
                </ul>
            </section>
        </aside>
    </div>
@endsection
