@extends('layouts.admin')
@section('title', 'Edit '.$role->displayName())
@section('heading', 'Edit '.$role->displayName())
@section('content')
    <x-admin.form-page
        :action="route('admin.roles.update', $role)"
        method="PUT"
        :cancel="route('admin.roles.index')"
        submit="Save permissions"
        :lede="'Update permissions for '.$role->displayName().'. '.$usersCount.' staff currently use this role.'"
    >
        <x-admin.form-section
            title="Role"
            :note="$role->isProtected() ? 'The Super Admin key is locked so you cannot lock yourself out.' : 'Changing the key updates the role name everywhere.'"
        >
            <x-form.input
                name="name"
                label="Role key"
                :value="old('name', $role->name)"
                required
                :disabled="$role->isProtected()"
            />
            @if ($role->isProtected())
                <input type="hidden" name="name" value="super_admin">
            @endif
        </x-admin.form-section>

        <x-admin.form-section title="Permissions" note="Checked permissions are granted to every staff member with this role." :columns="false">
            @include('admin.roles._permissions', [
                'permissionGroups' => $permissionGroups,
                'assignedPermissionIds' => $assignedPermissionIds,
                'lockUsersManage' => $role->isProtected(),
            ])
        </x-admin.form-section>
    </x-admin.form-page>

    @unless ($role->isProtected())
        <div class="mt-6 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <p class="text-sm text-ink-600">
                @if ($usersCount > 0)
                    This role is assigned to {{ $usersCount }} staff. Remove it from those accounts before deleting.
                @else
                    This role is not assigned to any staff and can be deleted.
                @endif
            </p>
            @if ($usersCount === 0)
                <div class="mt-4">
                    <x-admin.delete-button :action="route('admin.roles.destroy', $role)" label="Delete role" />
                </div>
            @endif
        </div>
    @endunless
@endsection
