@props([
    'stats',
    'headline',
    'note' => null,
    'ctaHref' => '#appointment',
    'ctaLabel' => 'Book Appointment →',
])

@php
    $statIcons = [
        'doctors' => 'stethoscope',
        'departments' => 'hospital',
        'patients' => 'users',
        'services' => 'pulse',
    ];
@endphp

<section {{ $attributes->merge(['class' => 'ch-band']) }} data-campus-band>
    <div class="ch-band__glow" aria-hidden="true"></div>
    <div class="ch-wrap ch-band__row">
        <div class="ch-band__copy">
            <div>
                <p class="ch-band__kicker">Campus snapshot</p>
                <h2>{{ $headline }}</h2>
                <p>{{ $note ?? $stats['summary'] }}</p>
            </div>
        </div>

        <div class="ch-stats ch-stats--band" role="list">
            @foreach ($stats['items'] as $stat)
                <div
                    class="ch-stat ch-stat--card"
                    role="listitem"
                    data-stat="{{ $stat['key'] }}"
                    data-count-to="{{ (int) $stat['value'] }}"
                >
                    <span class="ch-stat__icon" aria-hidden="true">
                        <x-icon :name="$statIcons[$stat['key']] ?? 'hospital'" />
                    </span>
                    <strong data-count-value>0</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>

        <a href="{{ $ctaHref }}" class="ch-btn ch-btn--light ch-band__cta">{{ $ctaLabel }}</a>
    </div>
</section>
