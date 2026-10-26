@extends('layouts.admin')
@section('title', 'Edit patient')
@section('heading', 'Edit patient')
@section('content')
    <form method="POST" action="{{ route('admin.patients.update', $patient) }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
        @csrf
        @method('PUT')
        <x-form.input name="full_name" label="Full name" :value="$patient->full_name" required />
        <x-form.input name="phone" label="Phone" :value="$patient->phone" required />
        <x-form.input name="email" label="Email" type="email" :value="$patient->email" />
        <x-form.input name="date_of_birth" label="Date of birth" type="date" :value="optional($patient->date_of_birth)->toDateString()" />
        <div>
            <label for="gender" class="text-sm font-medium text-navy-900">Gender</label>
            <select id="gender" name="gender" class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm">
                <option value="">Select</option>
                @foreach (['male', 'female', 'other'] as $gender)
                    <option value="{{ $gender }}" @selected(old('gender', $patient->gender) === $gender)>{{ ucfirst($gender) }}</option>
                @endforeach
            </select>
        </div>
        <x-form.textarea name="address" label="Address" :value="$patient->address" />
        <x-form.input name="emergency_contact_name" label="Emergency contact" :value="$patient->emergency_contact_name" />
        <x-form.input name="emergency_contact_phone" label="Emergency phone" :value="$patient->emergency_contact_phone" />
        <x-form.checkbox name="is_active" label="Active" :checked="$patient->is_active" />
        <div class="flex gap-3">
            <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
            <a href="{{ route('admin.patients.show', $patient) }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
        </div>
    </form>
@endsection
