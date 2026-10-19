@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $hoursLabel = $workingHours['saturday'] ?? ($workingHours['emergency'] ?? '24/7 Emergency');
@endphp

<div class="hidden border-b border-white/10 bg-navy-950 text-white/80 lg:block">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-2 text-xs">
        <div class="flex flex-wrap items-center gap-5">
            @if ($hospital->get('emergency_phone'))
                <a href="tel:{{ preg_replace('/\s+/', '', $hospital->get('emergency_phone')) }}" class="inline-flex items-center gap-2 font-semibold text-gold-500">
                    <x-icon name="phone" class="h-3.5 w-3.5" />
                    Emergency {{ $hospital->get('emergency_phone') }}
                </a>
            @endif
            @if ($hospital->get('phone'))
                <a href="tel:{{ preg_replace('/\s+/', '', $hospital->get('phone')) }}" class="inline-flex items-center gap-2 hover:text-white">
                    <x-icon name="phone" class="h-3.5 w-3.5" />
                    {{ $hospital->get('phone') }}
                </a>
            @endif
            @if ($hospital->get('email'))
                <a href="mailto:{{ $hospital->get('email') }}" class="inline-flex items-center gap-2 hover:text-white">
                    <x-icon name="mail" class="h-3.5 w-3.5" />
                    {{ $hospital->get('email') }}
                </a>
            @endif
        </div>
        <p class="inline-flex items-center gap-2">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            {{ $hoursLabel }}
            @if (! empty($workingHours['emergency']))
                <span class="text-white/40">·</span>
                Emergency {{ $workingHours['emergency'] }}
            @endif
        </p>
    </div>
</div>
