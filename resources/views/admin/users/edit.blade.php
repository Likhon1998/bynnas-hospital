@extends('layouts.admin')
@section('title', 'Edit user')
@section('heading', 'Edit user')
@section('content')
    <x-admin.form-page
        :action="route('admin.users.update', $user)"
        method="PUT"
        :cancel="route('admin.users.index')"
        lede="Update this staff account, change their roles, or deactivate console access."
    >
        <x-admin.form-section title="Account" note="Sign-in details for this staff member. Deactivate to block console access without deleting the account.">
            <x-form.input name="name" label="Name" :value="$user->name" required />
            <x-form.input name="email" label="Email" type="email" :value="$user->email" required />
            <x-form.input name="password" label="New password" type="password" hint="Leave blank to keep the current password." />
            <x-form.input name="password_confirmation" label="Confirm new password" type="password" />
            @unless (auth()->id() === $user->id)
                <x-form.checkbox name="is_active" label="Account is active" :checked="old('is_active', $user->is_active)" />
            @else
                <input type="hidden" name="is_active" value="1">
                <p class="text-sm text-ink-600">Your own account stays active while you are signed in.</p>
            @endunless
        </x-admin.form-section>

        <x-admin.form-section title="Roles" note="Tick every role this person needs. Access follows the selected roles." :columns="false">
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

    @unless (auth()->id() === $user->id)
        <div class="mt-6 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
            <p class="text-sm text-ink-600">Delete permanently removes this login. Prefer Deactivate if you may need the account again.</p>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                    @csrf
                    <button type="submit" class="table-btn table-btn--mute">
                        {{ $user->is_active ? 'Deactivate account' : 'Activate account' }}
                    </button>
                </form>
                <x-admin.delete-button :action="route('admin.users.destroy', $user)" label="Delete account" variant="pill" />
            </div>
        </div>
    @endunless
@endsection
