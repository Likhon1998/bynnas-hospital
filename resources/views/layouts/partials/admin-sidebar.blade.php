@php
    $logo = filled($hospitalLogo ?? null)
        ? $hospitalLogo
        : '/images/branding/bynnas-hospital-logo.png';
    $logoMark = '/images/branding/bynnas-mark.png';
    if (str_starts_with((string) $logo, '/images/branding/')) {
        $logoFile = public_path(ltrim((string) $logo, '/'));
        $logo .= '?v='.(is_file($logoFile) ? filemtime($logoFile) : '2');
    }
    $markFile = public_path('images/branding/bynnas-mark.png');
    $logoMark .= '?v='.(is_file($markFile) ? filemtime($markFile) : '2');
@endphp

<div id="admin-sidebar" class="fixed inset-0 z-40 hidden lg:static lg:block lg:h-dvh" data-admin-sidebar>
    <button type="button" class="absolute inset-0 bg-lilac-950/40 lg:hidden" data-admin-sidebar-close aria-label="Close menu"></button>
    <aside class="admin-sidebar absolute inset-y-0 left-0 flex h-full w-[min(15.25rem,88%)] flex-col lg:static lg:h-dvh lg:w-auto">
        <div class="admin-brand-row">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand has-logo" aria-label="{{ $hospitalName }} dashboard">
                <img
                    src="{{ $logo }}"
                    alt="{{ $hospitalName }}"
                    class="admin-brand__logo admin-brand__logo--full"
                    width="160"
                    height="52"
                    decoding="async"
                >
                <img
                    src="{{ $logoMark }}"
                    alt=""
                    class="admin-brand__logo admin-brand__logo--mark"
                    width="40"
                    height="40"
                    decoding="async"
                    aria-hidden="true"
                >
            </a>

            <button
                type="button"
                class="admin-collapse-btn"
                data-admin-sidebar-collapse
                aria-label="Collapse sidebar"
                data-label="Collapse sidebar"
            >
                <svg class="admin-collapse-btn__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <nav class="admin-scroll admin-nav min-h-0 flex-1 overscroll-contain" aria-label="Hospital menu">
            @if ($adminPrimaryAction)
                <a
                    href="{{ $adminPrimaryAction['href'] }}"
                    @class(['admin-nav-link', 'is-book', 'is-active' => $adminPrimaryAction['active']])
                    aria-label="{{ $adminPrimaryAction['label'] }}"
                    data-label="{{ $adminPrimaryAction['label'] }}"
                >
                    <x-admin.nav-icon :name="$adminPrimaryAction['icon']" class="h-4 w-4" />
                    <span>{{ $adminPrimaryAction['label'] }}</span>
                </a>
            @endif

            @if ($adminDashboardItem)
                <a
                    href="{{ $adminDashboardItem['href'] }}"
                    @class(['admin-nav-link', 'is-active' => $adminDashboardItem['active']])
                    aria-label="{{ $adminDashboardItem['label'] }}"
                    data-label="{{ $adminDashboardItem['label'] }}"
                >
                    <x-admin.nav-icon :name="$adminDashboardItem['icon']" class="h-4 w-4" />
                    <span>{{ $adminDashboardItem['label'] }}</span>
                </a>
            @endif

            @foreach ($adminNavSections as $section)
                <section
                    @class(['admin-nav-section', 'is-open' => $section['open']])
                    data-admin-nav-section
                >
                    <button
                        type="button"
                        class="admin-nav-section__toggle"
                        data-admin-nav-toggle
                        aria-expanded="{{ $section['open'] ? 'true' : 'false' }}"
                    >
                        <span>{{ $section['label'] }}</span>
                        <svg class="admin-nav-section__chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="admin-nav-section__body">
                        @foreach ($section['items'] as $item)
                            @php
                                $childIsCurrent = collect($item['children'])->contains(fn (array $child): bool => $child['active']);
                            @endphp
                            <div @class(['admin-nav-item', 'has-sub' => $item['children'] !== [], 'is-open' => $childIsCurrent])>
                                <a
                                    href="{{ $item['href'] }}"
                                    @class(['admin-nav-link', 'is-active' => $item['active'] && ! $childIsCurrent, 'is-branch' => $childIsCurrent])
                                    aria-label="{{ $item['label'] }}"
                                    data-label="{{ $item['label'] }}"
                                >
                                    <x-admin.nav-icon :name="$item['icon']" class="h-4 w-4" />
                                    <span>{{ $item['label'] }}</span>
                                </a>

                                @if ($item['children'] !== [])
                                    <div class="admin-nav-sub">
                                        @foreach ($item['children'] as $child)
                                            <a
                                                href="{{ $child['href'] }}"
                                                @class(['admin-nav-link', 'is-sub', 'is-active' => $child['active']])
                                                aria-label="{{ $child['label'] }}"
                                                data-label="{{ $child['label'] }}"
                                            >
                                                <x-admin.nav-icon :name="$child['icon']" class="h-3.5 w-3.5" />
                                                <span>{{ $child['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </nav>
    </aside>
</div>
