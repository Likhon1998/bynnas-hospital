@extends('layouts.admin')
@section('title', 'Banners')
@section('heading', 'Banners')
@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.banners.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Add banner</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Placement</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($banners as $banner)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $banner->title }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $banner->placement }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$banner->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="font-semibold text-teal-700">Edit</a>
                                <x-admin.delete-button :action="route('admin.banners.destroy', $banner)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No banners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $banners->links() }}</div>
@endsection
