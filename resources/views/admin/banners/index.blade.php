@extends('layouts.admin')
@section('title', 'Banners')
@section('heading', 'Banners')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $banners->total() }} {{ \Illuminate\Support\Str::plural('banner', $banners->total()) }}</p>
            <a href="{{ route('admin.banners.create') }}" class="admin-action">Add banner</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Banner</th>
                        <th>Placement</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($banners as $banner)
                        <tr>
                            <td class="col-idx">{{ ($banners->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $banner->title }}</span>
                                <span class="cell-sub">{{ $banner->subtitle ?: ($banner->cta_label ?: 'Banner') }}</span>
                            </td>
                            <td>
                                <span>{{ ucfirst(str_replace('_', ' ', (string) $banner->placement)) }}</span>
                                <span class="cell-sub">
                                    @if ($banner->starts_at || $banner->ends_at)
                                        {{ $banner->starts_at?->format('d M Y') ?: 'Open' }}–{{ $banner->ends_at?->format('d M Y') ?: 'Open' }}
                                    @else
                                        Always on
                                    @endif
                                </span>
                            </td>
                            <td class="col-status"><x-admin.status-pill :value="$banner->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.banners.edit', $banner) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.banners.destroy', $banner)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No banners yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $banners->links() }}</div>
@endsection
