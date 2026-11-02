@props([
    'stats',
    'headline',
    'note' => null,
    'ctaHref' => '#appointment',
    'ctaLabel' => 'Book Appointment →',
])

<section {{ $attributes->merge(['class' => 'ch-band']) }}>
    <div class="ch-wrap ch-band__row">
        <div class="ch-band__copy">
            <div>
                <h2>{{ $headline }}</h2>
                <p>{{ $note ?? $stats['summary'] }}</p>
            </div>
        </div>
        <div class="ch-stats">
            @foreach ($stats['items'] as $stat)
                <div class="ch-stat" data-stat="{{ $stat['key'] }}"><strong>{{ number_format($stat['value']) }}</strong><span>{{ $stat['label'] }}</span></div>
            @endforeach
        </div>
        <a href="{{ $ctaHref }}" class="ch-btn ch-btn--light">{{ $ctaLabel }}</a>
    </div>
</section>
