@extends('layouts.admin')
@section('title', 'Create role')
@section('heading', 'Create role')
@section('content')
    <x-admin.form-page
        :action="route('admin.roles.store')"
        :cancel="route('admin.roles.index')"
        submit="Create role"
        lede="Pick a role key and the permissions this role should grant. Assign the role to staff afterward."
    >
        <x-admin.form-section title="Role" note="Use a short lowercase key. Example: ward_clerk or billing_supervisor.">
            <x-form.input name="name" label="Role key" :value="old('name')" required />
        </x-admin.form-section>

        <x-admin.form-section title="Permissions" note="Staff with this role receive every checked permission." :columns="false">
            @include('admin.roles._permissions', [
                'permissionGroups' => $permissionGroups,
                'assignedPermissionIds' => $assignedPermissionIds,
            ])
        </x-admin.form-section>
    </x-admin.form-page>
@endsection
