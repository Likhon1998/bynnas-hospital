@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'required' => false,
])

@php
    $hydrated = \App\Support\ClinicalNoteOptions::hydrate($name, old($name, $value));
    $selected = old($name.'_options', $hydrated['selected']);
    $selected = is_array($selected) ? array_values(array_filter($selected)) : [];
    $other = old($name.'_other', $hydrated['other']);
    $optionList = array_values(array_unique([...$options, ...$selected]));
    $showOther = filled($other);
@endphp

<div class="clinical-pick" data-clinical-pick data-field="{{ $name }}">
    <div class="clinical-pick__head">
        <label class="clinical-pick__label" for="{{ $name }}_add">
            {{ $label }}
            @if ($required)<span class="clinical-pick__req">*</span>@endif
        </label>
        <button type="button" class="clinical-pick__custom-toggle" data-clinical-pick-custom-toggle @if ($showOther) hidden @endif>+ Custom</button>
    </div>

    <select id="{{ $name }}_add" class="admin-field clinical-pick__select" data-clinical-pick-add aria-label="Add {{ strtolower($label) }}">
        <option value="" disabled selected>Choose…</option>
        @foreach ($optionList as $option)
            <option value="{{ $option }}">{{ $option }}</option>
        @endforeach
    </select>

    <ul class="clinical-pick__chips" data-clinical-pick-chips @if ($selected === []) hidden @endif>
        @foreach ($selected as $item)
            <li class="clinical-pick__chip">
                <input type="hidden" name="{{ $name }}_options[]" value="{{ $item }}">
                <span>{{ $item }}</span>
                <button type="button" class="clinical-pick__remove" data-clinical-pick-remove aria-label="Remove">×</button>
            </li>
        @endforeach
    </ul>

    <input
        type="text"
        name="{{ $name }}_other"
        value="{{ $other }}"
        class="admin-field clinical-pick__other"
        data-clinical-pick-other
        placeholder="Type custom {{ strtolower($label) }}"
        autocomplete="off"
        @if (! $showOther) hidden @endif
    >
</div>
