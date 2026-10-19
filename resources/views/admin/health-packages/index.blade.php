@extends('layouts.admin')
@section('title', 'Health packages')
@section('heading', 'Health packages')
@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex w-full max-w-md gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search packages" class="w-full rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
            <button type="submit" class="rounded-full bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white">Search</button>
        </form>
        <a href="{{ route('admin.health-packages.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Add package</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Package</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Items</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($packages as $package)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $package->title }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ number_format((float) $package->price) }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $package->health_package_items_count }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$package->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('admin.health-packages.edit', $package) }}" class="font-semibold text-teal-700">Edit</a>
                                <x-admin.delete-button :action="route('admin.health-packages.destroy', $package)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-ink-600">No packages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $packages->links() }}</div>
@endsection
