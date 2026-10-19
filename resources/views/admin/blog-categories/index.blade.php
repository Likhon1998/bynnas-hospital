@extends('layouts.admin')
@section('title', 'Blog categories')
@section('heading', 'Blog categories')
@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.blog-categories.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Add category</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Posts</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $category->blog_posts_count }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$category->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('admin.blog-categories.edit', $category) }}" class="font-semibold text-teal-700">Edit</a>
                                <x-admin.delete-button :action="route('admin.blog-categories.destroy', $category)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $categories->links() }}</div>
@endsection
