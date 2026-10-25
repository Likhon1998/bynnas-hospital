@php
    $nameParts = preg_split('/\s+/', trim((string) $hospitalName)) ?: ['Hospital'];
    $brandMark = $nameParts[0] ?? 'Hospital';
    $brandRest = trim(implode(' ', array_slice($nameParts, 1)));
@endphp

<div id="admin-sidebar" class="fixed inset-0 z-40 hidden lg:static lg:block lg:h-dvh" data-admin-sidebar>
    <button type="button" class="absolute inset-0 bg-[#06111d]/60 lg:hidden" data-admin-sidebar-close aria-label="Close menu"></button>
    <aside class="admin-sidebar absolute inset-y-0 left-0 flex h-full w-[min(17.5rem,88%)] flex-col lg:static lg:h-dvh lg:w-auto">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <svg class="admin-brand__mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                <path fill="#2ec4c6" d="M16.2 3.5h7.6c1.7 0 2.7 1 2.7 2.6v7.2h7.2c1.6 0 2.6 1 2.6 2.7v7.6c0 1.7-1 2.7-2.6 2.7h-7.2v7.2c0 1.6-1 2.6-2.7 2.6h-7.6c-1.7 0-2.7-1-2.7-2.6v-7.2H6.3c-1.6 0-2.6-1-2.6-2.7v-7.6c0-1.7 1-2.7 2.6-2.7h7.2V6.1c0-1.6 1-2.6 2.7-2.6z"/>
            </svg>
            <span class="min-w-0">
                <span class="admin-brand__name">{{ $brandMark }}</span>
                <span class="admin-brand__sub">{{ $brandRest !== '' ? $brandRest : 'Hospital' }}</span>
            </span>
        </a>

        <nav class="admin-scroll min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 pb-2" aria-label="Admin">
            @foreach ($adminNavigation->groupBy('group') as $group => $items)
                <div class="admin-nav-group">
                    <p class="admin-nav-label">{{ $group }}</p>
                    <div class="grid gap-1">
                        @foreach ($items as $item)
                            <a
                                href="{{ $item['href'] }}"
                                @class(['admin-nav-link', 'is-active' => $item['active']])
                            >
                                <x-admin.nav-icon :name="$item['icon']" class="h-[18px] w-[18px]" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="admin-staff">
            <div class="admin-staff__who">
                <span class="admin-staff__avatar">{{ $staffInitials }}</span>
                <span class="min-w-0 flex-1">
                    <span class="admin-staff__name">{{ $staffDisplayName }}</span>
                    <span class="admin-staff__role">{{ $staffRoleLabel }}</span>
                </span>
            </div>
            <div class="admin-staff__links">
                <a href="{{ route('home') }}">
                    <x-admin.nav-icon name="globe" class="h-4 w-4" />
                    Website
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit">
                        <x-admin.nav-icon name="logout" class="h-4 w-4" />
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>
