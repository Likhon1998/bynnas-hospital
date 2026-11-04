<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ request()->boolean('embed') ? 'Preview' : 'Prescription' }} {{ $visit->visit_no }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .rx-paper { box-shadow: none !important; border-color: #cbd5e1 !important; }
        }
        body.is-embed {
            background: #fff !important;
            padding: 0 !important;
        }
        body.is-embed main {
            padding: 0.75rem !important;
        }
        .rx-paper {
            max-width: 48rem;
            margin: 0 auto;
            padding: 1.25rem 1.35rem 1.5rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.85rem;
            background: #fff;
        }
        .rx-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 0.85rem;
            border-bottom: 2px solid #0f766e;
        }
        .rx-brand {
            min-width: 0;
        }
        .rx-brand h1 {
            margin: 0.15rem 0 0;
            color: #0f172a;
            font-family: var(--font-display, Georgia, serif);
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .rx-eyebrow {
            margin: 0;
            color: #0f766e;
            font-size: 0.7rem;
            font-weight: 750;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .rx-meta {
            margin: 0.25rem 0 0;
            color: #475569;
            font-size: 0.82rem;
        }
        .rx-badge {
            flex: 0 0 auto;
            text-align: right;
        }
        .rx-badge strong {
            display: block;
            color: #0f172a;
            font-size: 1rem;
        }
        .rx-grid {
            display: grid;
            gap: 0.65rem 1rem;
            margin-top: 0.9rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .rx-label {
            margin: 0;
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 750;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .rx-value {
            margin: 0.12rem 0 0;
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 650;
        }
        .rx-section {
            margin-top: 0.95rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
        }
        .rx-section h2 {
            margin: 0 0 0.45rem;
            color: #0f766e;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .rx-section p,
        .rx-section li {
            color: #0f172a;
            font-size: 0.9rem;
            line-height: 1.45;
            white-space: pre-wrap;
        }
        .rx-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
        }
        .rx-table th,
        .rx-table td {
            padding: 0.4rem 0.5rem;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }
        .rx-table th {
            width: 8.5rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 750;
            text-transform: uppercase;
        }
        .rx-meds {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }
        .rx-meds th,
        .rx-meds td {
            padding: 0.45rem 0.55rem;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        .rx-meds thead th {
            background: #ecfdf5;
            color: #0f766e;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .rx-meds td:first-child {
            width: 2rem;
            text-align: center;
            font-weight: 700;
        }
        .rx-follow {
            padding: 0.7rem 0.8rem;
            border: 1px solid #99f6e4;
            border-radius: 0.65rem;
            background: #f0fdfa;
        }
        .rx-sign {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.4rem;
            padding-top: 0.9rem;
            border-top: 1px dashed #cbd5e1;
        }
        .rx-sign p {
            margin: 0;
            color: #64748b;
            font-size: 0.78rem;
        }
        .rx-sign strong {
            display: block;
            margin-top: 1.6rem;
            color: #0f172a;
            font-size: 0.9rem;
        }
        .rx-empty {
            color: #94a3b8;
            font-style: italic;
        }
        @media (max-width: 640px) {
            .rx-grid { grid-template-columns: 1fr; }
            .rx-sign { flex-direction: column; }
        }
    </style>
</head>
<body @class(['bg-slate-100 font-sans text-ink-900 antialiased', 'is-embed' => request()->boolean('embed')])>
    <main class="px-4 py-6 sm:px-6">
        @unless (request()->boolean('embed'))
            <div class="no-print mx-auto mb-4 flex max-w-3xl flex-wrap items-center gap-2">
                <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">Prescription preview</span>
                <button type="button" onclick="window.print()" class="rounded-lg bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Print prescription</button>
                <a href="{{ route('admin.visits.show', $visit) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-navy-900">Back to encounter</a>
            </div>
        @endunless

        <article class="rx-paper">
            <header class="rx-head">
                <div class="rx-brand">
                    <p class="rx-eyebrow">Medical prescription</p>
                    <h1>{{ $hospital->get('hospital_name') ?: config('app.name') }}</h1>
                    <p class="rx-meta">
                        {{ $hospital->get('address') ?: '' }}
                        @if ($hospital->get('phone'))
                            · {{ $hospital->get('phone') }}
                        @endif
                    </p>
                </div>
                <div class="rx-badge">
                    <p class="rx-label">Encounter</p>
                    <strong>{{ $visit->visit_no }}</strong>
                    <p class="rx-meta">{{ $visit->completed_at?->format('d M Y H:i') ?: $visit->arrived_at?->format('d M Y H:i') }}</p>
                </div>
            </header>

            <div class="rx-grid">
                <div>
                    <p class="rx-label">Patient</p>
                    <p class="rx-value">{{ $visit->patient?->full_name }}</p>
                    <p class="rx-meta">{{ $visit->patient?->patient_code }} · {{ $visit->patient?->ageLabel() }} · {{ $visit->patient?->gender ? ucfirst($visit->patient->gender) : '—' }}</p>
                    <p class="rx-meta">{{ $visit->patient?->phone ?: '—' }}</p>
                </div>
                <div>
                    <p class="rx-label">Doctor</p>
                    <p class="rx-value">{{ $visit->doctor?->name ?: 'Doctor' }}</p>
                    <p class="rx-meta">{{ $visit->department?->name ?: $visit->doctor?->department?->name ?: '—' }}</p>
                </div>
                <div>
                    <p class="rx-label">Appointment</p>
                    @if ($visit->appointment)
                        <p class="rx-value">{{ $visit->appointment->appointment_date?->format('d M Y') }}</p>
                        <p class="rx-meta">{{ $visit->appointment->timeRangeLabel() }} · {{ $visit->appointment->appointment_no }}</p>
                    @else
                        <p class="rx-value">Walk-in</p>
                        <p class="rx-meta">Arrived {{ $visit->arrived_at?->format('d M Y H:i') ?: '—' }}</p>
                    @endif
                </div>
                <div>
                    <p class="rx-label">Chief complaint</p>
                    <p class="rx-value">{{ $visit->chief_complaint ?: '—' }}</p>
                </div>
            </div>

            @if ($visit->hasVitals())
                <section class="rx-section">
                    <h2>Vitals</h2>
                    <table class="rx-table">
                        <tbody>
                            <tr>
                                <th>BP</th><td>{{ $visit->blood_pressure }}</td>
                                <th>Temp</th><td>{{ $visit->temperature }} °C</td>
                            </tr>
                            <tr>
                                <th>Pulse</th><td>{{ $visit->pulse }}</td>
                                <th>SpO₂</th><td>{{ $visit->oxygen_saturation }}%</td>
                            </tr>
                            <tr>
                                <th>Weight</th><td>{{ $visit->weight }} kg</td>
                                <th>Pain</th><td>{{ $visit->pain_score }}/10</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            @endif

            <section class="rx-section">
                <h2>Clinical findings</h2>
                <table class="rx-table">
                    <tbody>
                        <tr>
                            <th>Symptoms</th>
                            <td>{{ $visit->symptoms ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Examination</th>
                            <td>{{ $visit->examination_notes ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Diagnosis</th>
                            <td>{{ $visit->diagnosis ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Treatment</th>
                            <td>{{ $visit->treatment_plan ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="rx-section">
                <h2>Previous reports summary</h2>
                @if ($previousVisits->isEmpty())
                    <p class="rx-empty">No earlier completed encounters on file.</p>
                @else
                    <table class="rx-table">
                        <thead>
                            <tr>
                                <th style="width:6.5rem">Date</th>
                                <th>Summary</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($previousVisits as $prior)
                                <tr>
                                    <td>{{ $prior->completed_at?->format('d M Y') ?: '—' }}</td>
                                    <td>
                                        <strong>{{ $prior->diagnosis ?: 'Encounter '.$prior->visit_no }}</strong>
                                        @if ($prior->doctor)
                                            · {{ $prior->doctor->name }}
                                        @endif
                                        @if ($prior->treatment_plan)
                                            <br>{{ \Illuminate\Support\Str::limit($prior->treatment_plan, 120) }}
                                        @endif
                                        @php
                                            $priorLabs = $prior->labOrders->filter(fn ($order) => $order->result?->isPublished());
                                        @endphp
                                        @if ($priorLabs->isNotEmpty())
                                            <br>
                                            @foreach ($priorLabs as $order)
                                                {{ $order->test?->name }}: {{ $order->result?->displayValue() }}@if (! $loop->last); @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            <section class="rx-section">
                <h2>Investigations / tests</h2>
                @if ($visit->labOrders->isEmpty())
                    <p class="rx-empty">No laboratory or imaging tests ordered on this visit.</p>
                @else
                    <table class="rx-meds">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Test</th>
                                <th>Status</th>
                                <th>Result / next step</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($visit->labOrders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->test?->name }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}@if ($order->is_critical) · Critical @endif</td>
                                    <td>
                                        @if ($order->result?->isPublished())
                                            {{ $order->result->displayValue() }}
                                            @if ($order->result->reference_range)
                                                (ref {{ $order->result->reference_range }})
                                            @endif
                                        @elseif ($order->result)
                                            Preliminary — await pathologist approval
                                        @else
                                            Still to complete — follow hospital lab instructions
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            <section class="rx-section">
                <h2>Rx — Medicines</h2>
                @if ($medicineLines->isEmpty())
                    <p class="rx-empty">No medicines recorded on this encounter.</p>
                @else
                    <table class="rx-meds">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Medicine &amp; dose</th>
                                <th>How to take</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicineLines as $line)
                                @php
                                    $parts = preg_split('/\s*[—–|-]\s*/', $line, 2);
                                    $medicine = trim($parts[0] ?? $line);
                                    $how = trim($parts[1] ?? '');
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $medicine }}</td>
                                    <td>{{ $how !== '' ? $how : 'As advised by the doctor' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="rx-meta" style="margin-top:0.45rem">Tip for doctors: write each medicine on its own line as <em>Name dose — times per day / duration</em>.</p>
                @endif
            </section>

            <section class="rx-section">
                <h2>Next check / follow-up</h2>
                <div class="rx-follow">
                    <p>{{ $visit->follow_up_instructions ?: 'Follow up if symptoms worsen or as advised at the desk.' }}</p>
                </div>
            </section>

            <footer class="rx-sign">
                <div>
                    <p>Printed {{ now()->format('d M Y H:i') }}</p>
                    <p>Keep this paper for your next visit and pharmacy.</p>
                </div>
                <div style="text-align:right">
                    <p>Doctor’s signature</p>
                    <strong>{{ $visit->doctor?->name ?: ($visit->clinicallyRecordedBy?->name ?: '————————') }}</strong>
                </div>
            </footer>
        </article>
    </main>

    @if (request()->boolean('print') && ! request()->boolean('embed'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
