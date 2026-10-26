@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $nameParts = preg_split('/\s+/', trim($hospitalName)) ?: [$hospitalName];
    $brandMark = $nameParts[0] ?? 'Hospital';
    $brandRest = trim(implode(' ', array_slice($nameParts, 1)));
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $links = [
        ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'About us', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Services', 'href' => route('services'), 'active' => request()->routeIs('services', 'services.show')],
        ['label' => 'Contact', 'href' => route('home').'#contact', 'active' => false],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-[#e2e8f0] bg-white/95 backdrop-blur">
    <div class="web-wrap flex h-[4.35rem] items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="min-w-0 leading-tight">
            <span class="block text-[1.05rem] font-bold tracking-tight text-[#122447]">{{ $brandMark }}</span>
            @if ($brandRest)
                <span class="mt-0.5 block text-[11px] font-medium leading-none text-[#64748b]">{{ $brandRest }}</span>
            @endif
        </a>

        <nav class="hidden items-center gap-8 md:flex" aria-label="Primary">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    @class([
                        'text-sm font-semibold',
                        'text-[#16a34a]' => $link['active'],
                        'text-[#334155] hover:text-[#16a34a]' => ! $link['active'],
                    ])
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-4">
            @if ($phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hidden text-sm font-medium text-[#475569] lg:inline">
                    {{ $phone }}
                </a>
            @endif
            <a href="{{ route('home') }}#appointment" class="site-btn site-btn--green hidden !min-h-10 !px-4 sm:inline-flex">
                Request a visit
            </a>
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#e2e8f0] text-[#122447] md:hidden"
                data-nav-toggle
                aria-expanded="false"
                aria-controls="mobile-navigation"
                aria-label="Open menu"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>
</header>

<div id="mobile-navigation" class="fixed inset-0 z-50 hidden md:hidden" data-nav-panel>
    <button type="button" class="absolute inset-0 bg-[#122447]/45" data-nav-close aria-label="Close menu"></button>
    <div class="absolute inset-y-0 right-0 flex w-[min(20rem,100%)] flex-col bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between">
            <p class="text-base font-bold text-[#122447]">{{ $hospitalName }}</p>
            <button type="button" class="p-2 text-[#64748b]" data-nav-close aria-label="Close menu">✕</button>
        </div>
        <nav class="mt-8 flex flex-col gap-4" aria-label="Mobile">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" class="text-base font-medium text-[#122447]" data-nav-close>{{ $link['label'] }}</a>
            @endforeach
        </nav>
        <a href="{{ route('home') }}#appointment" class="site-btn site-btn--green mt-8" data-nav-close>
            Request a visit
        </a>
    </div>
</div>
