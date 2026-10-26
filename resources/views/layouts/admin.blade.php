<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $hospitalName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body h-dvh overflow-hidden font-sans text-ink-900 antialiased">
    <div class="admin-shell h-dvh overflow-hidden lg:grid lg:grid-cols-[14rem_1fr]" data-admin-shell>
        @include('layouts.partials.admin-sidebar')

        <div class="admin-scroll h-dvh min-w-0 overflow-y-auto overscroll-contain">
            <header class="admin-topbar sticky top-0 z-30 flex items-center justify-between gap-3 px-4 py-3 lg:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#d1d5db] bg-white text-[#111827] lg:hidden"
                        data-admin-sidebar-toggle
                        aria-label="Open menu"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <h1 class="truncate text-xl font-bold tracking-tight text-[#111827]">@yield('heading', 'Dashboard')</h1>
                    <div class="relative ml-2 hidden min-w-0 max-w-md flex-1 md:block" data-admin-search-root>
                        <form method="GET" action="{{ $adminSearchRoute }}" class="relative" data-admin-search-form>
                            <x-admin.nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6b7280]" />
                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                data-admin-search
                                data-admin-search-url="{{ $adminSearchUrl }}"
                                placeholder="{{ $adminSearchPlaceholder }}"
                                autocomplete="off"
                                aria-autocomplete="list"
                                aria-controls="admin-search-results"
                                aria-expanded="false"
                                class="h-10 w-full rounded-xl border border-[#d1d5db] bg-white pl-9 pr-14 text-sm text-[#111827] placeholder:text-[#6b7280]"
                            >
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-[#6b7280]" data-admin-search-hotkey>Ctrl K</span>
                        </form>
                        <div
                            id="admin-search-results"
                            class="admin-popover admin-search-results hidden"
                            data-admin-search-results
                            role="listbox"
                            hidden
                        ></div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <div class="relative" data-admin-menu>
                        <button
                            type="button"
                            class="admin-icon-btn relative"
                            data-admin-menu-trigger
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Notifications"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0"/></svg>
                            @if ($adminNotificationCount > 0)
                                <span class="admin-badge">{{ $adminNotificationCount > 9 ? '9+' : $adminNotificationCount }}</span>
                            @endif
                        </button>
                        <div class="admin-popover admin-popover--end hidden" data-admin-menu-panel hidden>
                            <div class="admin-popover__head">
                                <p class="admin-popover__title">Notifications</p>
                                @if ($adminNotificationCount > 0)
                                    <a href="{{ $adminNotificationsHref }}" class="admin-popover__link">View all</a>
                                @endif
                            </div>
                            <div class="admin-popover__body">
                                @forelse ($adminNotifications as $notice)
                                    <a href="{{ $notice['href'] }}" class="admin-notice admin-notice--{{ $notice['tone'] }}">
                                        <span class="admin-notice__title">{{ $notice['title'] }}</span>
                                        <span class="admin-notice__detail">{{ $notice['detail'] }}</span>
                                        @if ($notice['time'] !== '')
                                            <span class="admin-notice__time">{{ $notice['time'] }}</span>
                                        @endif
                                    </a>
                                @empty
                                    <p class="admin-popover__empty">No critical alerts right now.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="relative" data-admin-menu>
                        <button
                            type="button"
                            class="admin-user-trigger"
                            data-admin-menu-trigger
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Account menu"
                        >
                            <span class="hidden text-right sm:block">
                                <span class="block text-sm font-semibold text-[#111827]">{{ $staffDisplayName }}</span>
                                <span class="block text-xs text-[#4b5563]">{{ $staffRoleLabel }}</span>
                            </span>
                            <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-[#f59e0b] text-[11px] font-bold text-white">{{ $staffInitials }}</span>
                        </button>
                        <div class="admin-popover admin-popover--end admin-popover--user hidden" data-admin-menu-panel hidden>
                            <div class="admin-popover__head">
                                <div>
                                    <p class="admin-popover__title">{{ $staffDisplayName }}</p>
                                    <p class="admin-popover__meta">{{ $staffEmail }}</p>
                                </div>
                            </div>
                            <div class="admin-popover__body admin-popover__body--menu">
                                <a href="{{ route('admin.help') }}" class="admin-menu-link">Help &amp; Supports</a>
                                @if ($adminCanManageSettings)
                                    <a href="{{ route('admin.settings.edit') }}" class="admin-menu-link">Settings</a>
                                @endif
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="admin-menu-link admin-menu-link--danger">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
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
