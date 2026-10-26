@extends('layouts.admin')
@section('title', 'Departments')
@section('heading', 'Departments')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $departments->total() }} {{ \Illuminate\Support\Str::plural('department', $departments->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search departments">
                <button type="submit">Search</button>
                <a href="{{ route('admin.departments.create') }}" class="admin-action">Add department</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Department</th>
                        <th class="col-count">Doctors</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td class="col-idx">{{ ($departments->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $department->name }}</span>
                                <span class="cell-sub">{{ $department->is_featured ? 'Featured' : 'Department' }}@if (filled($department->description)) · {{ str($department->description)->limit(72) }}@endif</span>
                            </td>
                            <td class="col-count">{{ $department->doctors_count }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$department->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.departments.edit', $department) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.departments.destroy', $department)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No departments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $departments->links() }}</div>
@endsection
