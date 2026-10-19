<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->paginate(20);

        $roleMap = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', (new User)->getMorphClass())
            ->get(['model_has_roles.model_id', 'roles.name'])
            ->groupBy('model_id')
            ->map(fn ($rows) => $rows->pluck('name')->all());

        $users->getCollection()->each(function (User $user) use ($roleMap): void {
            $user->setAttribute('assigned_roles', $roleMap->get($user->id, []));
        });

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $roles = DB::table('roles')->orderBy('name')->get();
        $assignedRoleIds = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', $user->getMorphClass())
            ->pluck('role_id')
            ->all();

        return view('admin.users.edit', compact('user', 'roles', 'assignedRoleIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $roleIds = $validated['role_ids'] ?? [];

        if ($request->user()->is($user) && $request->user()->hasRole('super_admin')) {
            $superAdminId = DB::table('roles')->where('name', 'super_admin')->value('id');

            if ($superAdminId && ! in_array((int) $superAdminId, array_map('intval', $roleIds), true)) {
                $roleIds[] = (int) $superAdminId;
            }
        }

        $user->syncRoleIds(array_map('intval', $roleIds));

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }
}
