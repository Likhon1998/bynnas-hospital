@extends('layouts.admin')
@section('title', 'Add patient')
@section('heading', 'Add patient')
@section('content')
    <div class="patient-register">
        <section class="patient-register__hero">
            <div class="patient-register__intro">
                <p class="patient-register__eyebrow">Patient registration</p>
                <h2>Create a complete care record</h2>
                <p>
                    @if ($returnTo === 'beds')
                        Register the patient first, then return to the bed board to admit them to an available bed.
                    @else
                        Capture identity, profile, and emergency details so reception, nursing, and billing share one record.
                    @endif
                </p>
            </div>

            <ol class="patient-register__steps" aria-label="Registration steps">
                <li class="is-teal"><span>1</span>Identity</li>
                <li class="is-blue"><span>2</span>Profile</li>
                <li class="is-amber"><span>3</span>Emergency</li>
                <li class="is-green"><span>4</span>Status</li>
            </ol>

            @if ($returnTo === 'beds')
                <div class="patient-register__callout">
                    <strong>Bed admission path</strong>
                    <span>After saving, you will return to available beds to finish allocation.</span>
                </div>
            @endif
        </section>

        <x-admin.form-page
            :action="route('admin.patients.store')"
            method="POST"
            :cancel="$returnTo === 'beds' ? route('admin.beds.index', ['status' => 'available']) : route('admin.patients.index')"
            submit="Register patient"
            class="patient-register__form"
        >
            @if ($returnTo === 'beds')
                <input type="hidden" name="return_to" value="beds">
            @endif

            <x-admin.form-section
                title="Identity"
                note="Required details used at reception and on the bed board."
                tone="teal"
                step="1"
            >
                <x-form.input name="full_name" label="Full name" :value="old('full_name')" required />
                <x-form.input name="phone" label="Phone" :value="old('phone')" required />
                <x-form.input name="email" label="Email" type="email" :value="old('email')" />
            </x-admin.form-section>

            <x-admin.form-section
                title="Profile"
                note="Helps staff identify the patient and keep demographics accurate."
                tone="blue"
                step="2"
            >
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
                <x-form.select
                    name="care_flags"
                    label="Care alerts"
                    multiple
                    :size="6"
                    hint="Hold Ctrl (Windows) or Cmd (Mac) to select more than one."
                >
                    @foreach (\App\Models\Patient::CARE_FLAGS as $value => $label)
                        <option value="{{ $value }}" @selected(in_array($value, old('care_flags', []), true))>{{ $label }}</option>
                    @endforeach
                </x-form.select>
                <div class="form-span-2">
                    <x-form.textarea name="allergies" label="Allergies" :value="old('allergies')" />
                </div>
            </x-admin.form-section>

            <x-admin.form-section
                title="Emergency contact"
                note="Who to call if the patient cannot be reached."
                tone="amber"
                step="3"
            >
                <x-form.input name="emergency_contact_name" label="Contact name" :value="old('emergency_contact_name')" />
                <x-form.input name="emergency_contact_phone" label="Contact phone" :value="old('emergency_contact_phone')" />
            </x-admin.form-section>

            <x-admin.form-section
                title="Record status"
                note="Inactive records stay in history but are hidden from admission lists."
                tone="green"
                step="4"
                :columns="false"
            >
                <x-form.checkbox name="is_active" label="Active patient record" :checked="old('is_active', true)" />
            </x-admin.form-section>
        </x-admin.form-page>
    </div>
@endsection
