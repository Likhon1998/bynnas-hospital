@extends('layouts.admin')
@section('title', 'Health packages')
@section('heading', 'Health packages')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $packages->total() }} {{ \Illuminate\Support\Str::plural('package', $packages->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search packages">
                <button type="submit">Search</button>
                <a href="{{ route('admin.health-packages.create') }}" class="admin-action">Add package</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Package</th>
                        <th class="col-num">Price</th>
                        <th class="col-count">Items</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td class="col-idx">{{ ($packages->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $package->title }}</span>
                                <span class="cell-sub">
                                    {{ $package->is_featured ? 'Featured' : 'Package' }}
                                    @if (filled($package->duration_label))
                                        · {{ $package->duration_label }}
                                    @endif
                                </span>
                            </td>
                            <td class="col-num">{{ number_format((float) $package->price, 2) }}</td>
                            <td class="col-count">{{ $package->health_package_items_count }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$package->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.health-packages.edit', $package) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.health-packages.destroy', $package)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No packages yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $packages->links() }}</div>
@endsection
