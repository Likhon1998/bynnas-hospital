@php
    $hoursLabel = $workingHours['saturday'] ?? ($workingHours['emergency'] ?? '24/7 Emergency');
@endphp

<div class="hidden border-b border-[#0f2a26] bg-[#16332e] text-[#d7e4df] lg:block">
    <div class="web-wrap flex items-center justify-between gap-6 py-2 text-xs">
        <div class="flex min-w-0 flex-wrap items-center gap-x-5 gap-y-1">
            @if ($hospital->get('emergency_phone'))
                <a href="tel:{{ preg_replace('/\s+/', '', $hospital->get('emergency_phone')) }}" class="inline-flex items-center gap-2 font-semibold text-[#ffe08a]">
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
                <a href="mailto:{{ $hospital->get('email') }}" class="inline-flex min-w-0 items-center gap-2 hover:text-white">
                    <x-icon name="mail" class="h-3.5 w-3.5 shrink-0" />
                    <span class="truncate">{{ $hospital->get('email') }}</span>
                </a>
            @endif
        </div>
        <p class="inline-flex shrink-0 items-center gap-2">
            <x-icon name="clock" class="h-3.5 w-3.5" />
            {{ $hoursLabel }}
            @if (! empty($workingHours['emergency']))
                <span class="text-white/40">·</span>
                Emergency {{ $workingHours['emergency'] }}
            @endif
        </p>
    </div>
</div>
