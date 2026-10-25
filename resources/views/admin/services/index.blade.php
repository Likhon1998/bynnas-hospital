@extends('layouts.admin')
@section('title', 'Services')
@section('heading', 'Services')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $services->total() }} {{ \Illuminate\Support\Str::plural('service', $services->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search services">
                <button type="submit">Search</button>
                <a href="{{ route('admin.services.create') }}" class="admin-action">Add service</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Service</th>
                        <th>Department</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td class="col-idx">{{ ($services->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $service->title }}</span>
                                <span class="cell-sub">{{ $service->is_featured ? 'Featured' : 'Service' }}@if (filled($service->summary)) · {{ str($service->summary)->limit(72) }}@endif</span>
                            </td>
                            <td>{{ $service->department?->name ?: '—' }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$service->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.services.edit', $service) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.services.destroy', $service)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No services yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $services->links() }}</div>
@endsection
