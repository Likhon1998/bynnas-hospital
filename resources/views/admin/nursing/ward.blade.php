@extends('layouts.admin')
@section('title', $ward->name)
@section('heading', $ward->name)
@section('content')
    <p class="form-lede">
        {{ $ward->typeLabel() }} · {{ $counts['patients'] }} assigned
        · {{ $counts['due'] }} open tasks
        @if ($counts['overdue'] > 0)
            · {{ $counts['overdue'] }} overdue
        @endif
    </p>

    @if ($errors->any())
        <p class="admin-flash admin-flash--error">{{ $errors->first() }}</p>
    @endif

    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--blue">
            <p class="mc-kpi__label">Assigned</p>
            <p class="mc-kpi__value">{{ $counts['patients'] }}</p>
            <p class="mc-kpi__meta">Occupied beds</p>
        </article>
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Due tasks</p>
            <p class="mc-kpi__value">{{ $counts['due'] }}</p>
            <p class="mc-kpi__meta">Pending or in progress</p>
        </article>
        <article class="mc-kpi mc-kpi--green">
            <p class="mc-kpi__label">Overdue</p>
            <p class="mc-kpi__value">{{ $counts['overdue'] }}</p>
            <p class="mc-kpi__meta">Past due time</p>
        </article>
    </section>

    <div class="table-frame table-frame--fill mt-4">
        <div class="table-toolbar">
            <p class="table-count">Assigned patients</p>
            <a href="{{ route('admin.nursing.index') }}" class="text-sm font-semibold text-teal-700">All wards</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Bed</th>
                        <th>Patient</th>
                        <th>Care / allergies</th>
                        <th>Diagnosis</th>
                        <th>Admission</th>
                        <th class="col-open">Chart</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($beds as $bed)
                        <tr>
                            <td>
                                <span class="col-primary">{{ $bed->bed_code }}</span>
                                <span class="cell-sub">{{ $bed->lengthOfStayLabel() }}</span>
                            </td>
                            <td>
                                <span class="col-primary">{{ $bed->patient?->full_name }}</span>
                                <span class="cell-sub">{{ $bed->patient?->phone }}</span>
                            </td>
                            <td>
                                @if ($bed->patient?->careFlagLabels())
                                    <span>{{ implode(' · ', $bed->patient->careFlagLabels()) }}</span>
                                @else
                                    <span>No care flags</span>
                                @endif
                                <span class="cell-sub">{{ $bed->patient?->allergyLabel() }}</span>
                            </td>
                            <td>{{ $bed->patient?->currentDiagnosis($bed->admission_reason) ?: '—' }}</td>
                            <td>
                                <span>{{ $bed->admitted_at?->format('d M H:i') ?: '—' }}</span>
                                <span class="cell-sub">{{ $bed->admission_reason ?: 'Reason not recorded' }}</span>
                            </td>
                            <td class="col-open">
                                @if ($bed->patient)
                                    <a href="{{ route('admin.nursing.patients.show', $bed->patient) }}">Open chart</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No patients assigned on this ward.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mc-layout mt-4">
        <div class="mc-main">
            <section class="form-card">
                <header class="form-card__head">
                    <h2>Due tasks</h2>
                    <p>Observations, wound care, fluids, and prescribed work still open.</p>
                </header>
                <div class="form-card__body">
                    <ul class="divide-y divide-sand-100">
                        @forelse ($tasks as $task)
                            <li class="flex items-start justify-between gap-3 py-3 text-sm">
                                <div>
                                    <p class="font-semibold text-navy-900">{{ $task->title }}</p>
                                    <p class="text-ink-600">{{ $task->patient?->full_name }} · {{ $task->bed?->bed_code }} · {{ $task->typeLabel() }}</p>
                                    @if ($task->due_at)
                                        <p class="text-xs text-ink-500">Due {{ $task->due_at->format('d M H:i') }}</p>
                                    @endif
                                </div>
                                <x-admin.status-pill :value="$task->displayStatus()" />
                            </li>
                        @empty
                            <li class="py-6 text-ink-600">No open tasks on this ward.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            @can('nursing.handover')
                <div class="mt-4">
                    <x-admin.form-page
                        :action="route('admin.nursing.handovers.store', $ward)"
                        method="POST"
                        :cancel="route('admin.nursing.index')"
                        submit="Hand over shift"
                        lede="Record what the next shift still needs to finish."
                    >
                        <x-admin.form-section title="Shift handover" note="Outstanding work is stored with the time and the nurse who handed over.">
                            <x-form.select name="shift" label="This shift" required>
                                <option value="">Select</option>
                                @foreach (\App\Models\NursingHandover::SHIFTS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('shift') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.select name="incoming_shift" label="Incoming shift" required>
                                <option value="">Select</option>
                                @foreach (\App\Models\NursingHandover::SHIFTS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('incoming_shift') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <div class="form-span-2">
                                <x-form.textarea name="summary" label="Handover summary" :value="old('summary')" required />
                            </div>
                            <div class="form-span-2">
                                <x-form.textarea name="outstanding_work" label="Outstanding work" :value="old('outstanding_work', $outstanding)" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                </div>
            @endcan
        </div>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Handover log</h2>
                        <p>Signed with date and time</p>
                    </div>
                </div>
                <ul class="mc-stat-list">
                    @forelse ($handovers as $handover)
                        <li>
                            <div>
                                <span class="mc-stat-list__dot mc-stat-list__dot--blue"></span>
                                <span>{{ $handover->shiftLabel() }} → {{ $handover->incomingShiftLabel() }} · {{ $handover->summary }}</span>
                            </div>
                            <em>{{ $handover->handed_over_at?->format('d M H:i') }}</em>
                        </li>
                    @empty
                        <li><span>No handovers recorded yet.</span></li>
                    @endforelse
                </ul>
            </section>
        </aside>
    </div>
@endsection
