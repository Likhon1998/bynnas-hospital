@props(['appointment'])

@php
    $options = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No show',
    ];
@endphp

<form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="inline-flex">
    @csrf
    @method('PUT')
    <input type="hidden" name="cancellation_reason" value="Updated from the appointment list">
    <select
        name="status"
        onchange="this.form.submit()"
        aria-label="Change appointment status"
        class="status-select status-select--{{ $appointment->status }}"
    >
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected($appointment->status === $value)>{{ $label }}</option>
        @endforeach
    </select>
</form>
