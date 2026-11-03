<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Final report {{ $order->order_no }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-white font-sans text-ink-900 antialiased">
    <main class="mx-auto max-w-2xl px-6 py-10">
        <div class="no-print mb-6 flex gap-3">
            <button type="button" onclick="window.print()" class="rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Print / save PDF</button>
            @if (! empty($publicDownload))
                <a href="{{ route('lab-reports.index') }}" class="rounded-full bg-sand-100 px-5 py-2.5 text-sm font-semibold text-navy-900">Back to lookup</a>
            @else
                <a href="{{ route('admin.labs.show', $order) }}" class="rounded-full bg-sand-100 px-5 py-2.5 text-sm font-semibold text-navy-900">Back</a>
            @endif
        </div>

        <div class="rounded-3xl border border-sand-200 p-6">
            <p class="text-xs uppercase tracking-[0.18em] text-teal-700">
                {{ $order->isImagingStudy() ? 'Final imaging report' : 'Final laboratory report' }}
            </p>
            <h1 class="mt-1 font-display text-2xl text-navy-900">{{ $order->order_no }}</h1>
            <p class="mt-1 text-sm text-ink-600">{{ $order->test?->name }} · Published {{ $order->result?->published_at?->format('d M Y H:i') }}</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->patient?->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Ordering clinician</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->visit?->doctor?->name ?: ($order->orderedBy?->name ?: '—') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Sample ID</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->sample_id ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Collected</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->collected_at?->format('d M Y H:i') ?: '—' }}</p>
                </div>
            </div>

            <section class="mt-8 border-t border-sand-100 pt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-400">Result</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-ink-400">Value</p>
                        <p class="mt-1 text-lg font-bold text-navy-900">{{ $order->result?->displayValue() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-ink-400">Reference range</p>
                        <p class="mt-1 font-semibold text-navy-900">{{ $order->result?->reference_range ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-ink-400">Flags</p>
                        <p class="mt-1 font-semibold text-navy-900">
                            @if ($order->result?->is_critical) Critical @elseif ($order->result?->is_abnormal) Abnormal @else Within limits @endif
                        </p>
                    </div>
                </div>
                @if (filled($order->result?->findings))
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-wide text-ink-400">Findings / report</p>
                        <p class="mt-1 whitespace-pre-line text-navy-900">{{ $order->result->findings }}</p>
                    </div>
                @endif
                @if (filled($order->result?->notes))
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-wide text-ink-400">Notes</p>
                        <p class="mt-1 whitespace-pre-line text-navy-900">{{ $order->result->notes }}</p>
                    </div>
                @endif
            </section>

            <section class="mt-10 border-t border-sand-100 pt-6">
                <p class="text-xs uppercase tracking-wide text-ink-400">Digital signature</p>
                <p class="mt-2 font-display text-xl text-navy-900">{{ $order->result?->signature_name }}</p>
                <p class="text-sm text-ink-600">{{ $order->result?->signature_title }}</p>
                <p class="mt-1 text-xs text-ink-500">Signed {{ $order->result?->verified_at?->format('d M Y H:i') }}</p>
            </section>
        </div>
    </main>
</body>
</html>
