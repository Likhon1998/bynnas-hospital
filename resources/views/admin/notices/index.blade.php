@extends('layouts.admin')
@section('title', 'Notices')
@section('heading', 'Notices')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $notices->total() }} {{ \Illuminate\Support\Str::plural('notice', $notices->total()) }}</p>
            <a href="{{ route('admin.notices.create') }}" class="admin-action">Add notice</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Notice</th>
                        <th>Date</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notices as $notice)
                        <tr>
                            <td class="col-idx">{{ ($notices->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $notice->title }}</span>
                                @if ($notice->is_urgent)
                                    <span class="table-meta">Urgent</span>
                                @endif
                                @if (filled($notice->body))
                                    <p class="table-meta">{{ str($notice->body)->limit(90) }}</p>
                                @endif
                            </td>
                            <td>{{ $notice->published_on?->format('d M Y') ?: '—' }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$notice->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.notices.edit', $notice) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.notices.destroy', $notice)" />
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No notices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $notices->links() }}</div>
@endsection
