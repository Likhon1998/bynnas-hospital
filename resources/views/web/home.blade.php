@extends('layouts.public')

@section('title', ($hospital->get('hospital_name') ?: 'Hospital').' | '.($hospital->get('tagline') ?: 'Consultant care in Dhaka'))
@section('meta_description', $hospital->get('tagline') ?: 'Consultants, diagnostics, and emergency care in Dhaka.')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $bookableList = isset($bookableServices) && $bookableServices->isNotEmpty() ? $bookableServices : $services;
    $galleryItems = $galleryItems ?? collect();
    $posts = $posts ?? collect();
    $notices = $notices ?? collect();
    $doctorTalks = $doctorTalks ?? collect();
    $heroTitle = $heroBanner?->title ?: 'Care you can count on.';
    $heroLead = $heroBanner?->subtitle ?: 'Named consultants. Clear next steps.';
    $heroCtaLabel = $heroBanner?->cta_label ?: 'Book a visit';
    $heroCtaUrl = $heroBanner?->destination() ?: route('home', ['book' => 1]);
    $campusAddress = $hospital->get('address') ?: 'Main Campus';
    $campusLabel = filled($hospital->get('campus_name'))
        ? $hospital->get('campus_name')
        : 'Main Campus';
    $mapEmbed = trim((string) $hospital->get('map_embed'));
    $gallerySlides = $galleryItems
        ->map(function ($item) {
            $image = \App\Support\PublicPhoto::usable($item->image);

            if ($image === null) {
                return null;
            }

            return [
                'image' => $image,
                'title' => $item->title,
                'caption' => $item->caption,
            ];
        })
        ->filter()
        ->values();

    if ($gallerySlides->isEmpty() && $notices->isNotEmpty()) {
        $gallerySlides = collect(\App\Support\PublicPhoto::CAMPUS)
            ->take(4)
            ->values()
            ->map(fn (string $image, int $index) => [
                'image' => $image,
                'title' => $hospitalName,
                'caption' => $index === 0 ? 'Campus life' : null,
            ]);
    }

    $selectedVisitDate = old('date');
    $selectedVisitTime = old('time');
    $selectedVisitLabel = ($selectedVisitDate && $selectedVisitTime)
        ? \Illuminate\Support\Carbon::parse($selectedVisitDate)->format('D, j M Y').' · '.$selectedVisitTime
        : '';
    $bookingPanelOpen = $errors->any() || filled(old('name')) || filled(old('phone')) || filled(old('doctor'));
@endphp

