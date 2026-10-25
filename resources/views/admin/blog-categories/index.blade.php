@extends('layouts.admin')
@section('title', 'Blog categories')
@section('heading', 'Blog categories')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $categories->total() }} {{ \Illuminate\Support\Str::plural('category', $categories->total()) }}</p>
            <a href="{{ route('admin.blog-categories.create') }}" class="admin-action">Add category</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Category</th>
                        <th class="col-count">Posts</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="col-idx">{{ ($categories->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="col-primary">{{ $category->name }}</td>
                            <td class="col-count">{{ $category->blog_posts_count }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$category->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.blog-categories.edit', $category) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.blog-categories.destroy', $category)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $categories->links() }}</div>
@endsection
