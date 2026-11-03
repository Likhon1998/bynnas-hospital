<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OPD Ticket {{ $appointment->appointment_no }}</title>
    @vite(['resources/css/app.css'])
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-white font-sans text-ink-900 antialiased">
    <main class="mx-auto max-w-lg px-6 py-10">
        <div class="no-print mb-6 flex gap-3">
            <button type="button" onclick="window.print()" class="rounded-full bg-navy-800 px-5 py-2.5 text-sm font-semibold text-white">Print</button>
            <a href="{{ route('admin.appointments.show', $appointment) }}" class="rounded-full bg-sand-100 px-5 py-2.5 text-sm font-semibold text-navy-900">Back</a>
        </div>

        <div class="rounded-3xl border border-sand-200 p-6">
            <p class="text-xs uppercase tracking-[0.18em] text-teal-700">OPD ticket</p>
            <h1 class="mt-1 font-display text-2xl text-navy-900">{{ $hospital->get('hospital_name') ?: config('app.name') }}</h1>
            <p class="mt-1 text-sm text-ink-600">{{ $hospital->get('address') }}</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Serial</p>
                    <p class="mt-1 text-3xl font-bold text-navy-900">{{ $appointment->serialLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Appointment</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->appointment_no }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->status }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Patient</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->patient?->full_name }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->patient?->patient_code }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->patient?->phone }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Doctor</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->doctor?->name ?: '—' }}</p>
                    <p class="text-sm text-ink-600">{{ $appointment->department?->name ?: $appointment->doctor?->department?->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Date</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->appointment_date?->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Time</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $appointment->timeRangeLabel() }}</p>
                </div>
            </div>

            @if ($appointment->reason)
                <p class="mt-6 text-sm text-ink-600"><span class="font-semibold text-navy-900">Reason:</span> {{ $appointment->reason }}</p>
            @endif
        </div>
    </main>
</body>
</html>
