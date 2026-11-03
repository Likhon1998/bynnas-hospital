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
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Sample ID</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->sample_id ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Barcode</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->barcode ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Collected</p>
                    <p class="mt-1 text-navy-900">{{ $order->collected_at?->format('d M Y H:i') ?: '—' }}</p>
                </div>
                <div class="form-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Clinical notes</p>
                    <p class="mt-1 text-navy-900">{{ $order->clinical_notes ?: '—' }}</p>
                </div>
                <div class="form-span-2 flex flex-wrap gap-4">
                    <a href="{{ route('admin.labs.worksheet', $order) }}" class="text-sm font-semibold text-teal-700" target="_blank">Print laboratory worksheet</a>
                    @if ($order->result?->isPublished())
                        <a href="{{ route('admin.labs.report', $order) }}" class="text-sm font-semibold text-teal-700" target="_blank">Print final report</a>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <div class="mc-layout mt-4">
        <div class="mc-main">
            @can('lab.collect')
                @if ($order->canCollect())
                    <x-admin.form-page
                        :action="route('admin.labs.collect', $order)"
                        method="POST"
                        :cancel="route('admin.labs.index')"
                        submit="Mark sample collected"
                    >
                        <x-admin.form-section title="Sample collection" note="Leave Sample ID blank to auto-generate. Barcode defaults to the Sample ID.">
                            <x-form.input name="sample_id" label="Sample ID" :value="old('sample_id')" hint="Optional — auto-generated if blank" />
                            <x-form.input name="barcode" label="Barcode" :value="old('barcode')" hint="Optional — defaults to Sample ID" />
                            <x-form.input name="collected_at" label="Collected at" type="datetime-local" :value="old('collected_at', now()->format('Y-m-d\TH:i'))" />
                        </x-admin.form-section>
                    </x-admin.form-page>
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
                        submit="Save preliminary & submit"
                    >
                        <x-admin.form-section :title="$order->isImagingStudy() ? 'Preliminary imaging report' : 'Preliminary result'" note="Saved for pathologist verification. Not published as a final report until approved.">
                            <x-form.input name="value" :label="$order->isImagingStudy() ? 'Impression summary' : 'Result value'" :value="old('value')" required />
                            <x-form.input name="unit" label="Unit" :value="old('unit', $order->test?->unit)" />
                            <x-form.input name="reference_range" label="Reference range" :value="old('reference_range', $order->test?->reference_range)" />
                            <div class="form-span-2">
                                <x-form.textarea name="findings" :label="$order->isImagingStudy() ? 'Imaging findings' : 'Findings'" :value="old('findings')" />
                            </div>
                            <div class="form-span-2">
                                <x-form.textarea name="notes" label="Notes" :value="old('notes')" />
                            </div>
                            <div class="form-span-2">
                                <x-form.checkbox name="is_abnormal" label="Mark as abnormal / out of range" :checked="old('is_abnormal', false)" />
                            </div>
                            <div class="form-span-2">
                                <x-form.checkbox name="is_critical" label="Flag as critical / urgent (also auto-flagged from catalog limits)" :checked="old('is_critical', false)" />
                            </div>
                        </x-admin.form-section>
                    </x-admin.form-page>
                @endif
            @endcan

            @can('lab.verify')
                @if ($order->canVerify())
                    <x-admin.form-page
                        :action="route('admin.labs.verify', $order)"
                        method="POST"
                        :cancel="route('admin.labs.index')"
                        submit="Approve, sign & publish"
                    >
                        <x-admin.form-section title="Pathologist verification" note="Review accuracy and reference ranges. Publishing releases the final report to clinicians.">
                            <x-form.input name="value" label="Result value" :value="old('value', $order->result?->value)" required />
                            <x-form.input name="unit" label="Unit" :value="old('unit', $order->result?->unit)" />
                            <x-form.input name="reference_range" label="Reference range" :value="old('reference_range', $order->result?->reference_range)" />
                            <div class="form-span-2">
                                <x-form.textarea name="findings" :label="$order->isImagingStudy() ? 'Imaging findings / radiology report' : 'Findings'" :value="old('findings', $order->result?->findings)" />
                            </div>
                            <div class="form-span-2">
                                <x-form.textarea name="notes" label="Verifier notes" :value="old('notes', $order->result?->notes)" />
                            </div>
                            <div class="form-span-2">
                                <x-form.checkbox name="is_abnormal" label="Mark as abnormal" :checked="old('is_abnormal', $order->result?->is_abnormal)" />
                            </div>
                            <div class="form-span-2">
                                <x-form.checkbox name="is_critical" label="Mark as urgent / critical" :checked="old('is_critical', $order->result?->is_critical)" />
                            </div>
                            <x-form.input name="signature_name" label="Digital signature name" :value="old('signature_name', auth()->user()?->name)" required />
                            <x-form.input name="signature_title" label="Signature title" :value="old('signature_title', 'Pathologist')" />
                        </x-admin.form-section>
                    </x-admin.form-page>

                    <div class="mt-4">
                        <x-admin.form-page
                            :action="route('admin.labs.return', $order)"
                            method="POST"
                            :cancel="route('admin.labs.show', $order)"
                            submit="Return for correction"
                        >
                            <x-admin.form-section title="Return to laboratory" note="Clears the preliminary entry and sends the order back to processing.">
                                <div class="form-span-2">
                                    <x-form.input name="return_reason" label="Correction reason" :value="old('return_reason')" required />
                                </div>
                            </x-admin.form-section>
                        </x-admin.form-page>
                    </div>
                @endif
            @endcan

            @if ($order->result)
                <section class="form-card mt-4">
                    <header class="form-card__head">
                        <h2>{{ $order->result->isPublished() ? 'Published final report' : ($order->result->isVerified() ? 'Verified result' : 'Preliminary result') }}</h2>
                        <p>
                            @if ($order->result->isVerified())
                                {{ $order->result->verified_at?->format('d M Y H:i') }} · {{ $order->result->signature_name ?: ($order->result->verifiedBy?->name ?: 'Pathologist') }}
                            @else
                                Entered {{ $order->result->created_at?->format('d M Y H:i') }} · {{ $order->result->enteredBy?->name ?: 'Technician' }} · awaiting verification
                            @endif
                        </p>
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
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Flags</p>
                            <p class="mt-1 text-navy-900">
                                @if ($order->result->is_critical) Critical
                                @elseif ($order->result->is_abnormal) Abnormal
                                @else Normal
                                @endif
                            </p>
                        </div>
                        @if (filled($order->result->findings))
                            <div class="form-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Findings</p>
                                <p class="mt-1 whitespace-pre-line text-navy-900">{{ $order->result->findings }}</p>
                            </div>
                        @endif
                        <div class="form-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Notes</p>
                            <p class="mt-1 text-navy-900">{{ $order->result->notes ?: '—' }}</p>
                        </div>
                        @if ($order->result->isPublished())
                            <div class="form-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Digital signature</p>
                                <p class="mt-1 font-semibold text-navy-900">{{ $order->result->signature_name }} · {{ $order->result->signature_title }}</p>
                            </div>
                        @endif
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
