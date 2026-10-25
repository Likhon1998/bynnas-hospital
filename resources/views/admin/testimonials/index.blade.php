@extends('layouts.admin')
@section('title', 'Testimonials')
@section('heading', 'Testimonials')
@section('content')
    <div class="table-frame table-frame--fill">
        <div class="table-toolbar">
            <p class="table-count">{{ $testimonials->total() }} {{ \Illuminate\Support\Str::plural('testimonial', $testimonials->total()) }}</p>
            <a href="{{ route('admin.testimonials.create') }}" class="admin-action">Add testimonial</a>
        </div>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Person</th>
                        <th>Quote</th>
                        <th class="col-count">Rating</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr>
                            <td class="col-idx">{{ ($testimonials->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $testimonial->display_name }}</span>
                                <span class="cell-sub">{{ $testimonial->display_role ?: 'Patient' }}</span>
                            </td>
                            <td>{{ filled($testimonial->quote) ? str($testimonial->quote)->limit(90) : '—' }}</td>
                            <td class="col-count">{{ $testimonial->rating }}/5</td>
                            <td class="col-status"><x-admin.status-pill :value="$testimonial->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.testimonials.destroy', $testimonial)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No testimonials yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $testimonials->links() }}</div>
@endsection
