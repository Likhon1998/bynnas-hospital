@extends('layouts.admin')
@section('title', 'Admit patient')
@section('heading', 'Admit patient')
@section('content')
    <x-admin.form-page
        :action="route('admin.admissions.store')"
        method="POST"
        :cancel="route('admin.admissions.index')"
        submit="Admit to bed"
        lede="Create one admission record from an appointment, emergency visit, or a direct floor admission."
    >
        <input type="hidden" name="source" value="{{ old('source', $source) }}">
        @if ($appointment)
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        @endif
        @if ($visit)
            <input type="hidden" name="visit_id" value="{{ $visit->id }}">
        @endif

        <x-admin.form-section title="Admission" note="Reason, attending doctor, ward, and bed stay on this record.">
            <x-form.select name="patient_id" label="Patient" required>
                <option value="">Select patient</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->id }}" @selected((string) old('patient_id', $selectedPatientId) === (string) $patient->id)>
                        {{ $patient->full_name }} · {{ $patient->phone }}
                    </option>
                @endforeach
            </x-form.select>
            <x-form.select name="attending_doctor_id" label="Attending doctor">
                <option value="">Assign later</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" @selected((string) old('attending_doctor_id', $selectedDoctorId) === (string) $doctor->id)>
                        {{ $doctor->name }} · {{ $doctor->department?->name ?: 'No department' }}
                    </option>
                @endforeach
            </x-form.select>
            <x-form.select name="bed_id" label="Ward and bed" required>
                <option value="">Select an available bed</option>
                @foreach ($beds as $bed)
                    <option value="{{ $bed->id }}">{{ $bed->ward?->name }} · {{ $bed->bed_code }}</option>
                @endforeach
            </x-form.select>
            <div class="form-span-2">
                <x-form.input name="admission_reason" label="Admission reason" :value="old('admission_reason', $reason)" required />
            </div>
            <div class="form-span-2 text-sm text-ink-600">
                Source: {{ \App\Models\Admission::SOURCES[$source] ?? ucfirst($source) }}
                @if ($appointment)
                    · {{ $appointment->appointment_no }}
                @endif
                @if ($visit)
                    · {{ $visit->visit_no }}
                @endif
            </div>
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
