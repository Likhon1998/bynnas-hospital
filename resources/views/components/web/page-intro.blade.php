@props([
    'title',
    'kicker' => null,
    'note' => null,
])

<section class="border-b border-[#e2e8f0] bg-white">
    <div class="web-wrap py-10 sm:py-12">
        @if ($kicker)
            <span class="site-badge">{{ $kicker }}</span>
        @endif
        <h1 @class(['mt-3 max-w-3xl text-[clamp(1.9rem,3.5vw,2.7rem)] font-bold uppercase tracking-[-0.04em] text-[#122447] leading-[1.1]', 'mt-3' => filled($kicker)])>{{ $title }}</h1>
        @if ($note)
            <p class="mt-4 max-w-2xl text-[1rem] leading-7 text-[#64748b]">{{ $note }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
