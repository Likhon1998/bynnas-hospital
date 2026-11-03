@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $tagline = $hospital->get('tagline') ?: 'Compassionate care for every family';
    $aboutContent = $hospital->get('about_content')
        ?: 'We run consultant clinics, a diagnostic floor, and an emergency desk that stays open at night.';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $emergency = $hospital->get('emergency_phone') ?: $phone;
    $address = $hospital->get('address') ?: 'Main Campus';
    $email = $hospital->get('email');
    $clinicHours = $workingHours['saturday'] ?? $workingHours['monday'] ?? '9:00 AM – 9:00 PM';
    $emergencyHours = $workingHours['emergency'] ?? '24/7';
    $storyImage = 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&q=80';
    $values = [
        [
            'icon' => 'stethoscope',
            'title' => 'Named consultants',
            'text' => 'You book a real doctor from the roster — not a placeholder. The same name stays on your visit from clinic to follow-up.',
        ],
        [
            'icon' => 'flask',
            'title' => 'Care on one campus',
            'text' => 'Clinics, diagnostics, and the emergency desk sit together. Reports should not travel across town if we can help it.',
        ],
        [
            'icon' => 'siren',
            'title' => 'Emergency never closes',
            'text' => 'The night desk does not close on Friday. If it is urgent, come in. Do not wait for OPD hours.',
        ],
    ];
@endphp

