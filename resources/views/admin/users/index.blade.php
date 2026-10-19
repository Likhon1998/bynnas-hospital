@extends('layouts.admin')
@section('title', 'Users')
@section('heading', 'Staff users')
@section('content')
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Roles</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ implode(', ', $user->assigned_roles ?: []) ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-teal-700">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
@endsection
