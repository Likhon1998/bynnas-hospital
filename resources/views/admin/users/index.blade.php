@extends('layouts.admin')
@section('title', 'Staff')
@section('heading', 'Staff')
@section('content')
    <p class="form-lede">Manage staff logins, change roles, and deactivate accounts that should no longer access the console.</p>

    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $users->total() }} login {{ \Illuminate\Support\Str::plural('account', $users->total()) }}</p>
            <div class="admin-filters">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ str_replace('_', ' ', $role->name) }}</option>
                    @endforeach
                </select>
                @can('doctors.manage')
                    <a href="{{ route('admin.doctors.index') }}" class="admin-action">Doctors</a>
                @endcan
                <a href="{{ route('admin.roles.index') }}" class="admin-action">Roles</a>
                <a href="{{ route('admin.users.create') }}" class="admin-action">Create staff</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Staff</th>
                        <th>Roles</th>
                        <th class="col-status">Status</th>
                        <th class="col-pair-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $isSelf = auth()->id() === $user->id;
                        @endphp
                        <tr>
                            <td class="col-idx">{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $user->name }}</span>
                                <span class="cell-sub">{{ $user->email }}</span>
                            </td>
                            <td>{{ implode(', ', array_map(fn ($role) => str_replace('_', ' ', $role), $user->assigned_roles ?: [])) ?: '—' }}</td>
                            <td class="col-status">
                                @if ($user->is_active)
                                    <x-admin.status-pill value="active" />
                                @else
                                    <x-admin.status-pill value="inactive" />
                                @endif
                            </td>
                            <td class="col-pair-actions">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                    @unless ($isSelf)
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf
                                            <button type="submit">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <x-admin.delete-button :action="route('admin.users.destroy', $user)" />
                                    @endunless
                                </x-admin.row-menu>
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
