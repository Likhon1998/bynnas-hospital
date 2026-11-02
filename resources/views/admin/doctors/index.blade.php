@extends('layouts.admin')
@section('title', 'Doctors')
@section('heading', 'Doctors')
@section('content')
    <section class="mc-card mc-card--board roster">
        <div class="mc-card__head mc-card__head--table">
            <div>
                <h2>Clinical roster</h2>
                <p>{{ $doctors->total() }} {{ \Illuminate\Support\Str::plural('doctor', $doctors->total()) }} · profiles used by reception and the website</p>
            </div>
            <form method="GET" action="{{ route('admin.doctors.index') }}" class="roster__toolbar">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or specialty" class="mc-input roster__search" aria-label="Search doctors">
                <button type="submit" class="mc-btn mc-btn--ghost">Search</button>
                <a href="{{ route('admin.departments.index') }}" class="mc-btn mc-btn--ghost">Departments</a>
                <a href="{{ route('admin.doctors.create') }}" class="mc-btn mc-btn--primary">Add doctor</a>
            </form>
        </div>

        <div class="roster__scroll">
            <table class="roster__table">
                <thead>
                    <tr>
                        <th scope="col">Doctor</th>
                        <th scope="col">Department</th>
                        <th scope="col" class="is-num">Fee</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="is-action">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $doctor)
                        @php
                            $initials = collect(preg_split('/\s+/', trim(str_replace(['Dr.', 'Dr'], '', $doctor->name))))
                                ->filter()
                                ->map(fn (string $word): string => strtoupper(substr($word, 0, 1)))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <tr>
                            <td>
                                <div class="roster__person">
                                    @if ($doctor->photo)
                                        <img src="{{ $doctor->photo }}" alt="" class="roster__avatar">
                                    @else
                                        <span class="roster__avatar roster__avatar--fallback" aria-hidden="true">{{ $initials ?: 'DR' }}</span>
                                    @endif
                                    <div>
                                        <span class="roster__name">{{ $doctor->name }}</span>
                                        <span class="roster__meta">{{ $doctor->designation ?: 'Consultant' }}@if ($doctor->qualifications) · {{ $doctor->qualifications }}@endif</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="roster__name">{{ $doctor->department?->name ?: '—' }}</span>
                                <span class="roster__meta">{{ $doctor->specialization ?: 'No specialty listed' }}</span>
                            </td>
                            <td class="is-num">{{ number_format((float) $doctor->consultation_fee, 2) }}</td>
                            <td>
                                <span @class(['roster__status', 'is-on' => $doctor->is_active, 'is-off' => ! $doctor->is_active])>
                                    {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="is-action">
                                <div class="roster__actions">
                                    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="roster__btn">Edit</a>
                                    @can('appointments.view')
                                        <a href="{{ route('admin.appointments.index', ['doctor' => $doctor->id]) }}" class="roster__btn">Appointments</a>
                                    @endcan
                                    <x-admin.delete-button :action="route('admin.doctors.destroy', $doctor)" class="roster__btn roster__btn--danger" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty">
                            <td colspan="5">No doctors match this search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($doctors->hasPages())
            <div class="roster__pager">{{ $doctors->links() }}</div>
        @endif
    </section>
@endsection
