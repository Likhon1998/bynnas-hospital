@extends('layouts.admin')
@section('title', 'Add patient')
@section('heading', 'Add patient')
@section('content')
    <x-admin.form-page
        :action="route('admin.patients.store')"
        method="POST"
        :cancel="$returnTo === 'beds' ? route('admin.beds.index', ['status' => 'available']) : route('admin.patients.index')"
        submit="Register patient"
        lede="Register a patient so reception can book visits or admit them to an available bed."
    >
        @if ($returnTo === 'beds')
            <input type="hidden" name="return_to" value="beds">
        @endif

        <x-admin.form-section title="Patient" note="Primary identity used at reception.">
            <x-form.input name="full_name" label="Full name" :value="old('full_name')" required />
            <x-form.input name="phone" label="Phone" :value="old('phone')" required />
            <x-form.input name="email" label="Email" type="email" :value="old('email')" />
            <x-form.input name="date_of_birth" label="Date of birth" type="date" :value="old('date_of_birth')" />
            <x-form.select name="gender" label="Gender">
                <option value="">Select</option>
                @foreach (['male', 'female', 'other'] as $gender)
                    <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ ucfirst($gender) }}</option>
                @endforeach
            </x-form.select>
            <div class="form-span-2">
                <x-form.textarea name="address" label="Address" :value="old('address')" />
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="Emergency contact" note="Who to call if the patient cannot be reached.">
            <x-form.input name="emergency_contact_name" label="Contact name" :value="old('emergency_contact_name')" />
            <x-form.input name="emergency_contact_phone" label="Contact phone" :value="old('emergency_contact_phone')" />
        </x-admin.form-section>

        <x-admin.form-section title="Record status" :columns="false">
            <x-form.checkbox name="is_active" label="Active patient record" :checked="old('is_active', true)" />
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
