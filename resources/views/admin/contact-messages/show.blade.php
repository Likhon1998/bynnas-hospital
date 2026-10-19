@extends('layouts.admin')
@section('title', 'Message')
@section('heading', 'Message')
@section('content')
    <article class="max-w-3xl space-y-4 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
        <p class="font-display text-2xl text-navy-900">{{ $message->subject ?: 'Website enquiry' }}</p>
        <p class="text-sm text-ink-600">{{ $message->name }} · {{ $message->email }} · {{ $message->phone }}</p>
        <p class="whitespace-pre-wrap text-navy-900">{{ $message->message }}</p>
        <div class="flex flex-wrap gap-3 pt-4">
            <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}">
                @csrf
                @method('PUT')
                <button type="submit" class="rounded-full bg-navy-900 px-5 py-2.5 text-sm font-semibold text-white">{{ $message->is_read ? 'Mark unread' : 'Mark read' }}</button>
            </form>
            <x-admin.delete-button :action="route('admin.contact-messages.destroy', $message)" class="rounded-full px-5 py-2.5" />
            <a href="{{ route('admin.contact-messages.index') }}" class="rounded-full px-5 py-2.5 text-sm font-semibold text-ink-600">Back</a>
        </div>
    </article>
@endsection
