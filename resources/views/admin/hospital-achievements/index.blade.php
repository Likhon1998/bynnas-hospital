@extends('layouts.admin')
@section('title', 'Achievements')
@section('heading', 'Achievements')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $achievements->total() }} {{ \Illuminate\Support\Str::plural('achievement', $achievements->total()) }}</p>
            <a href="{{ route('admin.hospital-achievements.create') }}" class="admin-action">Add achievement</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Year</th>
                        <th>Title</th>
                        <th class="col-count">Order</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($achievements as $achievement)
                        <tr>
                            <td class="col-idx">{{ ($achievements->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $achievement->year ?: '—' }}</td>
                            <td>
                                <span class="col-primary">{{ $achievement->title }}</span>
                                @if (filled($achievement->description))
                                    <span class="cell-sub">{{ \Illuminate\Support\Str::limit($achievement->description, 80) }}</span>
                                @endif
                            </td>
                            <td class="col-count">{{ $achievement->sort_order }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$achievement->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.hospital-achievements.edit', $achievement) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.hospital-achievements.destroy', $achievement)" />
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No achievements yet. Add campus milestones to show them on About us.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $achievements->links() }}</div>
@endsection
