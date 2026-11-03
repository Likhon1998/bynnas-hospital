@extends('layouts.admin')
@section('title', 'Board members')
@section('heading', 'Board members')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $boardMembers->total() }} {{ \Illuminate\Support\Str::plural('member', $boardMembers->total()) }}</p>
            <a href="{{ route('admin.board-members.create') }}" class="admin-action">Add board member</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Person</th>
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
                                <span class="cell-sub">{{ $member->role_label ?: 'Board' }}</span>
                            </td>
                            <td>{{ $member->title }}</td>
                            <td class="col-count">{{ $member->sort_order }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$member->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.board-members.edit', $member) }}" class="table-btn table-btn--edit">Edit</a>
                                    <x-admin.delete-button :action="route('admin.board-members.destroy', $member)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No board members yet. Add the chairman and directors to show them on the About page.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $boardMembers->links() }}</div>
@endsection
