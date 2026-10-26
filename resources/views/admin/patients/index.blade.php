@extends('layouts.admin')
@section('title', 'Patients')
@section('heading', 'Patients')
@section('content')
    <div class="table-frame table-frame--fill">
        <form method="GET" class="table-toolbar">
            <p class="table-count">{{ $patients->total() }} {{ \Illuminate\Support\Str::plural('patient', $patients->total()) }}</p>
            <div class="admin-filters">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search patients">
                <button type="submit">Search</button>
                @can('patients.manage')
                    <a href="{{ route('admin.patients.create') }}" class="admin-action">Add patient</a>
                @endcan
            </div>
        </form>
        <div class="table-scroll admin-scroll">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th class="col-idx">#</th>
                        <th>Patient</th>
                        <th>Contact</th>
                        <th class="col-status">Status</th>
                        <th class="col-open">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="col-idx">{{ ($patients->firstItem() ?? 1) + $loop->index }}</td>
                            <td>
                                <span class="col-primary">{{ $patient->full_name }}</span>
                                <span class="cell-sub">{{ $patient->gender ? ucfirst($patient->gender) : 'Patient' }}@if ($patient->date_of_birth) · {{ $patient->date_of_birth->format('d M Y') }}@endif</span>
                            </td>
                            <td>
                                <span>{{ $patient->phone }}</span>
                                <span class="cell-sub">{{ $patient->email ?: '—' }}</span>
                            </td>
                            <td class="col-status"><x-admin.status-pill :value="$patient->is_active ? 'active' : 'inactive'" /></td>
                            <td class="col-open">
                                <a href="{{ route('admin.patients.show', $patient) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty"><td colspan="5">No patients yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-pager">{{ $patients->links() }}</div>
@endsection
