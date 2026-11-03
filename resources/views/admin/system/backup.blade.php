@extends('layouts.admin')
@section('title', 'Database backup')
@section('heading', 'Database backup')
@section('content')
    <div class="mc-card" style="max-width: 42rem;">
        <div class="mc-card__head">
            <div>
                <h2 class="mc-card__title">
                    <x-admin.nav-icon name="settings" class="h-4 w-4" />
                    <span>Download a backup</span>
                </h2>
                <p>Super Admin only. Restoration is done by replacing the database file or importing the SQL dump on the server.</p>
            </div>
        </div>
        <div style="padding: 1rem 1.15rem 1.25rem;">
            <p class="text-sm text-slate-600">Driver: <strong>{{ $driver }}</strong></p>
            @if ($database)
                <p class="mt-1 text-sm text-slate-600">Database: <strong>{{ $database }}</strong></p>
            @endif
            <form method="POST" action="{{ route('admin.system.backup.download') }}" class="mt-5">
                @csrf
                <button type="submit" class="admin-action">Download backup</button>
            </form>
            <p class="mt-4 text-xs text-slate-500">Each download is written to the system audit log.</p>
        </div>
    </div>
@endsection
