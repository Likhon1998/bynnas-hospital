@props([
    'title',
    'note' => null,
    'columns' => true,
    'tone' => null,
    'step' => null,
])

<section {{ $attributes->class(['form-card', $tone ? 'form-card--'.$tone : null]) }}>
    <header class="form-card__head">
        <div class="form-card__title">
            @if ($step)
                <span class="form-card__step">{{ $step }}</span>
            @endif
            <div>
                <h2>{{ $title }}</h2>
                @if ($note)
                    <p>{{ $note }}</p>
                @endif
            </div>
        </div>
    </header>
    <div @class(['form-card__body', 'form-grid' => $columns])>
        {{ $slot }}
    </div>
</section>
