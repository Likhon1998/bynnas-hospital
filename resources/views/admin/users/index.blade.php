@extends('layouts.admin')
@section('title', 'Staff')
@section('heading', 'Staff')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $users->total() }} login {{ \Illuminate\Support\Str::plural('account', $users->total()) }}</p>
            <div class="flex items-center gap-2">
                @can('doctors.manage')
                    <a href="{{ route('admin.doctors.index') }}" class="admin-action">Doctors</a>
                @endcan
                <a href="{{ route('admin.roles.index') }}" class="admin-action">Roles</a>
                <a href="{{ route('admin.users.create') }}" class="admin-action">Create staff</a>
            </div>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Staff</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th class="col-open">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="col-idx">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $user->name }}</span>
                                <span class="cell-sub">{{ $user->email }}</span>
                            </td>
                            <td>{{ implode(', ', $user->assigned_roles ?: []) ?: '—' }}</td>
                            <td>
                                @if ($user->is_active)
                                    <x-admin.status-pill value="active" />
                                @else
                                    <x-admin.status-pill value="inactive" />
                                @endif
                            </td>
                            <td class="col-open">
                                <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $users->links() }}</div>
@endsection
