@extends('layouts.admin')
@section('title', 'Daily collections')
@section('heading', 'Daily collections')
@section('content')
    <p class="form-lede">Cashier collection report by tender method for the selected day.</p>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="text-sm font-medium text-navy-900" for="date">Date</label>
            <input id="date" type="date" name="date" value="{{ $date }}" class="admin-field mt-2 block px-4 py-3 text-sm">
        </div>
        <button type="submit" class="form-btn form-btn--navy">Show</button>
        <a href="{{ route('admin.bills.index') }}" class="form-btn form-btn--ghost">Bills</a>
    </form>

    <section class="mc-kpis" style="--kpi-count: {{ max(2, $totalsByMethod->count() + 1) }}">
        <article class="mc-kpi mc-kpi--navy">
            <p class="mc-kpi__label">Total collected</p>
            <p class="mc-kpi__value">৳{{ number_format($grandTotal, 2) }}</p>
            <p class="mc-kpi__meta">{{ \Illuminate\Support\Carbon::parse($date)->format('j M Y') }}</p>
        </article>
        @foreach ($paymentMethods as $method => $label)
            <article class="mc-kpi mc-kpi--blue">
                <p class="mc-kpi__label">{{ $label }}</p>
                <p class="mc-kpi__value">৳{{ number_format((float) ($totalsByMethod[$method] ?? 0), 2) }}</p>
                <p class="mc-kpi__meta">Tender total</p>
            </article>
        @endforeach
    </section>

    <div class="table-frame table-frame--fill mt-4">
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Bill</th>
                        <th>Patient</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Cashier</th>
                        <th class="col-num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->collected_at?->format('H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.bills.show', $payment->bill) }}" class="font-semibold text-teal-700">{{ $payment->bill?->bill_no }}</a>
                            </td>
                            <td>{{ $payment->bill?->patient?->full_name }}</td>
                            <td>{{ $payment->methodLabel() }}</td>
                            <td>{{ $payment->reference ?: '—' }}</td>
                            <td>{{ $payment->collectedBy?->name }}</td>
                            <td class="col-num">{{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No collections for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    </div>
@endsection
