@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Bynnas Hospital';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone') ?: '';
    $emergency = $hospital->get('emergency_phone') ?: $phone;
    $addressParts = collect(explode(',', (string) $hospital->get('address')))
        ->map(fn (string $part): string => trim($part))
        ->filter();
    $campus = $addressParts->slice(-3, 2)->implode(', ') ?: ($addressParts->last() ?: 'Main Campus');
    $hoursLabel = $workingHours['saturday'] ?? $workingHours['monday'] ?? 'Open 24 hours';
    $emergencyLabel = $emergencyBanner?->title ?: '24/7 Emergency';
    $logo = filled($hospital->get('logo'))
        ? $hospital->get('logo')
        : '/images/branding/bynnas-hospital-logo.png';
    if (str_starts_with((string) $logo, '/images/branding/')) {
        $logoPath = public_path(ltrim((string) $logo, '/'));
        $logo .= '?v='.(is_file($logoPath) ? filemtime($logoPath) : '2');
    }
    $aboutChildren = array_merge(
        [
            [
                'label' => 'About',
                'href' => route('about'),
                'active' => request()->routeIs('about'),
            ],
        ],
        $leadershipNavRoles ?? [],
    );

    $onDoctorsIndex = request()->routeIs('doctors') && ! request()->routeIs('doctors.show');
    $findDoctorView = $onDoctorsIndex ? request()->query('view', 'department') : null;
    $findDoctorChildren = [
        [
            'label' => 'Department Wise',
            'href' => route('doctors', ['view' => 'department']),
            'active' => $findDoctorView === 'department',
        ],
        [
            'label' => 'Branch Wise',
            'href' => route('doctors', ['view' => 'branch']),
            'active' => $findDoctorView === 'branch',
        ],
        [
            'label' => 'Doctor Name Wise',
            'href' => route('doctors', ['view' => 'name']),
            'active' => $findDoctorView === 'name',
        ],
    ];

    $serviceChildren = collect($navServices ?? [])
        ->map(fn ($service) => [
            'label' => $service->title,
            'href' => $service->isUrgent() ? route('emergency') : route('services.show', $service),
            'active' => request()->routeIs('services.show') && request()->route('service')?->is($service),
        ])
        ->values()
        ->all();

    $serviceChildren[] = [
        'label' => 'All services',
        'href' => route('services'),
        'active' => request()->routeIs('services') && ! request()->routeIs('services.show'),
    ];

    $links = [
        [
            'label' => 'About us',
            'href' => route('about'),
            'active' => request()->routeIs('about', 'about.leadership.*', 'about.people.*'),
            'children' => $aboutChildren,
        ],
        ['label' => 'Departments', 'href' => route('departments'), 'active' => request()->routeIs('departments')],
        [
            'label' => 'Find a Doctor',
            'href' => route('doctors'),
            'active' => request()->routeIs('doctors', 'doctors.show'),
            'children' => $findDoctorChildren,
            'menu_class' => 'ch-find-menu',
        ],
        [
            'label' => 'Patients & Visitors',
            'href' => route('lab-reports.index'),
            'active' => request()->routeIs('lab-reports.*'),
            'children' => [
                ['label' => 'Lab reports', 'href' => route('lab-reports.index'), 'active' => request()->routeIs('lab-reports.*')],
            ],
        ],
        [
            'label' => 'Services',
            'href' => route('services'),
            'active' => request()->routeIs('services', 'services.show', 'emergency'),
            'children' => $serviceChildren,
        ],
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
        <a href="{{ route('home') }}" class="ch-brand has-logo" aria-label="{{ $hospitalName }} home">
            <img
                src="{{ $logo }}"
                alt="{{ $hospitalName }}"
                class="ch-brand__logo"
                width="220"
                height="72"
                decoding="async"
            >
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
                        <div @class(['ch-nav__menu', $link['menu_class'] ?? null]) data-nav-dropdown-panel hidden>
                            @foreach ($link['children'] as $child)
                                <a href="{{ $child['href'] }}" @class(['is-active' => ! empty($child['active'])])>
                                    <span>{{ $child['label'] }}</span>
                                </a>
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

    @if (($tickerAcknowledgements ?? collect())->isNotEmpty())
        <div class="ch-ticker" aria-label="Acknowledgements" lang="bn">
            <span class="ch-ticker__label">স্বীকৃতি</span>
            <div class="ch-ticker__viewport">
                <div class="ch-ticker__run">
                    @foreach ([0, 1] as $loopPass)
                        <div class="ch-ticker__group" @if ($loopPass === 1) aria-hidden="true" @endif>
                            @foreach ($tickerAcknowledgements as $acknowledgement)
                                <span class="ch-ticker__item">{{ $acknowledgement->message }}</span>
                                <span class="ch-ticker__sep" aria-hidden="true">•</span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</header>

<div id="mobile-navigation" class="ch-drawer hidden" data-nav-panel>
    <button type="button" class="ch-drawer__mask" data-nav-close aria-label="Close menu"></button>
    <div class="ch-drawer__panel">
        <div class="ch-drawer__top">
            <img
                src="{{ $logo }}"
                alt="{{ $hospitalName }}"
                class="ch-drawer__logo"
                width="180"
                height="56"
                decoding="async"
            >
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
        <a href="{{ route('home') }}#appointment" class="ch-btn ch-btn--solid ch-btn--wide" data-nav-close>Book Appointment</a>
    </div>
</div>
