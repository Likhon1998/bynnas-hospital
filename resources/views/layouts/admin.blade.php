<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · {{ $hospitalName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body h-dvh overflow-hidden font-sans text-ink-900 antialiased">
    <div class="h-dvh overflow-hidden lg:grid lg:grid-cols-[17.5rem_1fr]">
        @include('layouts.partials.admin-sidebar')

        <div class="admin-scroll h-dvh min-w-0 overflow-y-auto overscroll-contain">
            <header class="admin-topbar sticky top-0 z-30 flex items-center justify-between gap-3 px-4 py-3 lg:px-6">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#e2e8f0] bg-white text-[#162028] lg:hidden"
                    data-admin-sidebar-toggle
                    aria-label="Open menu"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>

                <form method="GET" action="{{ $adminSearchRoute }}" class="relative hidden min-w-0 flex-1 md:block">
                    <x-admin.nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a3b8]" />
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        data-admin-search
                        placeholder="{{ $adminSearchPlaceholder }}"
                        class="h-10 w-full rounded-full border border-[#e2e8f0] bg-[#f8fafc] pl-9 pr-16 text-sm text-[#162028]"
                    >
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-[#94a3b8]">Ctrl + K</span>
                </form>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-[#162028]">{{ now()->format('l, d F Y') }}</p>
                        <p class="text-xs text-[#64748b]">{{ now()->format('g:i A') }}</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#06111d] text-[11px] font-semibold text-white">{{ $staffInitials }}</span>
                    <span class="hidden leading-tight lg:block">
                        <span class="block text-sm font-medium text-[#162028]">{{ $staffDisplayName }}</span>
                        <span class="block text-xs text-[#64748b]">{{ $staffRoleLabel }}</span>
                    </span>
                </div>
            </header>

            <div class="admin-main">
                @if (session('status'))
                    <p class="admin-flash">{{ session('status') }}</p>
                @endif
                @if (session('error'))
                    <p class="admin-flash admin-flash--error">{{ session('error') }}</p>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
