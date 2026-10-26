@extends('layouts.admin')
@section('title', 'Gallery')
@section('heading', 'Gallery')
@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.galleries.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Add image</a>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($items as $item)
            <article class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
                <img src="{{ $item->image }}" alt="{{ $item->title }}" class="h-40 w-full object-cover">
                <div class="p-4">
                    <p class="font-medium text-navy-900">{{ $item->title }}</p>
                    <p class="text-sm text-ink-600">{{ $item->album ?: 'Uncategorized' }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <a href="{{ route('admin.galleries.edit', $item) }}" class="text-sm font-semibold text-teal-700">Edit</a>
                        <x-admin.delete-button :action="route('admin.galleries.destroy', $item)" />
                    </div>
                </div>
            </article>
        @empty
            <p class="text-ink-600">No gallery images yet.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $items->links() }}</div>
@endsection
