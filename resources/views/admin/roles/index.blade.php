@extends('layouts.admin')
@section('title', 'Roles & permissions')
@section('heading', 'Roles & permissions')
@section('content')
    <p class="form-lede">Create roles and choose which permissions each role receives. Then assign roles to staff under Users.</p>

    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $roles->count() }} {{ \Illuminate\Support\Str::plural('role', $roles->count()) }}</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="admin-action">Staff</a>
                <a href="{{ route('admin.roles.create') }}" class="admin-action">Create role</a>
            </div>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Staff</th>
                        <th class="col-pair-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="col-idx">{{ $loop->iteration }}</td>
                            <td>
                                <span class="col-primary">{{ $role->displayName() }}</span>
                                <span class="cell-sub">{{ $role->name }}</span>
                            </td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>
                                @if ($role->users_count > 0)
                                    <a href="{{ route('admin.users.index', ['role' => $role->name]) }}">{{ $role->users_count }}</a>
                                @else
                                    0
                                @endif
                            </td>
                            <td class="col-pair-actions">
                                <div class="table-actions">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="table-btn table-btn--edit">Edit</a>
                                    @unless ($role->isProtected())
                                        @if ($role->users_count === 0)
                                            <x-admin.delete-button :action="route('admin.roles.destroy', $role)" label="Delete" variant="pill" />
                                        @endif
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
