@extends('layouts.admin')
@section('title', 'Edit doctor')
@section('heading', 'Edit doctor')
@section('content')
    @include('admin.doctors._form', ['action' => route('admin.doctors.update', $doctor), 'method' => 'PUT'])

    <section class="form-card mt-4">
        <header class="form-card__head">
            <h2>Leave and vacation</h2>
            <p>Blocked dates cannot be booked on the website or at the desk. Emergency visits for this doctor are also refused while they are away.</p>
        </header>
        <div class="form-card__body">
            @if ($doctor->doctorLeaves->isEmpty())
                <p class="text-sm text-ink-600">No leave recorded. Clinic days still come from the weekly schedule.</p>
            @else
                <div class="table-frame">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>From</th>
                                <th>Until</th>
                                <th>Reason</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($doctor->doctorLeaves as $leave)
                                <tr>
                                    <td>{{ $leave->typeLabel() }}</td>
                                    <td>{{ $leave->starts_on?->format('d M Y') }}</td>
                                    <td>{{ $leave->ends_on?->format('d M Y') }}</td>
                                    <td>{{ $leave->reason ?: '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.doctors.leaves.destroy', [$doctor, $leave]) }}" onsubmit="return confirm('Open these dates for booking again?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="form-btn form-btn--ghost">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.doctors.leaves.store', $doctor) }}" class="form-grid mt-4">
                @csrf
                <x-form.input name="starts_on" label="Starts on" type="date" :value="old('starts_on')" required />
                <x-form.input name="ends_on" label="Ends on" type="date" :value="old('ends_on')" required />
                <x-form.select name="type" label="Type" required>
                    @foreach (\App\Models\DoctorLeave::TYPES as $type => $label)
                        <option value="{{ $type }}" @selected(old('type', 'vacation') === $type)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input name="reason" label="Reason" :value="old('reason')" />
                <div class="form-span-2">
                    <button type="submit" class="form-btn form-btn--navy">Block these dates</button>
                </div>
            </form>
        </div>
    </section>
@endsection