@section('title', 'About us | '.$hospitalName)
@section('meta_description', 'How '.$hospitalName.' works, who practises here, and how to reach the desk.')

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">About the hospital</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>About us</h1>
                    <p>{{ $tagline }}. Families come here for a named consultant, a blood test, or because something cannot wait until morning.</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn ch-btn--solid" href="{{ route('home') }}#find-doctor">Find a doctor</a>
                    <a class="ch-btn" href="{{ route('home') }}#appointment">Book appointment</a>
                </div>
            </div>
            <p class="ch-clinics-hero__count">
                {{ $stats['doctors'] }} {{ \Illuminate\Support\Str::plural('consultant', $stats['doctors']) }}
                · {{ $stats['departments'] }} {{ \Illuminate\Support\Str::plural('specialty', $stats['departments']) }}
                · Emergency {{ $emergencyHours }}
            </p>
        </div>
    </section>

    <section class="ch-about-story">
        <div class="ch-wrap ch-about-story__grid">
            <div class="ch-about-story__copy">
                <p class="ch-find__kicker">Our campus</p>
                <h2 class="ch-sec__title">Our story</h2>
                <p>{{ $aboutContent }}</p>
                <p>Reception is on the ground floor. If you have old papers, bring them — it often saves a second visit.</p>
                <div class="ch-profile__cta">
                    <a class="ch-btn ch-btn--solid" href="{{ route('home') }}#appointment">Request a visit</a>
                    @if ($phone)
                        <a class="ch-btn" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">Call {{ $phone }}</a>
                    @endif
                </div>
            </div>
            <figure class="ch-about-story__media">
                <img src="{{ $storyImage }}" alt="{{ $hospitalName }} campus" referrerpolicy="no-referrer">
                <figcaption>
                    <strong>Main campus</strong>
                    <span>{{ $address }}</span>
                </figcaption>
            </figure>
        </div>
    </section>

    <section class="ch-about-values">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">How we work</p>
                    <h2 class="ch-sec__title">Care you can follow</h2>
                    <p class="ch-depts-intro">Simple rules the desk tries to keep every day.</p>
                </div>
            </div>
            <div class="ch-values">
                @foreach ($values as $value)
                    <article class="ch-value">
                        <span class="ch-value__icon"><x-icon :name="$value['icon']" /></span>
                        <h3>{{ $value['title'] }}</h3>
                        <p>{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-web.campus-band
        :stats="$stats"
        :headline="$hospitalName"
        :note="$stats['summary']"
        :cta-href="route('home').'#appointment'"
    />

    @if (($boardMembers ?? collect())->isNotEmpty())
        <section class="ch-board-sec" id="board">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Leadership</p>
                        <h2 class="ch-sec__title">Board of directors</h2>
                        <p class="ch-depts-intro">The people who guide {{ $hospitalName }} — governance, clinical standards, and campus operations.</p>
                    </div>
                </div>
                <div class="ch-board">
                    @foreach ($boardMembers as $member)
                        <article class="ch-board__card">
                            <div class="ch-board__photo">
                                @if (filled($member->photo))
                                    <img src="{{ $member->photo }}" alt="{{ $member->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                @else
                                    <span aria-hidden="true">{{ $member->initials() }}</span>
                                @endif
                            </div>
                            <div class="ch-board__body">
                                @if (filled($member->role_label))
                                    <p class="ch-board__label">{{ $member->role_label }}</p>
                                @endif
                                <h3>{{ $member->name }}</h3>
                                <p class="ch-board__title">{{ $member->title }}</p>
                                @if (filled($member->bio))
                                    <p class="ch-board__bio">{{ $member->bio }}</p>
                                @endif
                                @if (filled($member->email) || filled($member->phone))
                                    <p class="ch-board__contact">
                                        @if (filled($member->email))
                                            <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                                        @endif
                                        @if (filled($member->phone))
                                            <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}">{{ $member->phone }}</a>
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($doctors->isNotEmpty())
        <section class="ch-docs-sec">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">The people you meet</p>
                        <h2 class="ch-sec__title">Consultants on the roster</h2>
                        <p class="ch-depts-intro">Open a profile to see clinic hours, then book a free slot.</p>
                    </div>
                    <a class="ch-more" href="{{ route('doctors') }}">View all Doctors →</a>
                </div>
                <div class="ch-docs ch-docs--all">
                    @foreach ($doctors as $doctor)
                        <article class="ch-doc">
                            <a class="ch-doc__profile" href="{{ route('doctors.show', $doctor) }}">
                                @if ($doctor->photo)
                                    <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}">
                                @else
                                    <span class="ch-doc__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
                                @endif
                                <strong>{{ $doctor->name }}</strong>
                                <em>{{ $doctor->specialization ?: $doctor->department?->name ?: 'Consultant' }}</em>
                            </a>
                            <div class="ch-doc__actions">
                                <a class="ch-doc__book" href="{{ route('doctors.show', $doctor) }}">Profile</a>
                                <a class="ch-doc__book" href="{{ route('home', ['doctor' => $doctor->id]) }}#appointment">Book</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($departments->isNotEmpty())
        <section class="ch-depts-sec">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Specialist clinics</p>
                        <h2 class="ch-sec__title">Departments in the building</h2>
                        <p class="ch-depts-intro">Choose a clinic to see the doctors you can book.</p>
                    </div>
                    <a class="ch-more" href="{{ route('departments') }}">View all Departments →</a>
                </div>
                <div class="ch-depts">
                    @foreach ($departments as $department)
                        <a class="ch-dept" href="{{ route('doctors', ['specialty' => $department->name]) }}" title="{{ $department->description ?: $department->name }}">
                            <span class="ch-dept__icon"><x-icon :name="$department->icon ?: 'hospital'" /></span>
                            <strong>{{ $department->name }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="ch-about-visit">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">Before you arrive</p>
                    <h2 class="ch-sec__title">Visit the campus</h2>
                </div>
                <a class="ch-more" href="{{ route('emergency') }}">Emergency desk →</a>
            </div>
            <div class="ch-visit">
                <article class="ch-visit__card">
                    <span class="ch-visit__icon"><x-icon name="pin" /></span>
                    <h3>Address</h3>
                    <p>{{ $address }}</p>
                </article>
                <article class="ch-visit__card">
                    <span class="ch-visit__icon"><x-icon name="clock" /></span>
                    <h3>Clinic hours</h3>
                    <p>{{ $clinicHours }}</p>
                    <p>Friday: {{ $workingHours['friday'] ?? 'Emergency only' }}</p>
                </article>
                <article class="ch-visit__card ch-visit__card--urgent">
                    <span class="ch-visit__icon"><x-icon name="ambulance" /></span>
                    <h3>Emergency</h3>
                    <p>Open {{ $emergencyHours }}.</p>
                    @if ($emergency)
                        <a href="tel:{{ preg_replace('/\s+/', '', $emergency) }}">{{ $emergency }}</a>
                    @endif
                </article>
                <article class="ch-visit__card">
                    <span class="ch-visit__icon"><x-icon name="phone" /></span>
                    <h3>Desk</h3>
                    @if ($phone)
                        <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
                    @endif
                    @if ($email)
                        <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                    @endif
                </article>
            </div>
        </div>
    </section>
@endsection
