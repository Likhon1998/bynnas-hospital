@extends('layouts.admin')
@section('title', 'Services')
@section('heading', 'Services')
@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex w-full max-w-md gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search services" class="w-full rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
            <button type="submit" class="rounded-full bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white">Search</button>
        </form>
        <a href="{{ route('admin.services.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Add service</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Service</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($services as $service)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $service->title }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $service->department?->name ?: '—' }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$service->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('admin.services.edit', $service) }}" class="font-semibold text-teal-700">Edit</a>
                                <x-admin.delete-button :action="route('admin.services.destroy', $service)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No services yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $services->links() }}</div>
@endsection
