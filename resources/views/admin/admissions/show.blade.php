@extends('layouts.admin')
@section('title', $admission->admission_no)
@section('heading', $admission->admission_no)
@section('content')
    <div class="form-workspace">
        <p class="form-lede">
            {{ $admission->sourceLabel() }} · {{ $admission->statusLabel() }}
            · admitted {{ $admission->admitted_at?->format('d M Y H:i') ?: '—' }}
        </p>

        @if ($errors->any())
            <p class="admin-flash admin-flash--error">{{ $errors->first() }}</p>
        @endif

        <section class="form-card">
            <header class="form-card__head">
                <h2>Stay</h2>
                <p>{{ $admission->ward?->name ?: 'No ward' }} · {{ $admission->bed?->bed_code ?: 'No bed' }}</p>
            </header>
            <div class="form-card__body form-grid">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $admission->patient?->full_name }}</p>
                    @can('patients.view')
                        @if ($admission->patient)
                            <a href="{{ route('admin.patients.show', $admission->patient) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700">Patient record</a>
                        @endif
                    @endcan
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Attending doctor</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $admission->attendingDoctor?->name ?: 'Unassigned' }}</p>
                    <p class="text-sm text-ink-600">{{ $admission->department?->name ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Reason</p>
                    <p class="mt-1 text-navy-900">{{ $admission->admission_reason }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Status</p>
                    <div class="mt-2"><x-admin.status-pill :value="$admission->status" /></div>
                </div>
                @if ($admission->appointment)
                    <div class="form-span-2">
                        <a href="{{ route('admin.appointments.show', $admission->appointment) }}" class="text-sm font-semibold text-teal-700">Linked {{ $admission->appointment->appointment_no }}</a>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="mc-layout mt-4">
        <div class="mc-main">
            @if ($admission->isOpen() && $transferTargets->isNotEmpty())
                @can('beds.operate')
                    <x-admin.form-page
                        :action="route('admin.admissions.transfers.store', $admission)"
                        method="POST"
                        :cancel="route('admin.admissions.index')"
                        submit="Transfer patient"
                    >
                        <x-admin.form-section title="Transfer" note="The source bed becomes dirty. History keeps the reason.">
                            <x-form.select name="to_bed_id" label="Destination bed" required>
                                <option value="">Choose bed</option>
                                @foreach ($transferTargets as $target)
                                    <option value="{{ $target->id }}">{{ $target->ward?->name }} · {{ $target->bed_code }}</option>
                                @endforeach
                            </x-form.select>
                            <div class="form-span-2">
                                <x-form.input name="reason" label="Transfer reason" :value="old('reason')" required />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                @endcan
            @endif

            @if ($admission->transfers->isNotEmpty())
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Transfer history</h2>
                        <p>Every bed move on this stay</p>
                    </header>
                    <div class="form-card__body">
                        <ul class="divide-y divide-sand-100">
                            @foreach ($admission->transfers as $transfer)
                                <li class="py-3 text-sm">
                                    <p class="font-semibold text-navy-900">{{ $transfer->fromBed?->bed_code }} → {{ $transfer->toBed?->bed_code }}</p>
                                    <p class="text-ink-600">{{ $transfer->reason ?: 'No reason recorded' }} · {{ $transfer->created_at?->format('d M Y H:i') }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            <section class="form-card mt-4">
                <header class="form-card__head">
                    <h2>Outstanding billing</h2>
                    <p>Tracked on the discharge record</p>
                </header>
                <div class="form-card__body">
                    <ul class="divide-y divide-sand-100">
                        @forelse ($outstandingBills as $bill)
                            <li class="flex items-center justify-between gap-3 py-3 text-sm">
                                <span>{{ $bill->bill_no }} · {{ number_format((float) $bill->total, 2) }} · {{ str_replace('_', ' ', $bill->status) }}</span>
                                @can('billing.manage')
                                    <a href="{{ route('admin.bills.show', $bill) }}" class="font-semibold text-teal-700">Open</a>
                                @endcan
                            </li>
                        @empty
                            <li class="py-6 text-ink-600">No unpaid bills for this patient.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            @can('admissions.discharge')
                @if ($admission->isOpen() && $admission->status !== 'discharge_authorized')
                    <div class="mt-4">
                        <x-admin.form-page
                            :action="route('admin.admissions.authorize', $admission)"
                            method="POST"
                            :cancel="route('admin.admissions.index')"
                            submit="Authorize discharge"
                        >
                            <x-admin.form-section title="Clinical discharge" note="A doctor must authorize the summary before the bed can be released.">
                                <div class="form-span-2">
                                    <x-form.textarea name="discharge_summary" label="Discharge summary" :value="old('discharge_summary')" required />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="discharge_medications" label="Discharge medications" :value="old('discharge_medications')" required />
                                </div>
                                <div class="form-span-2">
                                    <x-form.textarea name="follow_up_plan" label="Follow-up plan" :value="old('follow_up_plan')" required />
                                </div>
                                @if ($outstandingBills->isNotEmpty())
                                    <div class="form-span-2">
                                        <x-form.checkbox name="billing_reviewed" label="I have reviewed the outstanding bills listed above" :checked="old('billing_reviewed', false)" />
                                    </div>
                                @endif
                            </x-admin.form-section>
                        </x-admin.form-page>
                    </div>
                @endif
            @endcan

            @if ($admission->isAuthorizedForRelease())
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Authorized discharge</h2>
                        <p>{{ $admission->authorized_at?->format('d M Y H:i') }} · {{ $admission->authorizedBy?->name ?: 'Doctor' }}</p>
                    </header>
                    <div class="form-card__body form-grid">
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Summary</p><p class="mt-1 text-navy-900">{{ $admission->discharge_summary }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Medications</p><p class="mt-1 text-navy-900">{{ $admission->discharge_medications ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Follow-up</p><p class="mt-1 text-navy-900">{{ $admission->follow_up_plan ?: '—' }}</p></div>
                    </div>
                </section>
                @canany(['admissions.admit', 'admissions.discharge', 'beds.operate'])
                    <form method="POST" action="{{ route('admin.admissions.discharge', $admission) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="form-btn form-btn--primary">Release bed for cleaning</button>
                    </form>
                @endcanany
            @endif

            @if ($admission->status === 'discharged')
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Discharged</h2>
                        <p>{{ $admission->discharged_at?->format('d M Y H:i') }} · {{ $admission->dischargedBy?->name }}</p>
                    </header>
                    <div class="form-card__body form-grid">
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Summary</p><p class="mt-1 text-navy-900">{{ $admission->discharge_summary }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Medications</p><p class="mt-1 text-navy-900">{{ $admission->discharge_medications ?: '—' }}</p></div>
                        <div class="form-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Follow-up</p><p class="mt-1 text-navy-900">{{ $admission->follow_up_plan ?: '—' }}</p></div>
                    </div>
                </section>
            @endif
        </div>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Stay history</h2>
                        <p>Admit, transfer, authorize, discharge</p>
                    </div>
                </div>
                <ul class="mc-stat-list">
                    @forelse ($admission->events as $event)
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
