<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $bill->bill_no }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white font-sans text-ink-900 antialiased">
    <main class="mx-auto max-w-3xl px-6 py-10">
        <div class="flex items-start justify-between gap-6">
            <div>
                <p class="text-sm uppercase tracking-[0.18em] text-teal-700">Hospital bill</p>
                <h1 class="mt-1 font-display text-3xl text-navy-900">{{ $hospital->get('hospital_name') ?: config('app.name') }}</h1>
                <p class="mt-2 text-sm text-ink-600">{{ $hospital->get('address') }}</p>
                <p class="text-sm text-ink-600">{{ $hospital->get('phone') }} · {{ $hospital->get('email') }}</p>
            </div>
            <div class="text-right">
                <p class="font-medium text-navy-900">{{ $bill->bill_no }}</p>
                <p class="text-sm text-ink-600">{{ $bill->bill_date?->format('d M Y') }}</p>
                <p class="mt-2 text-sm font-semibold uppercase">{{ str_replace('_', ' ', $bill->status) }}</p>
            </div>
        </div>

        <section class="mt-8 grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-ink-400">Bill to</p>
                <p class="mt-1 font-medium text-navy-900">{{ $bill->patient?->full_name }}</p>
                <p class="text-sm text-ink-600">{{ $bill->patient?->phone }}</p>
                <p class="text-sm text-ink-600">{{ $bill->patient?->address }}</p>
            </div>
            @if ($bill->appointment)
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink-400">Visit</p>
                    <p class="mt-1 font-medium text-navy-900">{{ $bill->appointment->appointment_no }}</p>
                    <p class="text-sm text-ink-600">{{ $bill->appointment->doctor?->name }}</p>
                    <p class="text-sm text-ink-600">{{ $bill->appointment->appointment_date?->format('d M Y') }}</p>
                </div>
            @endif
        </section>

        <table class="pro-table mt-8">
            <thead>
                <tr>
                    <th class="col-idx">#</th>
                    <th>Description</th>
                    <th class="col-count">Qty</th>
                    <th class="col-num">Unit price</th>
                    <th class="col-num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bill->billItems as $item)
                    <tr>
                        <td class="col-idx">{{ $loop->iteration }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="col-count">{{ $item->quantity }}</td>
                        <td class="col-num">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="col-num">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="mt-6 ml-auto max-w-xs space-y-1 text-sm">
            <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ number_format((float) $bill->subtotal, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Discount</dt><dd>{{ number_format((float) $bill->discount, 2) }}</dd></div>
            <div class="flex justify-between font-semibold text-navy-900"><dt>Total</dt><dd>{{ number_format((float) $bill->total, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Paid</dt><dd>{{ number_format((float) $bill->paid_amount, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Due</dt><dd>{{ number_format($bill->balanceDue(), 2) }}</dd></div>
        </dl>

        @if ($bill->payments->isNotEmpty())
            <section class="mt-8">
                <p class="text-xs uppercase tracking-wide text-ink-400">Money receipt</p>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($bill->payments as $payment)
                        <li class="flex justify-between gap-4">
                            <span>{{ $payment->methodLabel() }}{{ $payment->reference ? ' · '.$payment->reference : '' }} · {{ $payment->collected_at?->format('d M Y H:i') }}</span>
                            <span class="font-semibold">{{ number_format((float) $payment->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if (filled($bill->report_access_code))
            <section class="mt-8 rounded-2xl border border-teal-200 bg-teal-50 px-4 py-4">
                <p class="text-xs uppercase tracking-wide text-teal-800">Lab report access code</p>
                <p class="mt-1 font-display text-3xl tracking-[0.12em] text-navy-900">{{ $bill->report_access_code }}</p>
                <p class="mt-2 text-sm text-ink-600">Use this code with the patient phone number at {{ url('/lab-reports') }} to view and download published reports.</p>
            </section>
        @endif

        @if ($bill->notes)
            <p class="mt-8 text-sm text-ink-600">{{ $bill->notes }}</p>
        @endif

        <p class="mt-10 text-xs text-ink-400">Prepared by {{ $bill->issuedBy?->name ?: 'Reception' }}.</p>
        <p class="mt-6 print:hidden">
            <button type="button" onclick="window.print()" class="rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Print</button>
            <a href="{{ route('admin.bills.show', $bill) }}" class="ml-3 text-sm font-semibold text-teal-700">Back</a>
        </p>
    </main>
</body>
</html>
