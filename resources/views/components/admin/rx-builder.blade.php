@props([
    'value' => null,
])

@php
    $lines = old('rx_lines', \App\Support\ClinicalNoteOptions::hydrate('prescriptions', $value)['selected']);
    $lines = is_array($lines) ? array_values(array_filter($lines)) : [];
@endphp

<div class="rx-builder" data-rx-builder>
    <div class="rx-builder__head">
        <h3>Medicines</h3>
        <p>Same drug, different dose for each patient — pick medicine, then how to take it</p>
    </div>

    <div class="rx-builder__compose">
        <label class="rx-builder__field">
            <span>Medicine</span>
            <select class="admin-field" data-rx-medicine>
                <option value="" disabled selected>Choose medicine…</option>
                @foreach (\App\Support\ClinicalNoteOptions::medicines() as $medicine)
                    <option value="{{ $medicine }}">{{ $medicine }}</option>
                @endforeach
            </select>
        </label>
        <label class="rx-builder__field">
            <span>Dose</span>
            <select class="admin-field" data-rx-dose>
                <option value="" disabled selected>Dose…</option>
                @foreach (\App\Support\ClinicalNoteOptions::doses() as $dose)
                    <option value="{{ $dose }}">{{ $dose }}</option>
                @endforeach
            </select>
        </label>
        <label class="rx-builder__field">
            <span>How often</span>
            <select class="admin-field" data-rx-frequency>
                <option value="" disabled selected>Frequency…</option>
                @foreach (\App\Support\ClinicalNoteOptions::frequencies() as $frequency)
                    <option value="{{ $frequency }}">{{ $frequency }}</option>
                @endforeach
            </select>
        </label>
        <label class="rx-builder__field">
            <span>For how long</span>
            <select class="admin-field" data-rx-duration>
                <option value="" disabled selected>Duration…</option>
                @foreach (\App\Support\ClinicalNoteOptions::durations() as $duration)
                    <option value="{{ $duration }}">{{ $duration }}</option>
                @endforeach
            </select>
        </label>
        <button type="button" class="rx-builder__add" data-rx-add>Add to Rx</button>
    </div>

    <p class="rx-builder__error" data-rx-error hidden>Choose medicine, dose, frequency, and duration first.</p>

    <ul class="rx-builder__list" data-rx-list @if ($lines === []) hidden @endif>
        @foreach ($lines as $line)
            <li class="rx-builder__item">
                <input type="hidden" name="rx_lines[]" value="{{ $line }}">
                <span>{{ $line }}</span>
                <button type="button" class="rx-builder__remove" data-rx-remove aria-label="Remove">Remove</button>
            </li>
        @endforeach
    </ul>

    <p class="rx-builder__empty" data-rx-empty @if ($lines !== []) hidden @endif>No medicines added yet.</p>
</div>
