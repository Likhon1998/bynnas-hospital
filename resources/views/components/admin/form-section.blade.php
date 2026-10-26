@props([
    'title',
    'note' => null,
    'columns' => true,
])

<section class="form-card">
    <header class="form-card__head">
        <h2>{{ $title }}</h2>
        @if ($note)
            <p>{{ $note }}</p>
        @endif
    </header>
    <div @class(['form-card__body', 'form-grid' => $columns])>
        {{ $slot }}
    </div>
</section>
