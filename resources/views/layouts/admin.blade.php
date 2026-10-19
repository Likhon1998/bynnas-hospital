<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · {{ $hospitalName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sand-50 font-sans text-ink-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[16.5rem_1fr]">
        <div id="admin-sidebar" class="fixed inset-0 z-40 hidden lg:static lg:block" data-admin-sidebar>
            <button type="button" class="absolute inset-0 bg-navy-950/50 lg:hidden" data-admin-sidebar-close aria-label="Close menu"></button>
            <aside class="absolute inset-y-0 left-0 flex w-[min(16.5rem,85%)] flex-col bg-navy-950 text-white lg:static lg:w-auto">
                <div class="border-b border-white/10 px-5 py-5">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-gold-500">Hospital admin</p>
                    <p class="mt-1 font-display text-lg leading-tight">{{ $hospitalName }}</p>
                </div>
                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Admin">
                    @foreach ($adminNavigation as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'block rounded-xl px-3 py-2 text-sm font-medium transition',
                                'bg-teal-700 text-white' => request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs(str_replace('.edit', '.*', $item['route'])),
                                'text-white/70 hover:bg-white/5 hover:text-white' => ! (request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs(str_replace('.edit', '.*', $item['route']))),
                            ])
                        >{{ $item['label'] }}</a>
                    @endforeach
                </nav>
                <div class="border-t border-white/10 p-4">
                    <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-white/50">{{ auth()->user()->email }}</p>
                    <div class="mt-3 flex flex-col gap-2">
                        <a href="{{ route('home') }}" class="text-xs font-semibold text-gold-500 hover:text-white">View website</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-white/70 hover:text-white">Sign out</button>
                        </form>
                    </div>
                </div>
            </aside>
        </div>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-sand-100 bg-white/90 px-4 py-3 backdrop-blur-md lg:px-8">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-sand-100 text-navy-900 lg:hidden"
                    data-admin-sidebar-toggle
                    aria-label="Open menu"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <p class="font-display text-lg text-navy-900 lg:text-xl">@yield('heading', 'Dashboard')</p>
                <span class="hidden text-xs text-ink-400 sm:inline">Staff console</span>
            </header>

            <div class="px-4 py-6 lg:px-8">
                @if (session('status'))
                    <p class="mb-4 rounded-2xl bg-teal-50 px-4 py-3 text-sm font-medium text-teal-800">{{ session('status') }}</p>
                @endif
                @if (session('error'))
                    <p class="mb-4 rounded-2xl bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
