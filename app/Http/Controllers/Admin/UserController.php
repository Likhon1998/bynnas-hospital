<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SystemAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $roleFilter = $request->string('role')->trim()->toString();

        $users = User::query()
            ->when($roleFilter !== '', function ($query) use ($roleFilter): void {
                $query->whereExists(function ($sub) use ($roleFilter): void {
                    $sub->selectRaw('1')
                        ->from('model_has_roles')
                        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->whereColumn('model_has_roles.model_id', 'users.id')
                        ->where('model_has_roles.model_type', (new User)->getMorphClass())
                        ->where('roles.name', $roleFilter);
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roleMap = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', (new User)->getMorphClass())
            ->whereIn('model_has_roles.model_id', $users->getCollection()->pluck('id'))
            ->get(['model_has_roles.model_id', 'roles.name'])
            ->groupBy('model_id')
            ->map(fn ($rows) => $rows->pluck('name')->all());

        $users->getCollection()->each(function (User $user) use ($roleMap): void {
            $user->setAttribute('assigned_roles', $roleMap->get($user->id, []));
        });

        $roles = DB::table('roles')->orderBy('name')->get(['id', 'name']);

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = DB::table('roles')->orderBy('name')->get();

        return view('admin.users.create', [
            'roles' => $roles,
            'assignedRoleIds' => [],
        ]);
    }

    public function store(Request $request, SystemAuditor $auditor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $user->syncRoleIds(array_map('intval', $validated['role_ids'] ?? []));

        $auditor->record('users.create', 'Created staff account '.$user->email, $user, [
            'roles' => $validated['role_ids'] ?? [],
        ], $request->user());

        return redirect()->route('admin.users.index')->with('status', 'Staff account created.');
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

    public function update(Request $request, User $user, SystemAuditor $auditor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $wasActive = (bool) $user->is_active;
        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = Hash::make($validated['password']);
        }

        if ($request->user()?->is($user)) {
            $payload['is_active'] = true;
        }

        $user->update($payload);

        $roleIds = $validated['role_ids'] ?? [];

        if ($request->user()?->is($user) && $request->user()->hasRole('super_admin')) {
            $superAdminId = DB::table('roles')->where('name', 'super_admin')->value('id');

            if ($superAdminId && ! in_array((int) $superAdminId, array_map('intval', $roleIds), true)) {
                $roleIds[] = (int) $superAdminId;
            }
        }

        $user->syncRoleIds(array_map('intval', $roleIds));

        $auditor->record('users.update', 'Updated staff account '.$user->email, $user, [
            'roles' => $roleIds,
            'is_active' => (bool) $user->is_active,
            'deactivated' => $wasActive && ! $user->is_active,
        ], $request->user());

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function toggleActive(Request $request, User $user, SystemAuditor $auditor): RedirectResponse
    {
        abort_if($request->user()?->is($user), 422, 'You cannot change the status of your own account.');

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $auditor->record(
            $user->is_active ? 'users.activate' : 'users.deactivate',
            ($user->is_active ? 'Activated' : 'Deactivated').' staff account '.$user->email,
            $user,
            ['is_active' => (bool) $user->is_active],
            $request->user()
        );

        return redirect()
            ->back()
            ->with('status', $user->is_active ? 'Staff account activated.' : 'Staff account deactivated.');
    }

    public function destroy(Request $request, User $user, SystemAuditor $auditor): RedirectResponse
    {
        abort_if($request->user()?->is($user), 422, 'You cannot delete your own account.');

        $email = $user->email;

        DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', $user->getMorphClass())
            ->delete();

        $user->delete();

        $auditor->record('users.delete', 'Deleted staff account '.$email, null, [
            'email' => $email,
        ], $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Staff account deleted.');
    }
}
