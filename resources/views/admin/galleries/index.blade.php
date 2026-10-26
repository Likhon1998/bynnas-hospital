@extends('layouts.admin')
@section('title', 'Gallery')
@section('heading', 'Gallery')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $items->total() }} {{ \Illuminate\Support\Str::plural('image', $items->total()) }}</p>
            <a href="{{ route('admin.galleries.create') }}" class="admin-action">Add image</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th class="col-thumb">Image</th>
                        <th>Item</th>
                        <th>Album</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td class="col-idx">{{ ($items->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="col-thumb">
                                @if (filled($item->image))
                                    <img src="{{ $item->image }}" alt="{{ $item->title }}" referrerpolicy="no-referrer">
                                @endif
                            </td>
                            <td>
                                <span class="col-primary">{{ $item->title }}</span>
                                <span class="cell-sub">{{ filled($item->caption) ? str($item->caption)->limit(72) : 'Gallery image' }}</span>
                            </td>
                            <td>{{ $item->album ?: 'Uncategorized' }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$item->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.galleries.edit', $item) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.galleries.destroy', $item)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No gallery images yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $items->links() }}</div>
@endsection
