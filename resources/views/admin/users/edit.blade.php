@extends('layouts.admin')
@section('title', 'Edit user')
@section('heading', 'Edit user')
@section('content')
    <x-admin.form-page
        :action="route('admin.users.update', $user)"
        method="PUT"
        :cancel="route('admin.users.index')"
        lede="Update this staff account and the roles they can use in the hospital console."
    >
        <x-admin.form-section title="Account" note="Sign-in details for this staff member.">
            <x-form.input name="name" label="Name" :value="$user->name" required />
            <x-form.input name="email" label="Email" type="email" :value="$user->email" required />
        </x-admin.form-section>

        <x-admin.form-section title="Roles" note="Choose every role this person needs. Access follows the selected roles." :columns="false">
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
