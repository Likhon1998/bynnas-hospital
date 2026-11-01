@extends('layouts.admin')
@section('title', $order->order_no)
@section('heading', $order->order_no)
@section('content')
    <div class="form-workspace">
        <p class="form-lede">
            {{ $order->test?->name }} · {{ $order->statusLabel() }}
            · ordered {{ $order->created_at?->format('d M Y H:i') ?: '—' }}
        </p>

        @if ($errors->any())
            <p class="admin-flash admin-flash--error">{{ $errors->first() }}</p>
        @endif

        <section class="form-card">
            <header class="form-card__head">
                <h2>Order</h2>
                <p>{{ $order->test?->categoryLabel() }} · {{ $order->test?->specimen }}</p>
            </header>
            <div class="form-card__body form-grid">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->patient?->full_name }}</p>
                    @can('patients.view')
                        @if ($order->patient)
                            <a href="{{ route('admin.patients.show', $order->patient) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700">Patient record</a>
                        @endif
                    @endcan
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Encounter</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->visit?->visit_no ?: '—' }}</p>
                    @can('visits.view')
                        @if ($order->visit)
                            <a href="{{ route('admin.visits.show', $order->visit) }}" class="mt-2 inline-flex text-sm font-semibold text-teal-700">Open encounter</a>
                        @endif
                    @endcan
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Status</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <x-admin.status-pill :value="$order->status" />
                        @if ($order->is_critical)
                            <x-admin.status-pill value="critical" />
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Price</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ number_format((float) ($order->test?->price ?? 0), 2) }}</p>
                </div>
                <div class="form-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Clinical notes</p>
                    <p class="mt-1 text-navy-900">{{ $order->clinical_notes ?: '—' }}</p>
                </div>
            </div>
        </section>
    </div>

    <div class="mc-layout mt-4">
        <div class="mc-main">
            @can('lab.collect')
                @if ($order->canCollect())
                    <form method="POST" action="{{ route('admin.labs.collect', $order) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="form-btn form-btn--primary">Mark sample collected</button>
                    </form>
                @endif
            @endcan

            @can('lab.report')
                @if ($order->canProcess())
                    <form method="POST" action="{{ route('admin.labs.process', $order) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="form-btn form-btn--navy">Start processing</button>
                    </form>
                @endif

                @if ($order->canRecordResult())
                    <x-admin.form-page
                        :action="route('admin.labs.results.store', $order)"
                        method="POST"
                        :cancel="route('admin.labs.index')"
                        submit="Record and verify"
                    >
                        <x-admin.form-section title="Result" note="Manual entry. The verifying technician is recorded on this result.">
                            <x-form.input name="value" label="Result value" :value="old('value')" required />
                            <x-form.input name="unit" label="Unit" :value="old('unit', $order->test?->unit)" />
                            <x-form.input name="reference_range" label="Reference range" :value="old('reference_range', $order->test?->reference_range)" />
                            <div class="form-span-2">
                                <x-form.textarea name="notes" label="Notes" :value="old('notes')" />
                            </div>
                            <div class="form-span-2">
                                <x-form.checkbox name="is_critical" label="Flag as critical (also auto-flagged from catalog limits)" :checked="old('is_critical', false)" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                @endif
            @endcan

            @if ($order->result)
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>Verified result</h2>
                        <p>{{ $order->result->verified_at?->format('d M Y H:i') }} · {{ $order->result->verifiedBy?->name ?: 'Technician' }}</p>
                    </header>
                    <div class="form-card__body form-grid">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Value</p>
                            <p class="mt-1 font-semibold text-navy-900">{{ $order->result->displayValue() }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Reference range</p>
                            <p class="mt-1 text-navy-900">{{ $order->result->reference_range ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Critical</p>
                            <p class="mt-1 text-navy-900">{{ $order->result->is_critical ? 'Yes — clinician notified' : 'No' }}</p>
                        </div>
                        <div class="form-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Notes</p>
                            <p class="mt-1 text-navy-900">{{ $order->result->notes ?: '—' }}</p>
                        </div>
                    </div>
                </section>
            @endif

            @if ($order->canCancel())
                @canany(['lab.order', 'lab.report'])
                    <div class="mt-4">
                        <x-admin.form-page
                            :action="route('admin.labs.cancel', $order)"
                            method="POST"
                            :cancel="route('admin.labs.index')"
                            submit="Cancel order"
                        >
                            <x-admin.form-section title="Cancel" note="If the sample has not been collected, the draft lab charge is removed.">
                                <div class="form-span-2">
                                    <x-form.input name="cancellation_reason" label="Reason" :value="old('cancellation_reason')" required />
                                </div>
                            </x-admin.form-section>
                        </x-admin.form-page>
                    </div>
                @endcanany
            @endif
        </div>

        <aside class="mc-side">
            <section class="mc-card">
                <div class="mc-card__head">
                    <div>
                        <h2>Order history</h2>
                        <p>Ordered, collected, verified</p>
                    </div>
                </div>
                <ul class="mc-stat-list">
                    @forelse ($order->events as $event)
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
