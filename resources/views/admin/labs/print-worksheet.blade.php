<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab worksheet {{ $order->order_no }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-white font-sans text-ink-900 antialiased">
    <main class="mx-auto max-w-2xl px-6 py-10">
        <div class="no-print mb-6 flex gap-3">
            <button type="button" onclick="window.print()" class="rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Print</button>
            <a href="{{ route('admin.labs.show', $order) }}" class="rounded-full bg-sand-100 px-5 py-2.5 text-sm font-semibold text-navy-900">Back</a>
        </div>

        <div class="rounded-3xl border border-sand-200 p-6">
            <p class="text-xs uppercase tracking-[0.18em] text-teal-700">Laboratory worksheet</p>
            <h1 class="mt-1 font-display text-2xl text-navy-900">{{ $order->order_no }}</h1>
            <p class="mt-1 text-sm text-ink-600">{{ $order->test?->name }} · {{ $order->statusLabel() }}</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->patient?->full_name }}</p>
                    <p class="text-sm text-ink-600">{{ $order->patient?->phone }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Encounter</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->visit?->visit_no ?: '—' }}</p>
                    <p class="text-sm text-ink-600">{{ $order->visit?->doctor?->name ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Sample ID</p>
                    <p class="mt-1 text-xl font-bold text-navy-900">{{ $order->sample_id ?: 'Not collected' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Barcode</p>
                    <p class="mt-1 text-xl font-bold tracking-widest text-navy-900">{{ $order->barcode ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Specimen</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->test?->specimen ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Collected at</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->collected_at?->format('d M Y H:i') ?: '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-ink-400">Clinical notes</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $order->clinical_notes ?: '—' }}</p>
                </div>
            </div>

            <section class="mt-8 border-t border-sand-100 pt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-400">Bench notes</h2>
                <div class="mt-3 min-h-28 rounded-2xl border border-dashed border-sand-300 p-4 text-sm text-ink-400">
                    Processing / reagent / instrument notes
                </div>
            </section>

            <section class="mt-6 border-t border-sand-100 pt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-400">Result entry</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-sand-200 p-3">
                        <p class="text-xs text-ink-400">Value</p>
                        <p class="mt-2 min-h-8 border-b border-sand-200">{{ $order->result?->value ?: '' }}</p>
                    </div>
                    <div class="rounded-2xl border border-sand-200 p-3">
                        <p class="text-xs text-ink-400">Unit</p>
                        <p class="mt-2 min-h-8 border-b border-sand-200">{{ $order->result?->unit ?: ($order->test?->unit ?: '') }}</p>
                    </div>
                    <div class="rounded-2xl border border-sand-200 p-3">
                        <p class="text-xs text-ink-400">Reference</p>
                        <p class="mt-2 min-h-8 border-b border-sand-200">{{ $order->result?->reference_range ?: ($order->test?->reference_range ?: '') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
