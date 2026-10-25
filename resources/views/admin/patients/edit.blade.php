@extends('layouts.admin')
@section('title', 'Edit patient')
@section('heading', 'Edit patient')
@section('content')
    <x-admin.form-page
        :action="route('admin.patients.update', $patient)"
        method="PUT"
        :cancel="route('admin.patients.show', $patient)"
        lede="Keep the patient record accurate so appointments, billing, and emergency contact stay connected."
    >
        <x-admin.form-section title="Patient" note="Primary identity used at reception.">
            <x-form.input name="full_name" label="Full name" :value="$patient->full_name" required />
            <x-form.input name="phone" label="Phone" :value="$patient->phone" required />
            <x-form.input name="email" label="Email" type="email" :value="$patient->email" />
            <x-form.input name="date_of_birth" label="Date of birth" type="date" :value="optional($patient->date_of_birth)->toDateString()" />
            <x-form.select name="gender" label="Gender">
                <option value="">Select</option>
                @foreach (['male', 'female', 'other'] as $gender)
                    <option value="{{ $gender }}" @selected(old('gender', $patient->gender) === $gender)>{{ ucfirst($gender) }}</option>
                @endforeach
            </x-form.select>
            <div class="form-span-2">
                <x-form.textarea name="address" label="Address" :value="$patient->address" />
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="Emergency contact" note="Who to call if the patient cannot be reached.">
            <x-form.input name="emergency_contact_name" label="Contact name" :value="$patient->emergency_contact_name" />
            <x-form.input name="emergency_contact_phone" label="Contact phone" :value="$patient->emergency_contact_phone" />
        </x-admin.form-section>

        <x-admin.form-section title="Record status" :columns="false">
            <x-form.checkbox name="is_active" label="Active patient record" :checked="$patient->is_active" />
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
