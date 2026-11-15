@extends('layouts.admin')
@section('title', "Doctor's Talk")
@section('heading', "Doctor's Talk")
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $talks->total() }} {{ \Illuminate\Support\Str::plural('video', $talks->total()) }}</p>
            <a href="{{ route('admin.doctor-talks.create') }}" class="admin-action">Add video</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th class="col-thumb">Thumbnail</th>
                        <th>Talk</th>
                        <th class="col-count">Order</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($talks as $talk)
                        <tr>
                            <td class="col-idx">{{ ($talks->firstItem() ?? 1) + $loop->index }}</td>
                            <td class="col-thumb">
                                @if (filled($talk->displayThumbnail()))
                                    <img src="{{ $talk->displayThumbnail() }}" alt="{{ $talk->title }}" referrerpolicy="no-referrer">
                                @endif
                            </td>
                            <td>
                                <span class="col-primary">{{ $talk->title }}</span>
                                <span class="cell-sub">
                                    {{ filled($talk->doctor_name) ? $talk->doctor_name.' · ' : '' }}{{ $talk->youtube_url }}
                                </span>
                            </td>
                            <td class="col-count">{{ $talk->sort_order }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$talk->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <x-admin.row-menu>
                                    <a href="{{ route('admin.doctor-talks.edit', $talk) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.doctor-talks.destroy', $talk)" />
                                </x-admin.row-menu>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No Doctor's Talk videos yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $talks->links() }}</div>
@endsection
