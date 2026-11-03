<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient slip {{ $patient->patient_code }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-white font-sans text-ink-900 antialiased">
    <main class="mx-auto max-w-2xl px-6 py-10">
        <div class="no-print mb-6 flex gap-3">
            <button type="button" onclick="window.print()" class="rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Print</button>
            <a href="{{ route('admin.patients.show', $patient) }}" class="rounded-full bg-sand-100 px-5 py-2.5 text-sm font-semibold text-navy-900">Back</a>
        </div>

        <div class="rounded-3xl border border-sand-200 p-6">
            <p class="text-xs uppercase tracking-[0.18em] text-teal-700">Patient slip</p>
            <h1 class="mt-1 font-display text-2xl text-navy-900">{{ $hospital->get('hospital_name') ?: config('app.name') }}</h1>
            <p class="mt-1 text-sm text-ink-600">{{ $hospital->get('phone') }}</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Patient ID</p>
                    <p class="mt-1 text-xl font-bold text-navy-900">{{ $patient->patient_code }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Name</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $patient->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Age / Gender</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $patient->ageLabel() }} · {{ $patient->gender ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Phone</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $patient->phone ?: '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-ink-400">Address</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $patient->address ?: '—' }}</p>
                </div>
            </div>

            <section class="mt-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-400">Recent visits</h2>
                <ul class="mt-3 divide-y divide-sand-100">
                    @forelse ($patient->appointments as $appointment)
                        <li class="flex justify-between gap-3 py-2 text-sm">
                            <span>{{ $appointment->appointment_date?->format('d M Y') }} · {{ $appointment->doctor?->name ?: 'Doctor' }} · {{ $appointment->serialLabel() }}</span>
                            <span class="font-medium">{{ $appointment->status }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-ink-500">No appointments yet.</li>
                    @endforelse
                </ul>
            </section>

            <section class="mt-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-400">Payment status</h2>
                <ul class="mt-3 divide-y divide-sand-100">
                    @forelse ($patient->bills as $bill)
                        <li class="flex justify-between gap-3 py-2 text-sm">
                            <span>{{ $bill->bill_no }} · ৳{{ number_format((float) $bill->total, 2) }}</span>
                            <span class="font-medium">{{ $bill->status }}</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-ink-500">No bills on file.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </main>
</body>
</html>
