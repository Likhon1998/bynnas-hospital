@extends('layouts.admin')
@section('title', 'Laboratory')
@section('heading', 'Laboratory')
@section('content')
    <p class="form-lede">Order, collect, process, and verify. Results stay on the encounter and the patient record.</p>

    <section class="mc-kpis">
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Ordered</p>
            <p class="mc-kpi__value">{{ $counts['ordered'] }}</p>
            <p class="mc-kpi__meta">Awaiting collection</p>
        </article>
        <article class="mc-kpi mc-kpi--blue">
            <p class="mc-kpi__label">Collected</p>
            <p class="mc-kpi__value">{{ $counts['collected'] }}</p>
            <p class="mc-kpi__meta">Ready for the bench</p>
        </article>
        <article class="mc-kpi mc-kpi--green">
            <p class="mc-kpi__label">Processing</p>
            <p class="mc-kpi__value">{{ $counts['processing'] }}</p>
            <p class="mc-kpi__meta">In the laboratory</p>
        </article>
        <article class="mc-kpi mc-kpi--orange">
            <p class="mc-kpi__label">Critical</p>
            <p class="mc-kpi__value">{{ $counts['critical'] }}</p>
            <p class="mc-kpi__meta">Verified and flagged</p>
        </article>
    </section>

    <div class="table-frame table-frame--fill mt-4">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search patient, test, or order no">
                <select name="status">
                    <option value="">Open</option>
                    @foreach (\App\Models\LabOrder::STATUSES as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit">Filter</button>
                @can('lab.order')
                    <a href="{{ route('admin.labs.create') }}" class="admin-action">Order test</a>
                @endcan
                @can('lab.view')
                    <a href="{{ route('admin.labs.tests.index') }}" class="admin-action">Catalog</a>
                @endcan
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Patient</th>
                        <th>Test</th>
                        <th>Encounter</th>
                        <th class="col-status">Status</th>
                        <th class="col-open">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <span class="col-primary">{{ $order->order_no }}</span>
                                <span class="cell-sub">{{ $order->created_at?->format('d M H:i') }}</span>
                            </td>
                            <td>{{ $order->patient?->full_name }}</td>
                            <td>
                                <span class="col-primary">{{ $order->test?->name }}</span>
                                <span class="cell-sub">{{ $order->test?->code }}</span>
                            </td>
                            <td>{{ $order->visit?->visit_no ?: '—' }}</td>
                            <td class="col-status">
                                <x-admin.status-pill :value="$order->status" />
                                @if ($order->is_critical)
                                    <x-admin.status-pill value="critical" />
                                @endif
                            </td>
                            <td class="col-open"><a href="{{ route('admin.labs.show', $order) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No open laboratory orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $orders->links() }}</div>
@endsection
