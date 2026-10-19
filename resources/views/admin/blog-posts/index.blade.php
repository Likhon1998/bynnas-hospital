@extends('layouts.admin')
@section('title', 'Blog posts')
@section('heading', 'Health articles')
@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex w-full max-w-md gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles" class="w-full rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
            <button type="submit" class="rounded-full bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white">Search</button>
        </form>
        <a href="{{ route('admin.blog-posts.create') }}" class="inline-flex rounded-full bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Write article</a>
    </div>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($posts as $post)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $post->title }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $post->blogCategory?->name }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$post->is_published ? 'published' : 'pending'" /></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('admin.blog-posts.edit', $post) }}" class="font-semibold text-teal-700">Edit</a>
                                <x-admin.delete-button :action="route('admin.blog-posts.destroy', $post)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No articles yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
@endsection
