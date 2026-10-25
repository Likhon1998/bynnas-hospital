@extends('layouts.admin')
@section('title', 'Doctors')
@section('heading', 'Doctors')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $doctors->total() }} {{ \Illuminate\Support\Str::plural('doctor', $doctors->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search doctors">
                <button type="submit">Search</button>
                <a href="{{ route('admin.doctors.create') }}" class="admin-action">Add doctor</a>
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th class="col-num">Fee</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $doctor)
                        <tr>
                            <td class="col-idx">{{ ($doctors->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $doctor->name }}</span>
                                <span class="cell-sub">{{ $doctor->designation ?: 'Consultant' }}@if ($doctor->qualifications) · {{ $doctor->qualifications }}@endif</span>
                            </td>
                            <td>
                                <span>{{ $doctor->department?->name ?: '—' }}</span>
                                <span class="cell-sub">{{ $doctor->specialization ?: '—' }}</span>
                            </td>
                            <td class="col-num">{{ number_format((float) $doctor->consultation_fee, 2) }}</td>
                            <td class="col-status"><x-admin.status-pill :value="$doctor->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-action">
                                <div class="table-actions">
                                    <a href="{{ route('admin.doctors.edit', $doctor) }}">Edit</a>
                                    <x-admin.delete-button :action="route('admin.doctors.destroy', $doctor)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="6">No doctors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $doctors->links() }}</div>
@endsection
