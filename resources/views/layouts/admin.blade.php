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
    <div class="admin-shell h-dvh overflow-hidden lg:grid lg:grid-cols-[15.25rem_1fr]" data-admin-shell>
        @include('layouts.partials.admin-sidebar')

        <div class="admin-scroll h-dvh min-w-0 overflow-y-auto overscroll-contain">
            <header class="admin-topbar sticky top-0 z-30 flex items-center justify-between gap-3 px-4 py-3 lg:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-lilac-200 bg-white text-lilac-800 lg:hidden"
                        data-admin-sidebar-toggle
                        aria-label="Open menu"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <h1 class="truncate text-xl font-bold tracking-tight text-lilac-900">@yield('heading', 'Dashboard')</h1>
                    <div class="relative ml-2 hidden min-w-0 max-w-md flex-1 md:block" data-admin-search-root>
                        <form method="GET" action="{{ $adminSearchRoute }}" class="relative" data-admin-search-form>
                            <x-admin.nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-lilac-500" />
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
                                class="h-10 w-full rounded-xl border border-lilac-200 bg-white pl-9 pr-14 text-sm text-lilac-900 placeholder:text-lilac-500"
                            >
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-lilac-500" data-admin-search-hotkey>Ctrl K</span>
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
                    @if ($adminCanBookAppointment)
                        <a href="{{ route('admin.appointments.create') }}" class="admin-book-btn">
                            <x-admin.nav-icon name="clipboard-plus" class="h-4 w-4" />
                            <span>Take appointment</span>
                        </a>
                    @endif
                    <div
                        class="relative"
                        data-admin-menu
                        data-admin-notifications
                        data-admin-notifications-url="{{ $adminNotificationsUrl }}"
                        data-admin-notifications-seen-url="{{ $adminNotificationsSeenUrl }}"
                    >
                        <button
                            type="button"
                            class="admin-icon-btn relative"
                            data-admin-menu-trigger
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Notifications"
                        >
                            <x-admin.nav-icon name="pulse" class="h-5 w-5" />
                            <span
                                class="admin-badge{{ $adminNotificationCount > 0 ? '' : ' hidden' }}"
                                data-admin-notification-badge
                                @if ($adminNotificationCount === 0) hidden @endif
                            >{{ $adminNotificationCount }}</span>
                        </button>
                        <div class="admin-popover admin-popover--end hidden" data-admin-menu-panel hidden>
                            <div class="admin-popover__head">
                                <p class="admin-popover__title">Notifications</p>
                                <a
                                    href="{{ $adminNotificationsHref }}"
                                    class="admin-popover__link{{ $adminNotificationCount > 0 ? '' : ' hidden' }}"
                                    data-admin-notifications-all
                                    @if ($adminNotificationCount === 0) hidden @endif
                                >View all</a>
                            </div>
                            <div class="admin-popover__body" data-admin-notifications-body>
                                @forelse ($adminNotifications as $notice)
                                    <a href="{{ $notice['href'] }}" class="admin-notice admin-notice--{{ $notice['tone'] }}" data-admin-notification-id="{{ $notice['id'] }}">
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
                                <span class="block text-sm font-semibold text-lilac-900">{{ $staffDisplayName }}</span>
                                <span class="block text-xs text-lilac-600">{{ $staffRoleLabel }}</span>
                            </span>
                            <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-lilac-400 text-[11px] font-bold text-lilac-900">{{ $staffInitials }}</span>
                        </button>
                        <div class="admin-popover admin-popover--end admin-popover--user hidden" data-admin-menu-panel hidden>
                            <div class="admin-popover__head">
                                <div>
                                    <p class="admin-popover__title">{{ $staffDisplayName }}</p>
                                    <p class="admin-popover__meta">{{ $staffEmail }}</p>
                                </div>
                            </div>
                            <div class="admin-popover__body admin-popover__body--menu">
                                <a href="{{ route('admin.help') }}" class="admin-menu-link">
                                    <x-admin.nav-icon name="help" class="h-4 w-4" />
                                    <span>Help &amp; Supports</span>
                                </a>
                                @if ($adminCanManageSettings)
                                    <a href="{{ route('admin.settings.edit') }}" class="admin-menu-link">
                                        <x-admin.nav-icon name="settings" class="h-4 w-4" />
                                        <span>Settings</span>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="admin-menu-link admin-menu-link--danger">
                                        <x-admin.nav-icon name="logout" class="h-4 w-4" />
                                        <span>Sign out</span>
                                    </button>
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
