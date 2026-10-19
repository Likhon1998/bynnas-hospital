@extends('layouts.admin')
@section('title', 'Edit user')
@section('heading', 'Edit user')
@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-3xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-sand-100">
        @csrf
        @method('PUT')
        <x-form.input name="name" label="Name" :value="$user->name" required />
        <x-form.input name="email" label="Email" type="email" :value="$user->email" required />
        <fieldset>
            <legend class="text-sm font-medium text-navy-900">Roles</legend>
            <div class="mt-3 space-y-2">
                @foreach ($roles as $role)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array((int) $role->id, array_map('intval', old('role_ids', $assignedRoleIds)), true)) class="rounded border-sand-100 text-teal-700">
                        {{ str_replace('_', ' ', $role->name) }}
                    </label>
                @endforeach
            </div>
        </fieldset>
        <div class="flex gap-3">
            <button type="submit" class="rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Save</button>
            <a href="{{ route('admin.users.index') }}" class="rounded-full px-5 py-3 text-sm font-semibold text-ink-600">Cancel</a>
        </div>
    </form>
@endsection
