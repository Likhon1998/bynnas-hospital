@extends('layouts.admin')
@section('title', 'Billing')
@section('heading', 'Patient billing')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $bills->total() }} {{ \Illuminate\Support\Str::plural('bill', $bills->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search bills">
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach (['draft' => 'Draft', 'issued' => 'Issued', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit">Filter</button>
                <a href="{{ route('admin.bills.create') }}" class="admin-action">Generate bill</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Bill</th>
                        <th>Patient</th>
                        <th class="col-when">Date</th>
                        <th class="col-num">Total</th>
                        <th class="col-status">Status</th>
                        <th class="col-open">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bills as $bill)
                        <tr>
                            <td class="col-idx">{{ ($bills->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $bill->bill_no }}</span>
                                <span class="cell-sub">{{ $bill->appointment?->doctor?->name ?: 'Desk bill' }}</span>
                            </td>
                            <td>
                                <span>{{ $bill->patient?->full_name }}</span>
                                <span class="cell-sub">{{ $bill->patient?->phone ?: '—' }}</span>
                            </td>
                            <td class="col-when">
                                <span>{{ $bill->bill_date?->format('d M Y') }}</span>
                                @if ($bill->issued_at)
                                    <span class="cell-sub">Issued {{ $bill->issued_at->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="col-num">
                                <span>{{ number_format((float) $bill->total, 2) }}</span>
                                <span class="cell-sub">Paid {{ number_format((float) $bill->paid_amount, 2) }}</span>
                            </td>
                            <td class="col-status"><x-admin.status-pill :value="$bill->status" /></td>
                            <td class="col-open">
                                <a href="{{ route('admin.bills.show', $bill) }}">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="7">No bills yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $bills->links() }}</div>
@endsection
