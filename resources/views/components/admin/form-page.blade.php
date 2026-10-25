@props([
    'action',
    'method' => 'POST',
    'cancel',
    'submit' => 'Save changes',
    'lede' => null,
    'submitName' => null,
    'submitValue' => null,
])

<form method="POST" action="{{ $action }}" {{ $attributes->merge(['class' => 'form-workspace']) }}>
    @csrf
    @if (strtoupper((string) $method) !== 'POST')
        @method($method)
    @endif

    @if ($lede)
        <p class="form-lede">{{ $lede }}</p>
    @endif

    {{ $slot }}

    <div class="form-bar">
        <a href="{{ $cancel }}" class="form-btn form-btn--ghost">Cancel</a>
        {{ $actions ?? '' }}
        <button
            type="submit"
            class="form-btn form-btn--primary"
            @if ($submitName)
                name="{{ $submitName }}"
                value="{{ $submitValue }}"
            @endif
        >{{ $submit }}</button>
    </div>
</form>
