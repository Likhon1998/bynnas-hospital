@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $tagline = $hospital->get('tagline') ?: 'Consultant clinics and emergency care in Dhaka';
    $aboutContent = $hospital->get('about_content')
        ?: 'We run consultant clinics, a diagnostic floor, and an emergency desk that stays open at night.';
    $phone = $hospital->get('phone') ?: $hospital->get('emergency_phone');
    $address = $hospital->get('address') ?: 'Main Campus';
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

    @if (($achievements ?? collect())->isNotEmpty())
        <section class="ch-achieve-sec" id="achievements">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Milestones</p>
                        <h2 class="ch-sec__title">Our achievements</h2>
                        <p class="ch-depts-intro">Campus milestones published by the hospital desk — updated as the story grows.</p>
                    </div>
                </div>
                <ol class="ch-achieve">
                    @foreach ($achievements as $achievement)
                        <li class="ch-achieve__item">
                            @if (filled($achievement->year))
                                <span class="ch-achieve__year">{{ $achievement->year }}</span>
                            @endif
                            <div class="ch-achieve__body">
                                <h3>{{ $achievement->title }}</h3>
                                @if (filled($achievement->description))
                                    <p>{{ $achievement->description }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if (($boardMembers ?? collect())->isNotEmpty())
        <section class="ch-board-sec" id="board" data-guardian-board>
            <div class="ch-wrap">
                <div class="ch-sec__head ch-board-sec__head">
                    <div>
                        <p class="ch-find__kicker">Guardians</p>
                        <h2 class="ch-sec__title">People who guide the campus</h2>
                        <p class="ch-depts-intro">Open the guardians menu to highlight a role, or use About us in the top nav for Founder, Co-founder, Chairman, and more.</p>
                    </div>
                </div>

                <div class="ch-guardian-bar">
                    <div class="ch-guardian-menu" data-nav-dropdown data-guardian-menu>
                        <button
                            type="button"
                            class="ch-nav__trigger ch-guardian-menu__trigger"
                            data-nav-dropdown-trigger
                            data-guardian-trigger
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <span data-guardian-trigger-label>All guardians</span>
                            <svg class="ch-nav__caret" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="ch-nav__menu ch-guardian-menu__panel" data-nav-dropdown-panel data-guardian-panel hidden>
                            <button type="button" class="is-active" data-guardian-option data-role="" data-role-label="All guardians">
                                All guardians
                            </button>
                            @foreach (($rolePages ?? collect()) as $roleKey => $page)
                                <button
                                    type="button"
                                    data-guardian-option
                                    data-role="{{ $roleKey }}"
                                    data-role-label="{{ $page['label'] }}"
                                    data-role-url="{{ route('about.leadership.role', $roleKey) }}"
                                    @class(['is-active' => request('guardian') === $roleKey])
                                >
                                    {{ $page['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="ch-guardian-bar__aside">
                        <p class="ch-guardian-bar__status" id="guardian-status" data-guardian-status>Showing all guardians</p>
                        <a
                            class="ch-btn ch-btn--solid"
                            href="{{ route('about') }}#board"
                            data-guardian-role-link
                            hidden
                        >View profile</a>
                    </div>
                </div>

                <div class="ch-board" id="guardian-grid" data-guardian-grid>
                    @foreach ($boardMembers as $member)
                        <article
                            class="ch-board__card"
                            data-guardian-card
                            data-guardian-role="{{ $member->role }}"
                            data-guardian-label="{{ $member->roleName() }}"
                        >
                            <a class="ch-board__photo" href="{{ route('about.leadership.role', $member->role) }}">
                                @if (filled($member->photo))
                                    <img src="{{ $member->photo }}" alt="{{ $member->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                @else
                                    <span aria-hidden="true">{{ $member->initials() }}</span>
                                @endif
                            </a>
                            <div class="ch-board__body">
                                <p class="ch-board__label">{{ $member->role_label ?: $member->roleName() }}</p>
                                <h3><a href="{{ route('about.leadership.role', $member->role) }}">{{ $member->name }}</a></h3>
                                <p class="ch-board__title">{{ $member->title }}</p>
                                @if (filled($member->bio))
                                    <p class="ch-board__bio">{{ $member->bio }}</p>
                                @endif
                                @if ($member->achievementList() !== [])
                                    <ul class="ch-board__achievements">
                                        @foreach (array_slice($member->achievementList(), 0, 2) as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <p class="ch-board__more">
                                    <a href="{{ route('about.leadership.role', $member->role) }}">View {{ $member->roleName() }}</a>
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
