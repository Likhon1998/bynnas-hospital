@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $achievements = $member->achievementList();
@endphp

@section('title', $member->name.' | Leadership | '.$hospitalName)
@section('meta_description', $member->title.' at '.$hospitalName.($member->bio ? ' — '.$member->bio : ''))

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">{{ $member->roleName() }}</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>{{ $member->name }}</h1>
                    <p>{{ $member->title }}@if (filled($member->role_label)) · {{ $member->role_label }}@endif</p>
                </div>
                <div class="ch-clinics-hero__actions">
                    @if ($peers->isNotEmpty())
                        <a class="ch-btn ch-btn--solid" href="{{ route('about.leadership.role', $member->role) }}">All {{ $member->roleName() }}</a>
                    @endif
                    <a class="ch-btn" href="{{ route('about') }}#board">About us</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-leader-profile">
        <div class="ch-wrap ch-leader-profile__grid">
            <aside class="ch-leader-profile__aside">
                <div class="ch-leader-profile__photo">
                    @if (filled($member->photo))
                        <img src="{{ $member->photo }}" alt="{{ $member->name }}" referrerpolicy="no-referrer">
                    @else
                        <span aria-hidden="true">{{ $member->initials() }}</span>
                    @endif
                </div>
                @if (filled($member->email) || filled($member->phone))
                    <div class="ch-leader-profile__contact">
                        @if (filled($member->email))
                            <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                        @endif
                        @if (filled($member->phone))
                            <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}">{{ $member->phone }}</a>
                        @endif
                    </div>
                @endif
            </aside>

            <div class="ch-leader-profile__main">
                @if (filled($member->bio))
                    <div class="ch-leader-profile__block">
                        <p class="ch-find__kicker">About</p>
                        <h2 class="ch-sec__title">Profile</h2>
                        <p>{{ $member->bio }}</p>
                    </div>
                @endif

                @if ($achievements !== [])
                    <div class="ch-leader-profile__block">
                        <p class="ch-find__kicker">Highlights</p>
                        <h2 class="ch-sec__title">Achievements</h2>
                        <ul class="ch-leader-profile__list">
                            @foreach ($achievements as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($peers->isNotEmpty())
        <section class="ch-board-sec">
            <div class="ch-wrap">
                <div class="ch-sec__head">
                    <div>
                        <p class="ch-find__kicker">Same role</p>
                        <h2 class="ch-sec__title">Other {{ $member->roleName() }} profiles</h2>
                    </div>
                </div>
                <div class="ch-board">
                    @foreach ($peers as $peer)
                        <article class="ch-board__card">
                            <a class="ch-board__photo" href="{{ route('about.people.show', $peer) }}">
                                @if (filled($peer->photo))
                                    <img src="{{ $peer->photo }}" alt="{{ $peer->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                @else
                                    <span aria-hidden="true">{{ $peer->initials() }}</span>
                                @endif
                            </a>
                            <div class="ch-board__body">
                                <h3><a href="{{ route('about.people.show', $peer) }}">{{ $peer->name }}</a></h3>
                                <p class="ch-board__title">{{ $peer->title }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
