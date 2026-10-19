@php
    $links = [
        ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'About us', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact us', 'href' => route('home').'#contact', 'active' => false],
        ['label' => 'Services', 'href' => route('services'), 'active' => request()->routeIs('services', 'services.show')],
    ];
@endphp

<header class="absolute inset-x-0 top-0 z-40">
    <div class="mx-auto grid max-w-6xl grid-cols-[auto_1fr_auto] items-center gap-4 px-5 py-6 sm:px-8">
        <a href="{{ route('home') }}" class="text-[1.65rem] font-bold leading-none tracking-tight text-white">
            Dr.
        </a>

        <nav class="hidden items-center justify-center gap-9 md:flex" aria-label="Primary">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    @class([
                        'text-[0.95rem] font-medium text-white transition hover:text-white',
                        'underline decoration-white decoration-2 underline-offset-[10px]' => $link['active'],
                        'text-white/90' => ! $link['active'],
                    ])
                >{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center justify-end gap-2">
            <x-ui.button href="{{ route('home') }}#appointment" variant="secondary" class="hidden sm:inline-flex !py-2.5 !text-[0.9rem]">
                Book Appointment
            </x-ui.button>
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/40 text-white md:hidden"
                data-nav-toggle
                aria-expanded="false"
                aria-controls="mobile-navigation"
                aria-label="Open menu"
            >
                <span class="sr-only">Open menu</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>
</header>

<div id="mobile-navigation" class="fixed inset-0 z-50 hidden md:hidden" data-nav-panel>
    <button type="button" class="absolute inset-0 bg-teal-900/60" data-nav-close aria-label="Close menu"></button>
    <div class="absolute inset-y-0 right-0 flex w-[min(20rem,100%)] flex-col bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between">
            <p class="text-lg font-bold text-teal-800">Dr.</p>
            <button type="button" class="rounded-full p-2 text-ink-600" data-nav-close aria-label="Close menu">✕</button>
        </div>
        <nav class="mt-8 flex flex-col gap-4" aria-label="Mobile">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" class="text-base font-medium text-ink-900" data-nav-close>{{ $link['label'] }}</a>
            @endforeach
        </nav>
        <x-ui.button href="{{ route('home') }}#appointment" variant="secondary" class="mt-8 !bg-teal-700 !text-white" data-nav-close>
            Book Appointment
        </x-ui.button>
    </div>
</div>
