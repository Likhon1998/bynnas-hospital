@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $nameParts = preg_split('/\s+/', trim($hospitalName)) ?: [$hospitalName];
    $brandMark = $nameParts[0] ?? 'Hospital';
    $brandRest = trim(implode(' ', array_slice($nameParts, 1))) ?: 'Medical Center';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone') ?: '';
    $emergency = $hospital->get('emergency_phone') ?: $phone;
    $addressParts = collect(explode(',', (string) $hospital->get('address')))
        ->map(fn (string $part): string => trim($part))
        ->filter();
    $campus = $addressParts->slice(-3, 2)->implode(', ') ?: ($addressParts->last() ?: 'Main Campus');
    $hoursLabel = $workingHours['saturday'] ?? $workingHours['monday'] ?? 'Open 24 hours';
    $emergencyLabel = $emergencyBanner?->title ?: '24/7 Emergency';
    $logo = $hospital->get('logo');
    $links = [
        ['label' => 'About us', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Departments', 'href' => route('departments'), 'active' => request()->routeIs('departments')],
        ['label' => 'Doctors', 'href' => route('doctors'), 'active' => request()->routeIs('doctors', 'doctors.show')],
        [
            'label' => 'Patients & Visitors',
            'href' => route('home').'#patients',
            'active' => request()->routeIs('lab-reports.*'),
            'children' => [
                ['label' => 'Patient Services', 'href' => route('home').'#patients'],
                ['label' => 'Lab reports', 'href' => route('lab-reports.index'), 'active' => request()->routeIs('lab-reports.*')],
            ],
        ],
        ['label' => 'Services', 'href' => route('services'), 'active' => request()->routeIs('services', 'services.show')],
        ['label' => 'Locations', 'href' => route('home').'#locations', 'active' => false],
        ['label' => 'Contact Us', 'href' => route('home').'#contact', 'active' => false],
    ];
@endphp

<header class="ch-top">
    <div class="ch-rail">
        <div class="ch-wrap ch-rail__bar">
            <a class="ch-rail__alert" href="{{ $emergencyBanner?->destination() ?: route('emergency') }}">
                <span class="ch-rail__pulse" aria-hidden="true"></span>
                <span>{{ $emergencyLabel }}</span>
                @if ($emergency)
                    <strong>{{ $emergency }}</strong>
                @endif
            </a>
            <p class="ch-rail__meta">
                <span>{{ $campus }}</span>
                <span>{{ $hoursLabel }}</span>
            </p>
        </div>
    </div>

    <div class="ch-wrap ch-top__bar">
        <a href="{{ route('home') }}" @class(['ch-brand', 'has-logo' => filled($logo)])>
            @if (filled($logo))
                <img src="{{ $logo }}" alt="{{ $hospitalName }}" class="ch-brand__logo" width="160" height="40" decoding="async">
            @else
                <span class="ch-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 36 36" class="h-5 w-5" fill="none">
                        <path d="M18 8v20M9 18h18" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="ch-brand__copy">
                    <span class="ch-brand__name">{{ $brandMark }}</span>
                    <span class="ch-brand__sub">{{ $brandRest }}</span>
                </span>
            @endif
        </a>

        <nav class="ch-nav" aria-label="Primary">
            @foreach ($links as $link)
                @if (! empty($link['children']))
                    <div class="ch-nav__item" data-nav-dropdown>
                        <button
                            type="button"
                            class="ch-nav__trigger @if ($link['active']) is-active @endif"
                            data-nav-dropdown-trigger
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <span>{{ $link['label'] }}</span>
                            <svg class="ch-nav__caret" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="ch-nav__menu" data-nav-dropdown-panel hidden>
                            @foreach ($link['children'] as $child)
                                <a href="{{ $child['href'] }}" @class(['is-active' => ! empty($child['active'])])>{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ $link['href'] }}" @class(['is-active' => $link['active']])>{{ $link['label'] }}</a>
                @endif
            @endforeach
        </nav>

        <div class="ch-top__actions">
            @if ($phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="ch-phone" aria-label="Call {{ $phone }}">
                    <span class="ch-phone__icon" aria-hidden="true">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 4h4l2 5-2 1a12 12 0 0 0 6 6l1-2 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 4 6a2 2 0 0 1 2-2z"/>
                        </svg>
                    </span>
                    <span class="ch-phone__copy">
                        <small>Call us</small>
                        <strong>{{ $phone }}</strong>
                    </span>
                </a>
            @endif
            <a href="{{ route('home') }}#find-doctor" class="ch-btn ch-btn--ghost ch-btn--find">Find a Doctor</a>
            <a href="{{ route('home') }}#appointment" class="ch-btn ch-btn--solid ch-btn--header-book">Book Appointment</a>
        </div>

        <button
            type="button"
            class="ch-menu"
            data-nav-toggle
            aria-expanded="false"
            aria-controls="mobile-navigation"
            aria-label="Open menu"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            <span class="ch-menu__label">Menu</span>
        </button>
    </div>
</header>

<div id="mobile-navigation" class="ch-drawer hidden" data-nav-panel>
    <button type="button" class="ch-drawer__mask" data-nav-close aria-label="Close menu"></button>
    <div class="ch-drawer__panel">
        <div class="ch-drawer__top">
            <p>{{ $hospitalName }}</p>
            <button type="button" data-nav-close aria-label="Close menu">✕</button>
        </div>
        <a href="{{ $emergencyBanner?->destination() ?: route('emergency') }}" class="ch-drawer__alert" data-nav-close>
            {{ $emergencyLabel }}@if ($emergency) · {{ $emergency }}@endif
        </a>
        <nav class="ch-drawer__nav" aria-label="Mobile">
            @foreach ($links as $link)
                @if (! empty($link['children']))
                    <a href="{{ $link['href'] }}" data-nav-close>{{ $link['label'] }}</a>
                    @foreach ($link['children'] as $child)
                        <a href="{{ $child['href'] }}" class="ch-drawer__sub" data-nav-close>{{ $child['label'] }}</a>
                    @endforeach
                @else
                    <a href="{{ $link['href'] }}" data-nav-close>{{ $link['label'] }}</a>
                @endif
            @endforeach
        </nav>
        @if ($phone)
            <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="ch-btn ch-btn--wide" data-nav-close>Call {{ $phone }}</a>
        @endif
        <a href="{{ route('home') }}#find-doctor" class="ch-btn ch-btn--wide" data-nav-close>Find a Doctor</a>
        <a href="{{ route('home') }}#appointment" class="ch-btn ch-btn--solid ch-btn--wide" data-nav-close>Book Appointment</a>
    </div>
</div>
