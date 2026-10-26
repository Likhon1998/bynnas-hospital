@php
    $nameParts = preg_split('/\s+/', trim((string) $hospitalName)) ?: ['Hospital'];
    $brandMark = $nameParts[0] ?? 'Hospital';
@endphp

<div id="admin-sidebar" class="fixed inset-0 z-40 hidden lg:static lg:block lg:h-dvh" data-admin-sidebar>
    <button type="button" class="absolute inset-0 bg-[#0f172a]/40 lg:hidden" data-admin-sidebar-close aria-label="Close menu"></button>
    <aside class="admin-sidebar absolute inset-y-0 left-0 flex h-full w-[min(14rem,88%)] flex-col lg:static lg:h-dvh lg:w-auto">
        <div class="admin-brand-row">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <span class="admin-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none">
                        <path d="M8 22 16 6l8 16H8Z" fill="#f59e0b"/>
                        <path d="M12 22 20 8l8 14H12Z" fill="#fb923c" opacity="0.9"/>
                    </svg>
                </span>
                <span class="admin-brand__name">{{ $brandMark }}</span>
            </a>

            <button
                type="button"
                class="admin-collapse-btn"
                data-admin-sidebar-collapse
                aria-label="Squeeze sidebar"
                title="Squeeze sidebar"
            >
                <svg class="admin-collapse-btn__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <nav class="admin-scroll min-h-0 flex-1 overflow-y-auto overscroll-contain px-2.5 pb-3" aria-label="Admin">
            @foreach ($adminNavigation->groupBy('group') as $group => $items)
                <div class="admin-nav-group">
                    <p class="admin-nav-label">{{ $group }}</p>
                    <div class="grid gap-0.5">
                        @foreach ($items as $item)
                            <a
                                href="{{ $item['href'] }}"
                                @class(['admin-nav-link', 'is-active' => $item['active']])
                                title="{{ $item['label'] }}"
                            >
                                <x-admin.nav-icon :name="$item['icon']" class="h-[17px] w-[17px]" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="admin-staff">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="admin-nav-link w-full" title="Sign out">
                    <x-admin.nav-icon name="logout" class="h-[17px] w-[17px]" />
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>
</div>
