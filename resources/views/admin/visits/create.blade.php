@extends('layouts.admin')
@section('title', 'Check in walk-in')
@section('heading', 'Check in walk-in')
@section('content')
    <x-admin.form-page
        :action="route('admin.visits.store')"
        method="POST"
        :cancel="route('admin.visits.index')"
        submit="Check in"
        lede="Use this when a patient arrives without a booked slot. Booked appointments should be checked in from the appointment page."
    >
        <x-admin.form-section title="Arrival" note="The patient enters the waiting queue immediately.">
            <x-form.select name="patient_id" label="Patient" required>
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $patient->id)>
                        {{ $patient->full_name }} · {{ $patient->phone }}
                    </option>
                @endforeach
            </x-form.select>
            <x-form.select name="doctor_id" label="Doctor">
                <option value="">Assign later</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>
                        {{ $doctor->name }} · {{ $doctor->department?->name ?: 'No department' }}
                    </option>
                @endforeach
            </x-form.select>
            <div class="form-span-2">
                <x-form.input name="chief_complaint" label="Chief complaint" :value="old('chief_complaint')" />
            </div>
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
