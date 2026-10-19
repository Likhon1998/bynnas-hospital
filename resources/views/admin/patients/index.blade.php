@extends('layouts.admin')
@section('title', 'Patients')
@section('heading', 'Patients')
@section('content')
    <form method="GET" class="mb-6 flex w-full max-w-md gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search patients" class="w-full rounded-full border border-sand-100 bg-white px-4 py-2.5 text-sm">
        <button type="submit" class="rounded-full bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white">Search</button>
    </form>
    <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-sand-100">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-sand-50 text-xs uppercase tracking-wide text-ink-400">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Phone</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-sand-100">
                @forelse ($patients as $patient)
                    <tr>
                        <td class="px-4 py-3 font-medium text-navy-900">{{ $patient->full_name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $patient->phone }}</td>
                        <td class="px-4 py-3"><x-admin.status-pill :value="$patient->is_active ? 'active' : 'inactive'" /></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.patients.show', $patient) }}" class="font-semibold text-teal-700">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-ink-600">No patients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $patients->links() }}</div>
@endsection
