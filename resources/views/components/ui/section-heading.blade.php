@props([
    'eyebrow' => null,
    'title',
    'intro' => null,
    'align' => 'left',
    'tone' => 'dark',
])

<div @class(['max-w-2xl', 'mx-auto text-center' => $align === 'center'])>
    @if ($eyebrow)
        <p @class(['text-xs font-semibold uppercase tracking-[0.14em]', 'text-gold-500' => $tone === 'light', 'text-teal-800' => $tone === 'dark'])>{{ $eyebrow }}</p>
    @endif
    <h2 @class(['font-display mt-1.5 text-2xl font-semibold tracking-tight sm:text-3xl', 'text-white' => $tone === 'light', 'text-navy-900' => $tone === 'dark'])>{{ $title }}</h2>
    @if ($intro)
        <p @class(['mt-2 max-w-xl text-sm leading-6 sm:text-base', 'text-white/75' => $tone === 'light', 'text-ink-600' => $tone === 'dark'])>{{ $intro }}</p>
    @endif
</div>
