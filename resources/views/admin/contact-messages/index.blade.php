@extends('layouts.admin')
@section('title', 'Messages')
@section('heading', 'Contact messages')
@section('content')
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">From</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($messages as $message)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $message->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $message->subject ?: 'Website enquiry' }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$message->is_read ? 'completed' : 'pending'" /></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="font-semibold text-teal-700">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No messages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
