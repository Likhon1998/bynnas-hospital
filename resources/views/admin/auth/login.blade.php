@extends('layouts.admin-guest')

@section('title', 'Staff sign in')

@section('content')
    <p class="text-[11px] uppercase tracking-[0.2em] text-teal-700">Staff console</p>
    <h1 class="mt-2 font-display text-3xl text-navy-900">Sign in</h1>
    <p class="mt-2 text-sm text-ink-600">Use your hospital staff account to continue.</p>

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="text-sm font-medium text-navy-900">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm outline-none ring-teal-700/20 focus:ring-2">
            @error('email') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="text-sm font-medium text-navy-900">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 w-full rounded-2xl border border-sand-100 bg-sand-50 px-4 py-3 text-sm outline-none ring-teal-700/20 focus:ring-2">
            @error('password') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-ink-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-sand-100 text-teal-700">
            Remember me
        </label>
        <button type="submit" class="w-full rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">Sign in</button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-400">
        <a href="{{ route('home') }}" class="font-medium text-teal-700 hover:text-navy-900">Back to website</a>
    </p>
@endsection
