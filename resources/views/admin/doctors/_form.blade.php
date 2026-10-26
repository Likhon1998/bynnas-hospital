<x-admin.form-page
    :action="$action"
    :method="$method"
    :cancel="route('admin.doctors.index')"
    lede="Keep the doctor profile complete so reception, billing, and the public website all show the same person."
>
    <x-admin.form-section title="Identity" note="Who this doctor is on the roster and the website.">
        <x-form.input name="name" label="Full name" :value="$doctor->name" required />
        <x-form.input name="designation" label="Designation" :value="$doctor->designation" required />
        <x-form.select name="department_id" label="Department" required>
            <option value="">Select department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $doctor->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </x-form.select>
        <x-form.input name="specialization" label="Specialization" :value="$doctor->specialization" />
    </x-admin.form-section>

    <x-admin.form-section title="Practice" note="Used by reception when booking and by billing for consultation charges.">
        <x-form.input name="qualifications" label="Qualifications" :value="$doctor->qualifications" />
        <x-form.input name="experience_years" label="Years of experience" type="number" :value="$doctor->experience_years" />
        <x-form.input name="registration_number" label="Registration number" :value="$doctor->registration_number" />
        <x-form.input name="consultation_fee" label="Consultation fee" type="number" :value="$doctor->consultation_fee" />
        <x-form.select name="user_id" label="Linked staff user">
            <option value="">None</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $doctor->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </x-form.select>
        <x-form.input name="sort_order" label="Sort order" type="number" :value="$doctor->sort_order ?? 0" />
    </x-admin.form-section>

    <x-admin.form-section title="Public profile" note="Photo, URL, and biography shown on the hospital website.">
        <x-form.input name="photo" label="Photo URL" :value="$doctor->photo" />
        <x-form.input name="slug" label="Slug" :value="$doctor->slug" />
        <div class="form-span-2">
            <x-form.textarea name="biography" label="Biography" :value="$doctor->biography" :rows="6" />
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Visibility" note="Control where this doctor appears." :columns="false">
        <div class="form-grid">
            <x-form.checkbox name="is_featured" label="Featured on homepage" :checked="$doctor->is_featured" />
            <x-form.checkbox name="is_active" label="Active on the roster" :checked="$doctor->is_active" />
        </div>
    </x-admin.form-section>
</x-admin.form-page>
