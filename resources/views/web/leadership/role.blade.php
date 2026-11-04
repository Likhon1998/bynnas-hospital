@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $isPair = $members->count() === 2;
    $isSolo = $members->count() === 1;
    $solo = $isSolo ? $members->first() : null;
    $heroTitle = $solo?->name ?: $roleTitle;
    $heroLede = $solo
        ? trim(($solo->title ?: $roleTitle).($solo->role_label ? ' · '.$solo->role_label : ''))
        : ($isPair
            ? 'Both '.$roleTitle.' profiles for '.$hospitalName.'.'
            : $roleTitle.' at '.$hospitalName.'.');
@endphp

@section('title', ($solo?->name ?: $roleTitle).' | Leadership | '.$hospitalName)
@section('meta_description', $solo
    ? ($solo->name.' — '.($solo->title ?: $roleTitle).' at '.$hospitalName)
    : ($roleTitle.' at '.$hospitalName.' — people who guide the campus.'))

@section('content')
    <section class="ch-lead-hero">
        <div class="ch-wrap">
            <nav class="ch-lead-hero__crumb" aria-label="Breadcrumb">
                <a href="{{ route('about') }}">About us</a>
                <span aria-hidden="true">/</span>
                <span>{{ $roleTitle }}</span>
            </nav>

            <div class="ch-lead-hero__row">
                <div>
                    <p class="ch-find__kicker">{{ $roleTitle }}</p>
                    <h1>{{ $heroTitle }}</h1>
                    <p>{{ $heroLede }}</p>
                </div>
                <div class="ch-lead-hero__actions">
                    <a class="ch-btn" href="{{ route('about') }}">All guardians</a>
                    <a class="ch-btn ch-btn--solid" href="{{ route('home') }}#appointment">Book appointment</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-lead-stage-sec">
        <div class="ch-wrap">
            @if ($members->isEmpty())
                <div class="ch-lead-empty">
                    <p class="ch-find__kicker">Leadership</p>
                    <h2>No {{ strtolower($roleTitle) }} published yet</h2>
                    <p>When the desk adds this role, the profile will appear here.</p>
                    <a class="ch-btn ch-btn--solid" href="{{ route('about') }}">Back to About us</a>
                </div>
            @else
                <div @class([
                    'ch-lead-stage',
                    'ch-lead-stage--solo' => $isSolo,
                    'ch-lead-stage--pair' => $isPair,
                    'ch-lead-stage--grid' => ! $isSolo && ! $isPair,
                ])>
                    @foreach ($members as $member)
                        @php
                            $enterFrom = $loop->index % 2 === 0 ? 'left' : 'right';
                            $delay = min($loop->index * 0.14, 0.42);
                            $achievements = $member->achievementList();
                        @endphp
                        <article
                            class="ch-lead-profile ch-lead-profile--enter-{{ $enterFrom }}"
                            style="--lead-delay: {{ $delay }}s"
                            id="guardian-{{ $member->slug }}"
                        >
                            <div class="ch-lead-profile__media">
                                <div class="ch-lead-profile__photo">
                                    @if (filled($member->photo))
                                        <img src="{{ $member->photo }}" alt="{{ $member->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                    @else
                                        <span aria-hidden="true">{{ $member->initials() }}</span>
                                    @endif
                                </div>
                                <p class="ch-lead-profile__role-chip">{{ $member->roleName() }}</p>
                            </div>

                            <div class="ch-lead-profile__body">
                                @unless ($isSolo)
                                    <p class="ch-lead-profile__label">{{ $member->role_label ?: $member->roleName() }}</p>
                                    <h2>{{ $member->name }}</h2>
                                    <p class="ch-lead-profile__title">{{ $member->title }}</p>
                                @else
                                    <p class="ch-lead-profile__label">{{ $member->role_label ?: 'Leadership' }}</p>
                                    <h2 class="ch-lead-profile__name-echo">{{ $member->title }}</h2>
                                @endunless

                                @if (filled($member->bio))
                                    <div class="ch-lead-profile__block">
                                        <h3>About</h3>
                                        <p>{{ $member->bio }}</p>
                                    </div>
                                @endif

                                @if ($achievements !== [])
                                    <div class="ch-lead-profile__block">
                                        <h3>Achievements</h3>
                                        <ul class="ch-lead-profile__list">
                                            @foreach ($achievements as $item)
                                                <li>
                                                    <span class="ch-lead-profile__mark" aria-hidden="true"></span>
                                                    <span>{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (filled($member->email) || filled($member->phone))
                                    <div class="ch-lead-profile__contact">
                                        @if (filled($member->email))
                                            <a class="ch-lead-profile__contact-link" href="mailto:{{ $member->email }}">
                                                <span>Email</span>
                                                <strong>{{ $member->email }}</strong>
                                            </a>
                                        @endif
                                        @if (filled($member->phone))
                                            <a class="ch-lead-profile__contact-link" href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}">
                                                <span>Phone</span>
                                                <strong>{{ $member->phone }}</strong>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="ch-lead-footer">
        <div class="ch-wrap ch-lead-footer__row">
            <div>
                <p class="ch-find__kicker">Campus</p>
                <h2 class="ch-sec__title">Continue exploring {{ $hospitalName }}</h2>
                <p class="ch-depts-intro">Return to the guardians list, or book a consultant visit at the desk.</p>
            </div>
            <div class="ch-lead-footer__actions">
                <a class="ch-btn ch-btn--solid" href="{{ route('about') }}">About us</a>
                <a class="ch-btn" href="{{ route('doctors') }}">Find a doctor</a>
            </div>
        </div>
    </section>
@endsection
