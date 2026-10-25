@extends('layouts.admin')
@section('title', 'Messages')
@section('heading', 'Contact messages')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $messages->total() }} {{ \Illuminate\Support\Str::plural('message', $messages->total()) }}</p>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>From</th>
                        <th>Subject</th>
                        <th class="col-when">Received</th>
                        <th class="col-status">Status</th>
                        <th class="col-open">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr>
                            <td class="col-idx">{{ ($messages->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $message->name }}</span>
                                <span class="cell-sub">{{ $message->email ?: '—' }}@if ($message->phone) · {{ $message->phone }}@endif</span>
                            </td>
                            <td>{{ $message->subject ?: 'Website enquiry' }}</td>
                            <td class="col-when">
                                <span>{{ $message->created_at?->format('d M Y') }}</span>
                                <span class="cell-sub">{{ $message->created_at?->format('H:i') }}</span>
                            </td>
                            <td class="col-status"><x-admin.status-pill :value="$message->is_read ? 'completed' : 'pending'" /></td>
                            <td class="col-open">
                                <a href="{{ route('admin.contact-messages.show', $message) }}">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No messages yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $messages->links() }}</div>
@endsection
