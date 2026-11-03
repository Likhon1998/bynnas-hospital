@extends('layouts.admin')
@section('title', 'Create staff')
@section('heading', 'Create staff')
@section('content')
    <x-admin.form-page
        :action="route('admin.users.store')"
        :cancel="route('admin.users.index')"
        submit="Create account"
        lede="Create a login for Hospital Admins, Doctors, Receptionists, and other staff. Access follows the roles you assign."
    >
        <x-admin.form-section title="Account" note="Sign-in details for this staff member.">
            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.input name="email" label="Email" type="email" :value="old('email')" required />
            <x-form.input name="password" label="Password" type="password" required />
            <x-form.input name="password_confirmation" label="Confirm password" type="password" required />
            <x-form.checkbox name="is_active" label="Account is active" :checked="old('is_active', true)" />
        </x-admin.form-section>

        <x-admin.form-section title="Roles" note="Choose every role this person needs." :columns="false">
            <div class="form-grid">
                @foreach ($roles as $role)
                    <label class="form-toggle">
                        <input
                            type="checkbox"
                            name="role_ids[]"
                            value="{{ $role->id }}"
                            @checked(in_array((int) $role->id, array_map('intval', old('role_ids', $assignedRoleIds)), true))
                            class="form-toggle__input"
                        >
                        <span>{{ str_replace('_', ' ', $role->name) }}</span>
                    </label>
                @endforeach
            </div>
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
