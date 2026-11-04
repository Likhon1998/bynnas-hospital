@php
    $specialtyLabel = $doctor->specialization ?: $doctor->department?->name ?: 'Consultant';
    $todaySession = $doctor->doctorSchedules
        ?->first(fn ($session) => $session->weekday === ($weekdayToday ?? ''));
    $todayHours = null;

    if ($todaySession) {
        $start = substr((string) $todaySession->start_time, 0, 5);
        $end = substr((string) $todaySession->end_time, 0, 5);
        if (preg_match('/^\d{2}:\d{2}$/', $start) && preg_match('/^\d{2}:\d{2}$/', $end)) {
            $todayHours = \Illuminate\Support\Carbon::createFromFormat('H:i', $start)->format('g:i A')
                .' – '
                .\Illuminate\Support\Carbon::createFromFormat('H:i', $end)->format('g:i A');
        }
    }
@endphp

<article class="ch-dir-card @if ($doctor->is_featured) is-featured @endif">
    <a class="ch-dir-card__profile" href="{{ route('doctors.show', $doctor) }}">
        <div class="ch-dir-card__media">
            @if ($doctor->photo)
                <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
            @else
                <span class="ch-dir-card__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
            @endif
            @if ($doctor->is_featured)
                <span class="ch-dir-card__badge">Featured</span>
            @endif
        </div>

        <div class="ch-dir-card__body">
            <p class="ch-dir-card__clinic">{{ $doctor->department?->name ?: $specialtyLabel }}</p>
            <h3>{{ $doctor->name }}</h3>
            <p class="ch-dir-card__title">{{ $doctor->designation ?: $specialtyLabel }}</p>

            @if (filled($doctor->qualifications))
                <p class="ch-dir-card__quals">{{ $doctor->qualifications }}</p>
            @endif

            <ul class="ch-dir-card__meta">
                @if ($doctor->experience_years)
                    <li>{{ $doctor->experience_years }} yrs experience</li>
                @endif
                @if ((float) $doctor->consultation_fee > 0)
                    <li>৳{{ number_format((float) $doctor->consultation_fee) }} consult</li>
                @endif
                @if ($todayHours)
                    <li>Today {{ $todayHours }}</li>
                @endif
            </ul>
        </div>
    </a>

    <div class="ch-dir-card__actions">
        <a class="ch-doc__btn ch-doc__btn--ghost" href="{{ route('doctors.show', $doctor) }}">Profile</a>
        <a class="ch-doc__btn ch-doc__btn--solid" href="{{ route('home', ['doctor' => $doctor->id]) }}#appointment">Book</a>
    </div>
</article>
