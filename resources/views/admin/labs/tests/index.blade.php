@extends('layouts.admin')
@section('title', 'Lab catalog')
@section('heading', 'Lab catalog')
@section('content')
    <p class="form-lede">Tests, units, reference ranges, critical limits, and prices.</p>

    <div class="table-frame table-frame--fill mt-4">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $tests->total() }} {{ \Illuminate\Support\Str::plural('test', $tests->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search code or name">
                <button type="submit">Filter</button>
                @can('lab.report')
                    <a href="{{ route('admin.labs.tests.create') }}" class="admin-action">Add test</a>
                @endcan
                <a href="{{ route('admin.labs.index') }}" class="admin-action">Orders</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Category</th>
                        <th>Range</th>
                        <th>Price</th>
                        <th class="col-status">Status</th>
                        @can('lab.report')
                            <th class="col-open">Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tests as $test)
                        <tr>
                            <td>
                                <span class="col-primary">{{ $test->name }}</span>
                                <span class="cell-sub">{{ $test->code }} · {{ $test->specimen }}</span>
                            </td>
                            <td>{{ $test->categoryLabel() }}</td>
                            <td>{{ $test->reference_range ?: '—' }} {{ $test->unit }}</td>
                            <td>{{ number_format((float) $test->price, 2) }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$test->is_active ? 'active' : 'inactive'" /></td>
                            @can('lab.report')
                                <td class="col-open">
                                    <x-admin.row-menu>
                                        <a href="{{ route('admin.labs.tests.edit', $test) }}">Edit</a>
                                    </x-admin.row-menu>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No tests in the catalog.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $tests->links() }}</div>
@endsection
