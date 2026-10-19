<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <div>
        <label for="department_id" class="text-sm font-medium text-navy-900">Department</label>
        <select id="department_id" name="department_id" required class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
            <option value="">Select department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $doctor->department_id) == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @error('department_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="user_id" class="text-sm font-medium text-navy-900">Linked staff user</label>
        <select id="user_id" name="user_id" class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
            <option value="">None</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $doctor->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
        @error('user_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <x-form.input name="name" label="Name" :value="$doctor->name" required />
    <x-form.input name="slug" label="Slug" :value="$doctor->slug" />
    <x-form.input name="photo" label="Photo URL" :value="$doctor->photo" />
    <x-form.input name="designation" label="Designation" :value="$doctor->designation" required />
    <x-form.input name="qualifications" label="Qualifications" :value="$doctor->qualifications" />
    <x-form.input name="specialization" label="Specialization" :value="$doctor->specialization" />
    <x-form.input name="experience_years" label="Years of experience" type="number" :value="$doctor->experience_years" />
    <x-form.input name="registration_number" label="Registration number" :value="$doctor->registration_number" />
    <x-form.input name="consultation_fee" label="Consultation fee" type="number" :value="$doctor->consultation_fee" />
    <x-form.textarea name="biography" label="Biography" :value="$doctor->biography" :rows="6" />
    <x-form.input name="sort_order" label="Sort order" type="number" :value="$doctor->sort_order ?? 0" />
    <div class="flex flex-wrap gap-6">
        <x-form.checkbox name="is_featured" label="Featured on homepage" :checked="$doctor->is_featured" />
        <x-form.checkbox name="is_active" label="Active" :checked="$doctor->is_active" />
    </div>
    <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
        <a href="{{ route('admin.doctors.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
    </div>
</form>
