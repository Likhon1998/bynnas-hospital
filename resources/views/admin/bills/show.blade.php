@extends('layouts.admin')
@section('title', $bill->bill_no)
@section('heading', $bill->bill_no)
@section('content')
    <div class="grid gap-6 xl:grid-cols-[1fr_18rem]">
        <article class="rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-ink-600">Patient</p>
                    <p class="font-medium text-navy-900">{{ $bill->patient?->full_name }}</p>
                    <p class="text-sm text-ink-600">{{ $bill->patient?->phone }}</p>
                </div>
                <x-admin.status-pill :value="$bill->status" />
            </div>
            <p class="mt-4 text-sm text-ink-600">Bill date</p>
            <p class="font-medium text-navy-900">{{ $bill->bill_date?->format('d M Y') }}</p>
            @if ($bill->appointment)
                <p class="mt-4 text-sm text-ink-600">Appointment</p>
                <p class="font-medium text-navy-900">{{ $bill->appointment->appointment_no }} · {{ $bill->appointment->doctor?->name }}</p>
            @endif
            <div class="table-frame table-frame--fit mt-6">
                <table class="pro-table">
                    <thead>
                        <tr>
                            <th class="col-idx">#</th>
                            <th>Item</th>
                            <th class="col-count">Qty</th>
                            <th class="col-num">Unit</th>
                            <th class="col-num">Total</th>
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
            </div>
            <dl class="mt-6 space-y-1 text-sm">
                <div class="flex justify-between gap-8"><dt class="text-ink-600">Subtotal</dt><dd>{{ number_format((float) $bill->subtotal, 2) }}</dd></div>
                <div class="flex justify-between gap-8"><dt class="text-ink-600">Discount</dt><dd>{{ number_format((float) $bill->discount, 2) }}</dd></div>
                <div class="flex justify-between gap-8 font-semibold text-navy-900"><dt>Total</dt><dd>{{ number_format((float) $bill->total, 2) }}</dd></div>
                <div class="flex justify-between gap-8"><dt class="text-ink-600">Paid</dt><dd>{{ number_format((float) $bill->paid_amount, 2) }}</dd></div>
            </dl>
            @if ($bill->notes)
                <p class="mt-4 text-sm text-ink-600">Notes</p>
                <p class="whitespace-pre-wrap text-navy-900">{{ $bill->notes }}</p>
            @endif
        </article>

        <aside class="space-y-3 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <a href="{{ route('admin.bills.print', $bill) }}" class="block rounded-full bg-navy-900 px-5 py-2.5 text-center text-sm font-semibold text-white">Print bill</a>
            @if ($bill->isEditable())
                <a href="{{ route('admin.bills.edit', $bill) }}" class="block rounded-full bg-teal-700 px-5 py-2.5 text-center text-sm font-semibold text-white">Edit draft</a>
                <form method="POST" action="{{ route('admin.bills.issue', $bill) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-full border border-sand-100 px-5 py-2.5 text-sm font-semibold text-navy-900">Issue bill</button>
                </form>
                <x-admin.delete-button :action="route('admin.bills.destroy', $bill)" label="Delete draft" />
            @endif
            @if ($bill->canMarkPaid())
                <form method="POST" action="{{ route('admin.bills.pay', $bill) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Mark as paid</button>
                </form>
            @endif
            @if ($bill->canCancel())
                <form method="POST" action="{{ route('admin.bills.cancel', $bill) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="cancellation_reason" placeholder="Cancellation reason" class="w-full rounded-2xl border border-sand-100 bg-sand-50 px-3 py-2 text-sm">
                    <button type="submit" class="w-full rounded-full px-5 py-2.5 text-sm font-semibold text-red-700">Cancel bill</button>
                </form>
            @endif
            <a href="{{ route('admin.bills.index') }}" class="block px-5 py-2.5 text-center text-sm font-semibold text-ink-600">Back to bills</a>
        </aside>
    </div>
@endsection
