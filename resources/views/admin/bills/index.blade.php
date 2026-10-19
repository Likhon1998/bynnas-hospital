@extends('layouts.admin')
@section('title', 'Billing')
@section('heading', 'Patient billing')
@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by bill number or patient" class="w-full max-w-md rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
            <select name="status" class="rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
                <option value="">All statuses</option>
                @foreach (['draft', 'issued', 'paid', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-full bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white">Filter</button>
        </form>
        <a href="{{ route('admin.bills.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Generate bill</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr>
                    <th class="px-4 py-3">Bill</th>
                    <th class="px-4 py-3">Patient</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($bills as $bill)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $bill->bill_no }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $bill->patient?->full_name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $bill->bill_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ number_format((float) $bill->total, 2) }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$bill->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.bills.show', $bill) }}" class="font-semibold text-teal-700">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-ink-600">No bills yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $bills->links() }}</div>
@endsection
