@foreach ($permissionGroups as $group => $permissions)
    <div class="form-span-2 mt-4 first:mt-0">
        <p class="text-sm font-semibold text-navy-900">{{ $group }}</p>
        <div class="form-grid mt-3">
            @foreach ($permissions as $permission)
                <label class="form-toggle">
                    <input
                        type="checkbox"
                        name="permission_ids[]"
                        value="{{ $permission->id }}"
                        @checked(in_array((int) $permission->id, array_map('intval', old('permission_ids', $assignedPermissionIds)), true))
                        class="form-toggle__input"
                        @if (($lockUsersManage ?? false) && $permission->name === 'users.manage')
                            checked
                            onclick="return false;"
                        @endif
                    >
                    <span>
                        {{ $permission->displayName() }}
                        <span class="block text-xs font-normal text-ink-500">{{ $permission->name }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </div>
@endforeach
