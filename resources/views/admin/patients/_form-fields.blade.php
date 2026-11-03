@php
    /** @var \App\Models\Patient|null $patient */
    $patient ??= null;
    $careFlags = old('care_flags', $patient?->care_flags ?? []);
@endphp

<x-admin.form-section
    title="Identity"
    note="Required details used at reception, nursing, and billing."
    tone="teal"
    step="1"
>
    <x-form.input name="full_name" label="Full name" :value="old('full_name', $patient?->full_name)" required />
    <x-form.input name="phone" label="Primary phone" :value="old('phone', $patient?->phone)" required hint="Use the number the patient answers for appointments and reports." />
    <x-form.input name="secondary_phone" label="Secondary phone" :value="old('secondary_phone', $patient?->secondary_phone)" />
    <x-form.input name="email" label="Email" type="email" :value="old('email', $patient?->email)" />
    <x-form.input name="nid_number" label="NID / Passport / Birth certificate" :value="old('nid_number', $patient?->nid_number)" hint="National ID preferred for adults. Birth certificate or passport is fine for children." />
    <x-form.input name="guardian_name" label="Father / Mother / Guardian" :value="old('guardian_name', $patient?->guardian_name)" />
</x-admin.form-section>

<x-admin.form-section
    title="Demographics"
    note="Age, gender, and blood group matter for dosing, bed assignment, and emergency care."
    tone="blue"
    step="2"
>
    <x-form.input
        name="date_of_birth"
        label="Date of birth"
        type="date"
        :value="old('date_of_birth', optional($patient?->date_of_birth)->toDateString())"
        required
        max="{{ now()->toDateString() }}"
    />
    <x-form.select name="gender" label="Gender" required>
        <option value="">Select gender</option>
        @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
            <option value="{{ $value }}" @selected(old('gender', $patient?->gender) === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.select name="blood_group" label="Blood group">
        <option value="">Select blood group</option>
        @foreach (\App\Models\Patient::BLOOD_GROUPS as $group)
            <option value="{{ $group }}" @selected(old('blood_group', $patient?->blood_group) === $group)>{{ $group }}</option>
        @endforeach
    </x-form.select>
    <x-form.select name="marital_status" label="Marital status">
        <option value="">Select</option>
        @foreach (\App\Models\Patient::MARITAL_STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(old('marital_status', $patient?->marital_status) === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="occupation" label="Occupation" :value="old('occupation', $patient?->occupation)" />
    <x-form.input name="district" label="District / City" :value="old('district', $patient?->district)" />
    <div class="form-span-2">
        <x-form.textarea name="address" label="Full address" :value="old('address', $patient?->address)" :rows="3" />
    </div>
</x-admin.form-section>

<x-admin.form-section
    title="Clinical safety"
    note="Allergies and care alerts are shown on nursing charts and admission screens."
    tone="rose"
    step="3"
    :columns="false"
>
    <div class="form-grid">
        <div class="form-span-2">
            <x-form.textarea
                name="allergies"
                label="Known allergies"
                :value="old('allergies', $patient?->allergies)"
                :rows="3"
                hint="List drug, food, and latex allergies. Leave blank only if none are known."
            />
        </div>
    </div>
    <div class="mt-4">
        <p class="mb-2 text-sm font-medium text-navy-900">Care alerts</p>
        <div class="form-grid">
            @foreach (\App\Models\Patient::CARE_FLAGS as $value => $label)
                <label class="form-toggle">
                    <input
                        type="checkbox"
                        name="care_flags[]"
                        value="{{ $value }}"
                        class="form-toggle__input"
                        @checked(in_array($value, $careFlags, true))
                    >
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <p class="mt-2 text-sm text-ink-600">Allergy alert is added automatically when allergies are entered.</p>
    </div>
</x-admin.form-section>

<x-admin.form-section
    title="Emergency contact"
    note="Who to call if the patient cannot speak for themselves."
    tone="amber"
    step="4"
>
    <x-form.input name="emergency_contact_name" label="Contact name" :value="old('emergency_contact_name', $patient?->emergency_contact_name)" />
    <x-form.select name="emergency_contact_relation" label="Relationship">
        <option value="">Select relationship</option>
        @foreach (\App\Models\Patient::EMERGENCY_RELATIONS as $value => $label)
            <option value="{{ $value }}" @selected(old('emergency_contact_relation', $patient?->emergency_contact_relation) === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="emergency_contact_phone" label="Contact phone" :value="old('emergency_contact_phone', $patient?->emergency_contact_phone)" />
</x-admin.form-section>

<x-admin.form-section
    title="Notes & status"
    note="Registration notes stay on the chart. Inactive records stay in history but leave admission lists."
    tone="green"
    step="5"
>
    <div class="form-span-2">
        <x-form.textarea
            name="notes"
            label="Registration notes"
            :value="old('notes', $patient?->notes)"
            :rows="3"
            hint="Optional: referral source, language needs, mobility notes, or billing remarks."
        />
    </div>
    <div class="form-span-2">
        <x-form.checkbox name="is_active" label="Active patient record" :checked="old('is_active', $patient?->is_active ?? true)" />
    </div>
</x-admin.form-section>
