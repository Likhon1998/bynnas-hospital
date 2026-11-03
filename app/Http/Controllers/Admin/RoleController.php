<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Support\SystemAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->orderByRaw("case when name = 'super_admin' then 0 else 1 end")
            ->orderBy('name')
            ->get()
            ->map(function (Role $role): Role {
                $role->setAttribute('users_count', $role->usersCount());

                return $role;
            });

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissionGroups' => $this->permissionGroups(),
            'assignedPermissionIds' => [],
        ]);
    }

    public function store(StoreRoleRequest $request, SystemAuditor $auditor): RedirectResponse
    {
        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissionIds(array_map('intval', $request->validated('permission_ids') ?? []));

        $auditor->record('roles.create', 'Created role '.$role->name, $role, [
            'permissions' => $request->validated('permission_ids') ?? [],
        ], $request->user());

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role '.$role->displayName().' created. Assign it to staff under Users.');
    }

    public function edit(Role $role): View
    {
        $assignedPermissionIds = $role->permissions()->pluck('permissions.id')->all();

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'assignedPermissionIds' => $assignedPermissionIds,
            'usersCount' => $role->usersCount(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role, SystemAuditor $auditor): RedirectResponse
    {
        $name = $request->validated('name');

        if ($role->isProtected()) {
            $name = 'super_admin';
        }

        $role->update([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        $permissionIds = array_map('intval', $request->validated('permission_ids') ?? []);

        if ($role->isProtected()) {
            $usersManageId = Permission::query()->where('name', 'users.manage')->value('id');

            if ($usersManageId && ! in_array((int) $usersManageId, $permissionIds, true)) {
                $permissionIds[] = (int) $usersManageId;
            }
        }

        $role->syncPermissionIds($permissionIds);

        $auditor->record('roles.update', 'Updated role '.$role->name, $role, [
            'permissions' => $permissionIds,
        ], $request->user());

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role permissions saved.');
    }

    public function destroy(Role $role, SystemAuditor $auditor): RedirectResponse
    {
        abort_if($role->isProtected(), 403, 'The Super Admin role cannot be deleted.');
        abort_if($role->usersCount() > 0, 422, 'Remove this role from all staff accounts before deleting it.');

        $name = $role->name;
        $role->delete();

        $auditor->record('roles.delete', 'Deleted role '.$name, null, [
            'role' => $name,
        ], request()->user());

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role deleted.');
    }

    /**
     * @return array<string, Collection<int, Permission>>
     */
    private function permissionGroups(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => $permission->group())
            ->sortKeys()
            ->all();
    }
}
