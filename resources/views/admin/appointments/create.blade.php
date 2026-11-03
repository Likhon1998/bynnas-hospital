@extends('layouts.admin')
@section('title', 'Take appointment')
@section('heading', 'Take appointment')
@section('content')
    <x-admin.form-page
        :action="route('admin.appointments.store')"
        method="POST"
        :cancel="route('admin.appointments.index')"
        submit="Take appointment"
        lede="Book a desk or emergency visit for a registered patient. Off days, vacation, and a slot already held by another patient are blocked. Emergency can use an off day, but not a doctor who is away."
    >
        <x-admin.form-section title="Who is coming" note="Choose the patient and the clinician they will see.">
            <x-form.select name="patient_id" label="Patient" required>
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $patient->id)>
                        {{ $patient->full_name }} · {{ $patient->phone }}
                    </option>
                @endforeach
            </x-form.select>
            <x-form.select name="doctor_id" label="Doctor" required :disabled="$doctorLocked ?? false">
                <option value="">Select doctor</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $selectedDoctorId ?? null) === (string) $doctor->id)>
                        {{ $doctor->name }} · {{ $doctor->department?->name ?: 'No department' }}
                    </option>
                @endforeach
            </x-form.select>
            @if ($doctorLocked ?? false)
                <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
            @endif
        </x-admin.form-section>

        <x-admin.form-section title="When" note="Each visit is 30 minutes. The same doctor cannot be double-booked, booked on an off day, or booked while on leave.">
            <x-form.input name="appointment_date" label="Date" type="date" :value="old('appointment_date', now()->toDateString())" required />
            <x-form.input name="start_time" label="Start time" type="time" :value="old('start_time', '09:00')" required />
            <x-form.select name="status" label="Status" required>
                <option value="pending" @selected(old('status', 'confirmed') === 'pending')>Pending</option>
                <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>Confirmed</option>
            </x-form.select>
            <x-form.select name="source" label="From" required>
                <option value="admin" @selected(old('source', 'admin') === 'admin')>Desk</option>
                <option value="emergency" @selected(old('source') === 'emergency')>Emergency</option>
            </x-form.select>
        </x-admin.form-section>

        <x-admin.form-section title="Visit notes" note="Optional reason for the queue and internal desk notes.">
            <x-form.input name="reason" label="Reason" :value="old('reason')" />
            <div class="form-span-2">
                <x-form.textarea name="notes" label="Internal notes" :value="old('notes')" />
            </div>
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
