@extends('layouts.admin')
@section('title', 'Users')
@section('heading', 'Staff users')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $users->total() }} {{ \Illuminate\Support\Str::plural('user', $users->total()) }}</p>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Staff</th>
                        <th>Roles</th>
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
                            <td class="col-open">
                                <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="4">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $users->links() }}</div>
@endsection
