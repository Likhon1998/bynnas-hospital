@extends('layouts.admin')
@section('title', 'Leadership')
@section('heading', 'Leadership')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $boardMembers->total() }} {{ \Illuminate\Support\Str::plural('leader', $boardMembers->total()) }}</p>
            <a href="{{ route('admin.board-members.create') }}" class="admin-action">Add leader</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Person</th>
                        <th>Role page</th>
                        <th>Title</th>
                        <th class="col-count">Order</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($boardMembers as $member)
                        <tr>
                            <td class="col-idx">{{ ($boardMembers->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $member->name }}</span>
                                <span class="cell-sub">{{ $member->role_label ?: $member->roleName() }}</span>
                            </td>
                            <td>{{ $member->roleName() }}</td>
                            <td>{{ $member->title }}</td>
                            <td class="col-count">{{ $member->sort_order }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$member->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.board-members.edit', $member) }}">Edit</a>
                                    @if ($member->is_active && filled($member->role))
                                        <a href="{{ route('about.leadership.role', $member->role) }}" target="_blank" rel="noopener">View page</a>
                                    @endif
                                    <x-admin.delete-button :action="route('admin.board-members.destroy', $member)" />
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="7">No leaders yet. Add the founder, co-founder, chairman, and directors — each role gets its own public page.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $boardMembers->links() }}</div>
@endsection