@section('content')
    <section class="ch-hero" @if ($heroBanner?->image) style="--ch-hero-image: url('{{ $heroBanner->image }}')" @endif>
        <div class="ch-wrap ch-hero__grid">
            <div class="ch-hero__copy">
                <h1>{{ $heroTitle }}</h1>
                <p class="ch-hero__lead">{{ $heroLead }}</p>
                <div class="ch-hero__actions">
                    <a
                        class="ch-more ch-hero__cta-link"
                        href="{{ $heroCtaUrl }}"
                        @if (\Illuminate\Support\Str::contains($heroCtaUrl, 'book=1') || \Illuminate\Support\Str::contains($heroCtaUrl, '#appointment')) data-book-open @endif
                    >
                        {{ $heroCtaLabel }} →
                    </a>
                </div>
            </div>

            <div @class(['ch-hero__card', 'is-open' => $bookingPanelOpen]) data-book-panel>
                <form id="appointment" class="ch-card" method="POST" action="{{ route('appointment-requests.store') }}" data-appointment-slots="{{ route('appointment-slots') }}" data-appointment-schedule="{{ route('appointment-schedule') }}">
                    @csrf
                    <div class="ch-card__head">
                        <h2>Book an Appointment</h2>
                        <button
                            type="button"
                            class="ch-hero__book-close"
                            data-book-close
                            aria-label="Close booking form"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="ch-card__body">
                    @if (session('status'))
                        <p class="ch-card__flash" role="status">{{ session('status') }}</p>
                    @endif
                    <div class="ch-field-row">
                        <label class="ch-field">
                            <span>Your name</span>
                            <input class="ch-input" type="text" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" placeholder="Full name">
                            @error('name')
                                <span class="ch-error">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="ch-field">
                            <span>Phone</span>
                            <input class="ch-input" type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel" placeholder="Phone number">
                            @error('phone')
                                <span class="ch-error">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>
                    <label class="ch-field">
                        <span>Find a Doctor</span>
                        <select class="ch-input" name="doctor" required>
                            <option value="">Select doctor</option>
                            @foreach ($directoryDoctors as $doctor)
                                <option value="{{ $doctor->id }}" data-department="{{ $doctor->department_id }}" @selected((string) old('doctor') === (string) $doctor->id)>{{ $doctor->name }} — {{ $doctor->specialization ?: $doctor->department?->name }}</option>
                            @endforeach
                        </select>
                        @error('doctor')
                            <span class="ch-error">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="ch-field">
                        <span>Select Service</span>
                        <select class="ch-input" name="service" required>
                            <option value="">Choose a doctor first</option>
                            @foreach ($bookableList as $service)
                                <option value="{{ $service->slug }}" data-department="{{ $service->department_id }}" @selected(old('service') === $service->slug)>{{ $service->title }}</option>
                            @endforeach
                        </select>
                        @error('service')
                            <span class="ch-error">{{ $message }}</span>
                        @enderror
                    </label>
                    <div class="ch-field">
                        <span>Clinic schedule</span>
                        <button
                            type="button"
                            class="ch-cal-open{{ $selectedVisitLabel !== '' ? ' is-picked' : '' }}"
                            data-cal-open
                            aria-haspopup="dialog"
                            aria-expanded="false"
                        >
                            <svg class="ch-cal-open__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <rect x="3.2" y="4.5" width="13.6" height="12.3" rx="2.2" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M3.2 8.2h13.6M7 3.2v2.6M13 3.2v2.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                            <span class="ch-cal-open__copy" data-cal-pick data-cal-empty="Choose from clinic schedule">{{ $selectedVisitLabel !== '' ? $selectedVisitLabel : 'Choose from clinic schedule' }}</span>
                            <svg class="ch-cal-open__chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <input type="hidden" name="date" value="{{ $selectedVisitDate }}">
                        <input type="hidden" name="time" value="{{ $selectedVisitTime }}">
                        @error('date')
                            <span class="ch-error">{{ $message }}</span>
                        @enderror
                        @error('time')
                            <span class="ch-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <p class="ch-slot-note" data-slot-note hidden></p>
                    <button type="submit" class="ch-btn ch-btn--solid ch-btn--wide">Book Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @if ($notices->isNotEmpty() || $gallerySlides->isNotEmpty())
        <section id="notices" class="ch-notices-sec" aria-label="Gallery and hospital notices">
            <div class="ch-wrap">
                <div class="ch-spotlight">
                    <div class="ch-spotlight__panel ch-spotlight__panel--gallery">
                        <div class="ch-spotlight__head">
                            <h2 class="ch-sec__title">Gallery</h2>
                        </div>
                        <div
                            class="ch-gallery-slider"
                            data-gallery-slider
                            @if ($gallerySlides->count() > 1) data-autoplay="5000" @endif
                        >
                            <div class="ch-gallery-slider__viewport">
                                <div class="ch-gallery-slider__track" data-gallery-track>
                                    @foreach ($gallerySlides as $slide)
                                        <figure class="ch-gallery-slider__slide" data-gallery-slide @if (! $loop->first) aria-hidden="true" @endif>
                                            <img
                                                src="{{ $slide['image'] }}"
                                                alt="{{ $slide['title'] ?: $hospitalName }}"
                                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                            >
                                            @if (filled($slide['title']) || filled($slide['caption']))
                                                <figcaption class="ch-gallery-slider__caption">
                                                    @if (filled($slide['title']))
                                                        <strong>{{ $slide['title'] }}</strong>
                                                    @endif
                                                    @if (filled($slide['caption']))
                                                        <span>{{ $slide['caption'] }}</span>
                                                    @endif
                                                </figcaption>
                                            @endif
                                        </figure>
                                    @endforeach
                                </div>
                            </div>

                            @if ($gallerySlides->count() > 1)
                                <div class="ch-gallery-slider__controls">
                                    <button type="button" class="ch-gallery-slider__nav" data-gallery-prev aria-label="Previous gallery image">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M12.5 4.5 7 10l5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <div class="ch-gallery-slider__dots" data-gallery-dots role="tablist" aria-label="Gallery slides">
                                        @foreach ($gallerySlides as $slide)
                                            <button
                                                type="button"
                                                class="ch-gallery-slider__dot{{ $loop->first ? ' is-active' : '' }}"
                                                data-gallery-dot
                                                data-index="{{ $loop->index }}"
                                                aria-label="Show gallery image {{ $loop->iteration }}"
                                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                            ></button>
                                        @endforeach
                                    </div>
                                    <button type="button" class="ch-gallery-slider__nav" data-gallery-next aria-label="Next gallery image">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7.5 4.5 13 10l-5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="ch-spotlight__panel ch-spotlight__panel--notices">
                        <div class="ch-spotlight__head">
                            <h2 class="ch-sec__title">Notices</h2>
                            <a class="ch-more" href="{{ route('notices') }}">All notices →</a>
                        </div>
                        <div class="ch-notices ch-notices--stack" data-notice-slots="5">
                            @forelse ($notices->take(5) as $notice)
                                <a
                                    href="{{ route('notices.show', $notice) }}"
                                    @class(['ch-notice', 'ch-notice--stack', 'ch-notice--link', 'ch-notice--urgent' => $notice->is_urgent])
                                >
                                    <div class="ch-notice__meta">
                                        <time class="ch-notice__date" datetime="{{ $notice->published_on->toDateString() }}">
                                            {{ $notice->published_on->format('d M Y') }}
                                        </time>
                                        @if ($notice->is_urgent)
                                            <span class="ch-notice__tag">Urgent</span>
                                        @endif
                                    </div>
                                    <div class="ch-notice__body">
                                        <h3>{{ $notice->title }}</h3>
                                        @if (filled($notice->body))
                                            <p>{{ \Illuminate\Support\Str::limit($notice->body, 64) }}</p>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <p class="ch-spotlight__empty">No published notices right now.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section id="departments" class="ch-depts-sec">
        <div class="ch-wrap">
            <div class="ch-depts-sec__head">
                <div>
                    <h2 class="ch-sec__title">Major departments</h2>
                </div>
                <a class="ch-depts-sec__all" href="{{ route('departments') }}">All departments</a>
            </div>
            <div class="ch-depts">
                @forelse ($departments->take(8) as $department)
                    @php
                        $deptSummary = filled($department->description)
                            ? \Illuminate\Support\Str::limit(strip_tags((string) $department->description), 96)
                            : 'Specialist clinics and bookable consultants.';
                    @endphp
                    <a class="ch-dept" href="{{ route('departments.show', $department) }}">
                        <span class="ch-dept__icon" aria-hidden="true">
                            <x-icon :name="$department->icon ?: 'hospital'" />
                        </span>
                        <span class="ch-dept__body">
                            <strong>{{ $department->name }}</strong>
                            <span class="ch-dept__copy">{{ $deptSummary }}</span>
                        </span>
                        <span class="ch-dept__go">View department</span>
                    </a>
                @empty
                    <p class="ch-lead">No departments listed yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($doctorTalks->isNotEmpty())
        <section id="doctor-talks" class="ch-talks-sec" aria-label="Doctor's Talk">
            <div class="ch-wrap">
                <div class="ch-talks-sec__head">
                    <h2 class="ch-talks-sec__title">Doctor's Talk</h2>
                    <div class="ch-talks-sec__nav">
                        <button type="button" class="ch-talks-sec__arrow" data-talks-prev aria-label="Previous videos">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M12.5 4.5 7 10l5.5 5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" class="ch-talks-sec__arrow" data-talks-next aria-label="Next videos">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7.5 4.5 13 10l-5.5 5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
                <div class="ch-talks" data-talks-slider data-talk-slots="4">
                    <div class="ch-talks__track" data-talks-track>
                        @foreach ($doctorTalks as $talk)
                            @continue(! filled($talk->embedUrl()))
                            @php
                                $talkThumb = $talk->displayThumbnail();
                            @endphp
                            <button
                                type="button"
                                class="ch-talk"
                                data-youtube-open
                                data-youtube-src="{{ $talk->embedUrl() }}"
                                data-youtube-title="{{ $talk->title }}"
                            >
                                <span class="ch-talk__media">
                                    @if (filled($talkThumb))
                                        <img
                                            src="{{ $talkThumb }}"
                                            alt="{{ $talk->title }}"
                                            loading="lazy"
                                            referrerpolicy="no-referrer"
                                            data-talk-thumb
                                        >
                                    @endif
                                    <span class="ch-talk__fallback" aria-hidden="true">
                                        <svg viewBox="0 0 48 36" fill="none"><rect x="4" y="6" width="40" height="24" rx="4" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="18" r="1.6" fill="currentColor"/><circle cx="24" cy="18" r="1.6" fill="currentColor"/><circle cx="30" cy="18" r="1.6" fill="currentColor"/></svg>
                                    </span>
                                    <span class="ch-talk__veil" aria-hidden="true"></span>
                                    <span class="ch-talk__play" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 7.2v9.6L17.6 12 9 7.2Z"/></svg>
                                    </span>
                                </span>
                                <span class="ch-talk__caption">
                                    <strong>{{ $talk->title }}</strong>
                                    @if (filled($talk->doctor_name))
                                        <em>{{ $talk->doctor_name }}</em>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="ch-video-modal" data-youtube-modal hidden>
            <div class="ch-video-modal__backdrop" data-youtube-close></div>
            <div class="ch-video-modal__dialog" role="dialog" aria-modal="true" aria-label="Doctor's Talk video">
                <button type="button" class="ch-video-modal__close" data-youtube-close aria-label="Close video">×</button>
                <div class="ch-video-modal__frame">
                    <iframe
                        data-youtube-frame
                        title="Doctor's Talk video"
                        src=""
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>
    @endif

    <x-web.campus-band :stats="$stats" headline="Ready to book a slot?" cta-href="?book=1" cta-label="Book a visit →" />

    <section id="locations" class="ch-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <h2 class="ch-sec__title">How to get here</h2>
                </div>
            </div>
            <div class="ch-locs">
                <div class="ch-loc-list">
                    <a class="ch-loc" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($campusAddress) }}" target="_blank" rel="noopener">
                        <span>
                            <strong>{{ $campusLabel }}</strong>
                            <span>{{ $campusAddress }}</span>
                        </span>
                    </a>
                </div>
                <div class="ch-map">
                    @if (filled($mapEmbed) && str_contains($mapEmbed, '<iframe'))
                        {!! $mapEmbed !!}
                    @elseif (filled($mapEmbed))
                        <iframe title="{{ $hospitalName }} locations" src="{{ $mapEmbed }}" loading="lazy"></iframe>
                    @else
                        <iframe title="{{ $hospitalName }} locations" src="https://www.openstreetmap.org/export/embed.html?bbox=90.39%2C23.78%2C90.43%2C23.81&amp;layer=mapnik&amp;marker=23.7925%2C90.4078" loading="lazy"></iframe>
                    @endif
                </div>
                <form class="ch-dir" action="https://www.google.com/maps/dir/" method="get" target="_blank" rel="noopener">
                    <h3>Directions</h3>
                    <input type="hidden" name="api" value="1">
                    <input type="hidden" name="destination" value="{{ $campusAddress }}">
                    <label class="ch-field">
                        <span class="sr-only">Your address</span>
                        <input class="ch-input" type="search" name="origin" placeholder="Your starting point">
                    </label>
                    <button type="submit" class="ch-btn ch-btn--solid ch-btn--wide">Get Directions</button>
                </form>
            </div>
        </div>
    </section>

    <section id="news" class="ch-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <h2 class="ch-sec__title">News &amp; updates</h2>
                </div>
                <a class="ch-more" href="{{ route('news') }}">All updates →</a>
            </div>
            <div class="ch-news">
                @forelse ($posts as $article)
                    <article class="ch-article">
                        <img src="{{ \App\Support\PublicPhoto::article($article->cover_image, $loop->index) }}" alt="{{ $article->title }}">
                        @if ($article->published_at)
                            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('F j, Y') }}</time>
                        @endif
                        <h3>{{ $article->title }}</h3>
                        <p>{{ $article->excerpt }}</p>
                        <a class="ch-more" href="{{ route('news.show', $article) }}">Read more →</a>
                    </article>
                @empty
                    <p class="ch-lead">No articles yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <div class="ch-cal-modal" data-doctor-calendar hidden>
        <button type="button" class="ch-cal-modal__mask" data-cal-close aria-label="Close schedule"></button>
        <div class="ch-cal-modal__panel" role="dialog" aria-modal="true" aria-labelledby="clinic-schedule-title">
            <div class="ch-cal">
                <div class="ch-cal__bar">
                    <p class="ch-cal__title" id="clinic-schedule-title">Clinic schedule</p>
                    <div class="ch-cal__views">
                        <button type="button" class="is-on" data-cal-view="month">Month</button>
                        <button type="button" data-cal-view="week">Week</button>
                    </div>
                    <button type="button" class="ch-cal-modal__close" data-cal-close aria-label="Close">×</button>
                </div>
                <div class="ch-cal__nav">
                    <button type="button" data-cal-prev aria-label="Previous">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M12.5 5 7.5 10l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <p data-cal-label>Select a doctor to see availability</p>
                    <button type="button" data-cal-next aria-label="Next">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m7.5 5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
                <div class="ch-cal__weekdays" data-cal-weekdays hidden></div>
                <div class="ch-cal__board" data-cal-board></div>
                <p class="ch-cal__hint" data-cal-hint>Green: available. Red: booked.</p>
                <div class="ch-cal__key" aria-hidden="true">
                    <span class="ch-cal__swatch is-free">Free</span>
                    <span class="ch-cal__swatch is-taken">Booked</span>
                </div>
                <div class="ch-cal__times" data-cal-times hidden>
                    <p class="ch-cal__slot-label" data-cal-slot-label></p>
                    <div class="ch-cal__slots" data-cal-slots></div>
                </div>
                <button type="button" class="ch-btn ch-btn--solid ch-btn--wide" data-cal-apply>Use this time</button>
            </div>
        </div>
    </div>
@endsection
