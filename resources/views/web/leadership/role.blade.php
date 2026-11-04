@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $isPair = $members->count() === 2;
    $isSolo = $members->count() === 1;
@endphp

@section('title', $roleTitle.' | Leadership | '.$hospitalName)
@section('meta_description', $roleTitle.' at '.$hospitalName.' — people who guide the campus.')

@section('content')
    <section class="ch-clinics-hero">
        <div class="ch-wrap">
            <p class="ch-find__kicker">{{ $roleTitle }}</p>
            <div class="ch-clinics-hero__row">
                <div>
                    <h1>{{ $roleTitle }}</h1>
                    <p>
                        @if ($isSolo)
                            Profile for the {{ strtolower($roleTitle) }} at {{ $hospitalName }}.
                        @elseif ($isPair)
                            Both {{ strtolower($roleTitle) }} profiles on one page.
                        @else
                            {{ $roleTitle }} profiles for {{ $hospitalName }}.
                        @endif
                    </p>
                </div>
                <div class="ch-clinics-hero__actions">
                    <a class="ch-btn ch-btn--solid" href="{{ route('about') }}#board">Back to About us</a>
                    <a class="ch-btn" href="{{ route('home') }}#appointment">Book appointment</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ch-lead-stage-sec">
        <div class="ch-wrap">
            @if ($members->isEmpty())
                <p class="ch-depts-intro">No published {{ strtolower($roleTitle) }} profiles yet.</p>
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
                            $delay = min($loop->index * 0.12, 0.48);
                            $achievements = $member->achievementList();
                        @endphp
                        <article
                            class="ch-lead-profile ch-lead-profile--full ch-lead-profile--enter-{{ $enterFrom }}"
                            style="--lead-delay: {{ $delay }}s"
                            id="guardian-{{ $member->slug }}"
                        >
                            <div class="ch-lead-profile__photo">
                                @if (filled($member->photo))
                                    <img src="{{ $member->photo }}" alt="{{ $member->name }}" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                @else
                                    <span aria-hidden="true">{{ $member->initials() }}</span>
                                @endif
                            </div>
                            <div class="ch-lead-profile__body">
                                <p class="ch-lead-profile__label">{{ $member->role_label ?: $member->roleName() }}</p>
                                <h2>{{ $member->name }}</h2>
                                <p class="ch-lead-profile__title">{{ $member->title }}</p>

                                @if (filled($member->bio))
                                    <div class="ch-lead-profile__block">
                                        <p class="ch-find__kicker">About</p>
                                        <p class="ch-lead-profile__bio">{{ $member->bio }}</p>
                                    </div>
                                @endif

                                @if ($achievements !== [])
                                    <div class="ch-lead-profile__block">
                                        <p class="ch-find__kicker">Achievements</p>
                                        <ul class="ch-lead-profile__list">
                                            @foreach ($achievements as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (filled($member->email) || filled($member->phone))
                                    <div class="ch-lead-profile__contact">
                                        @if (filled($member->email))
                                            <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                                        @endif
                                        @if (filled($member->phone))
                                            <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}">{{ $member->phone }}</a>
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
@endsection
