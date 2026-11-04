@props([
    'label' => 'Action',
])

<div {{ $attributes->class(['row-menu']) }} data-row-menu>
    <button
        type="button"
        class="row-menu__btn"
        data-row-menu-trigger
        aria-expanded="false"
        aria-haspopup="menu"
    >
        <span>{{ $label }}</span>
        <svg class="row-menu__chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>
    <div class="row-menu__panel" data-row-menu-panel role="menu" hidden>
        {{ $slot }}
    </div>
</div>
