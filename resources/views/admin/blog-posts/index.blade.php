@extends('layouts.admin')
@section('title', 'Blog posts')
@section('heading', 'Health articles')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles">
                <button type="submit">Search</button>
                <a href="{{ route('admin.blog-posts.create') }}" class="admin-action">Write article</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Article</th>
                        <th>Category</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td class="col-idx">{{ ($posts->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $post->title }}</span>
                                <span class="cell-sub">
                                    @if ($post->published_at)
                                        {{ $post->published_at->format('d M Y') }}
                                    @else
                                        Draft
                                    @endif
                                    @if ($post->author)
                                        · {{ $post->author->name }}
                                    @endif
                                    @if (filled($post->excerpt))
                                        · {{ str($post->excerpt)->limit(64) }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $post->blogCategory?->name ?: '—' }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$post->is_published ? 'published' : 'pending'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.blog-posts.edit', $post) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.blog-posts.destroy', $post)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No articles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $posts->links() }}</div>
@endsection
