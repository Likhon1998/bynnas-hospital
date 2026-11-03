@extends('layouts.admin')
@section('title', 'Audit logs')
@section('heading', 'Audit logs')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $logs->total() }} system {{ \Illuminate\Support\Str::plural('event', $logs->total()) }}</p>
            @if (auth()->user()?->hasRole('super_admin'))
                <a href="{{ route('admin.system.backup.index') }}" class="admin-action">Database backup</a>
            @endif
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>When</th>
                        <th>Actor</th>
                        <th>Action</th>
                        <th>Summary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="col-idx">{{ ($logs->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $log->created_at?->format('j M Y H:i') }}</td>
                            <td>{{ $log->user?->name ?: 'System' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>
                                <span class="col-primary">{{ $log->summary }}</span>
                                @if ($log->ip_address)
                                    <span class="cell-sub">{{ $log->ip_address }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No audit events yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $logs->links() }}</div>
@endsection
