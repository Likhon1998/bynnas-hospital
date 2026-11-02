@extends('layouts.public')

@section('title', ($hospital->get('hospital_name') ?: 'Hospital').' | '.($hospital->get('tagline') ?: 'Exceptional Care'))
@section('meta_description', $hospital->get('tagline') ?: 'Consultant care, diagnostics, and emergency support closer to home.')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $bookableList = isset($bookableServices) && $bookableServices->isNotEmpty() ? $bookableServices : $services;
    $doctorItems = $doctors->values()->map(function ($doctor) {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'specialization' => $doctor->specialization ?: $doctor->department?->name ?: 'Consultant',
            'department' => $doctor->department?->name,
            'years' => $doctor->experience_years,
            'photo' => $doctor->photo,
            'initials' => $doctor->initials(),
            'slug' => $doctor->slug,
        ];
    });
    $finderDoctors = $finderDoctors ?? collect();
    $faqs = $faqs ?? collect();
    $galleryItems = $galleryItems ?? collect();
    $testimonials = $testimonials ?? collect();
    $posts = $posts ?? collect();
    $isFinderSearch = $isFinderSearch ?? false;
    $leadDoctor = $isFinderSearch
        ? ($finderDoctors->first(fn ($doctor) => filled($doctor->photo)) ?? $finderDoctors->first())
        : ($doctors->first(fn ($doctor) => filled($doctor->photo)) ?? $doctors->first());
    $leadDoctorInitials = $leadDoctor?->initials() ?? 'DR';
    $finderCount = $isFinderSearch ? $finderDoctors->count() : (int) ($stats['doctors'] ?? 0);
    $finderIsVacant = $isFinderSearch && $finderDoctors->isEmpty();
    $heroTitle = $heroBanner?->title ?: 'Exceptional Care. Closer to Home.';
    $heroLead = $heroBanner?->subtitle ?: 'World-class medical care, advanced technology, and a compassionate team — for a healthier tomorrow.';
    $heroCtaLabel = $heroBanner?->cta_label ?: 'Learn more';
    $heroCtaUrl = $heroBanner?->destination() ?: route('about');
    $heroLines = preg_split('/(?<=\.)\s+/', $heroTitle, 2) ?: [$heroTitle];
    $patientServices = $services->take(4);
    $campusAddress = $hospital->get('address') ?: 'Main Campus';
    $mapEmbed = trim((string) $hospital->get('map_embed'));
    $review = $testimonials->first();
    $reviewAverage = $testimonials->isNotEmpty() ? round((float) $testimonials->avg('rating'), 1) : null;
    $prepPhoto = $galleryItems->first();
    $prepImage = \App\Support\PublicPhoto::usable($prepPhoto?->image) ?? \App\Support\PublicPhoto::LOBBY;
    $reviewScene = \App\Support\PublicPhoto::usable($galleryItems->get(1)?->image) ?? \App\Support\PublicPhoto::CARE;
    $reviewAvatar = \App\Support\PublicPhoto::usable($review?->photo);
    $selectedVisitDate = old('date');
    $selectedVisitTime = old('time');
    $selectedVisitLabel = ($selectedVisitDate && $selectedVisitTime)
        ? \Illuminate\Support\Carbon::parse($selectedVisitDate)->format('D, j M Y').' · '.$selectedVisitTime
        : '';
@endphp

@section('content')
    <section class="ch-hero" @if ($heroBanner?->image) style="--ch-hero-image: url('{{ $heroBanner->image }}')" @endif>
        <div class="ch-wrap ch-hero__grid">
            <div class="ch-hero__copy">
                <p class="ch-hero__kicker">{{ strtoupper($hospitalName) }} —</p>
                <h1>{!! implode('<br>', array_map('e', $heroLines)) !!}</h1>
                <p class="ch-hero__lead">{{ $heroLead }}</p>
                <ul class="ch-checks">
                    <li><span class="ch-check"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4 10-10"/></svg></span> {{ number_format((int) ($stats['doctors'] ?? 0)) }} expert doctors</li>
                    <li><span class="ch-check"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4 10-10"/></svg></span> {{ number_format((int) ($stats['departments'] ?? 0)) }} specialties</li>
                    <li><span class="ch-check"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4 10-10"/></svg></span> Patient-first</li>
                </ul>
                <a class="ch-more" href="{{ $heroCtaUrl }}">{{ $heroCtaLabel }} →</a>
            </div>

            <div class="ch-hero__card">
                <form id="appointment" class="ch-card" method="POST" action="{{ route('appointment-requests.store') }}" data-appointment-slots="{{ route('appointment-slots') }}" data-appointment-schedule="{{ route('appointment-schedule') }}">
                    @csrf
                    <div class="ch-card__head">
                        <h2>Book an Appointment</h2>
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
                            @foreach ($directoryDoctors ?? $doctors as $doctor)
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

    <section id="departments" class="ch-depts-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">Specialist clinics</p>
                    <h2 class="ch-sec__title">Our Departments</h2>
                    <p class="ch-depts-intro">Comprehensive care under one roof</p>
                </div>
                <a class="ch-more" href="{{ route('departments') }}">View all Departments →</a>
            </div>
            <div class="ch-depts">
                @forelse ($departments->take(8) as $department)
                    <a class="ch-dept" href="{{ route('home', ['specialty' => $department->name]) }}#find-doctor" title="{{ $department->description ?: $department->name }}">
                        <span class="ch-dept__icon"><x-icon :name="$department->icon ?: 'hospital'" /></span>
                        <strong>{{ $department->name }}</strong>
                    </a>
                @empty
                    <p class="ch-lead">Departments will appear here when the desk publishes them.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section id="find-doctor" class="ch-find-sec">
        <div class="ch-wrap">
            <script type="application/json" data-finder-directory>@json($finderDirectory ?? [])</script>
            <div class="ch-find{{ $finderIsVacant ? ' is-vacant' : '' }}" data-doctor-finder data-finder-total="{{ $stats['doctors'] }}">
                <div class="ch-find__media">
                    <img
                        class="ch-find__photo"
                        data-finder-photo
                        src="{{ $leadDoctor?->photo ?: '' }}"
                        alt="{{ $leadDoctor?->name ?: '' }}"
                        @if (! $leadDoctor?->photo) hidden @endif
                    >
                    <span class="ch-find__photo ch-find__initials" data-finder-initials aria-hidden="true" @if ($leadDoctor?->photo || ! $leadDoctor) hidden @endif>{{ $leadDoctorInitials }}</span>
                    <span class="ch-find__photo ch-find__photo--empty" data-finder-vacant aria-hidden="true" @if (! $finderIsVacant) hidden @endif>
                        <x-icon name="stethoscope" />
                    </span>
                    <p data-finder-caption>{{ $finderIsVacant ? 'No consultants in this clinic' : 'Consult with our expert doctors' }}</p>
                    <p class="ch-find__count">
                        <strong data-finder-count>{{ $finderCount }}</strong>
                        <span data-finder-count-label>{{ \Illuminate\Support\Str::plural('Specialist', $finderCount) }}</span>
                    </p>
                    <a class="ch-more" href="{{ route('doctors') }}">View all Specialists →</a>
                </div>
                <div class="ch-find__body">
                    <p class="ch-find__kicker">Doctor directory</p>
                    <h2>Find the Right Doctor for You</h2>
                    <p>Search by a specialty. We will show consultants you can book — you do not need to know a name first.</p>
                    <form class="ch-search" action="{{ route('home') }}#find-doctor" method="get" data-doctor-filter>
                        <label class="ch-search__field">
                            <span>Specialty</span>
                            <select class="ch-input" name="specialty" data-doctor-specialty>
                                <option value="">All specialties</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->name }}" @selected(request('specialty') === $department->name)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="ch-search__field">
                            <span>Choose a doctor</span>
                            <select class="ch-input" name="q" data-doctor-list>
                                <option value="">Browse consultants</option>
                                @foreach ($directoryDoctors as $doctor)
                                    <option
                                        value="{{ $doctor->name }}"
                                        data-specialty="{{ $doctor->department?->name }}"
                                        data-specialization="{{ $doctor->specialization }}"
                                        @selected(request('q') === $doctor->name)
                                    >{{ $doctor->name }}@if ($doctor->specialization || $doctor->department) — {{ $doctor->specialization ?: $doctor->department?->name }}@endif</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="ch-btn ch-btn--solid ch-search__go">Show doctors</button>
                    </form>
                    <p class="ch-find__result" data-finder-result @if (! $isFinderSearch) hidden @endif>
                        <span data-finder-result-text>
                            @if ($isFinderSearch && $finderDoctors->isNotEmpty())
                                {{ $finderDoctors->count() }} {{ \Illuminate\Support\Str::plural('consultant', $finderDoctors->count()) }}{{ $finderSearchLabel ? ' in '.$finderSearchLabel : '' }}.
                            @elseif ($isFinderSearch)
                                No consultants in this clinic yet. Pick another specialty or browse all doctors.
                            @endif
                        </span>
                        <a href="{{ route('home') }}#find-doctor" data-finder-clear>Show all</a>
                    </p>
                    <div class="ch-find__people" data-finder-people @if ($finderDoctors->isEmpty()) hidden @endif>
                        @foreach ($finderDoctors as $doctor)
                                <a class="ch-find__person" href="{{ filled($doctor->slug) ? route('doctors.show', $doctor) : route('home').'#appointment' }}">
                                @if ($doctor->photo)
                                    <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}">
                                @else
                                    <span class="ch-find__initials" aria-hidden="true">{{ $doctor->initials() }}</span>
                                @endif
                                <span>
                                    <strong>{{ $doctor->name }}</strong>
                                    <em>{{ $doctor->specialization ?: $doctor->department?->name ?: 'Consultant' }}</em>
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <div class="ch-find__chips">
                        @foreach ($departments->take(6) as $department)
                            <a href="{{ route('home', ['specialty' => $department->name]) }}#find-doctor" data-finder-chip="{{ $department->name }}" @class(['is-active' => request('specialty') === $department->name])>{{ $department->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="patients" class="ch-patients">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <p class="ch-find__kicker">For patients & families</p>
                    <h2 class="ch-sec__title">Patient Services</h2>
                    <p class="ch-depts-intro">The desks people use most: emergency, records, visiting, and billing.</p>
                </div>
                <a class="ch-more" href="{{ route('services') }}">Explore services →</a>
            </div>
            <div class="ch-services">
                @forelse ($patientServices as $service)
                    <article @class(['ch-service', 'ch-service--urgent' => $service->isUrgent()])>
                        <span class="ch-service__icon"><x-icon :name="$service->publicIcon()" class="h-5 w-5" /></span>
                        <p class="ch-service__meta">{{ $service->department?->name ?: ($service->isUrgent() ? 'Open 24 hours' : 'Patient service') }}</p>
                        <h3>{{ $service->title }}</h3>
                        <p>{{ $service->summary ?: 'Ask the desk how this service runs and whether you need to book.' }}</p>
                        <p class="ch-service__fact">
                            @if ($service->isUrgent())
                                {{ $hospital->get('emergency_phone') ?: $hospital->get('phone') ?: '24/7 Emergency Care' }}
                            @else
                                {{ $service->department?->name ?: 'Published by the desk' }}
                            @endif
                        </p>
                        <a href="{{ $service->isUrgent() ? route('emergency') : route('services.show', $service) }}">
                            {{ $service->isUrgent() ? 'Go to emergency' : 'Learn more' }} →
                        </a>
                    </article>
                @empty
                    <p class="ch-lead">Services will appear here when the desk publishes them.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section id="doctors" class="ch-docs-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <h2 class="ch-sec__title">Our Expert Doctors</h2>
                    <p class="ch-depts-intro">Skilled. Experienced. Compassionate.</p>
                </div>
                <a class="ch-more" href="{{ route('doctors') }}">View all Doctors →</a>
            </div>
            <div class="ch-docs">
                @forelse ($doctorItems as $doctor)
                    <article class="ch-doc">
                        <a class="ch-doc__profile" @if (filled($doctor['slug'])) href="{{ route('doctors.show', $doctor['slug']) }}" @else href="{{ route('doctors') }}" @endif>
                            @if ($doctor['photo'])
                                <img src="{{ $doctor['photo'] }}" alt="{{ $doctor['name'] }}">
                            @else
                                <span class="ch-doc__initials" aria-hidden="true">{{ $doctor['initials'] }}</span>
                            @endif
                            <strong>{{ $doctor['name'] }}</strong>
                            <em>{{ $doctor['specialization'] }}</em>
                            @if ($doctor['years'])
                                <p class="ch-doc__meta">{{ $doctor['years'] }} yrs experience</p>
                            @endif
                        </a>
                        <div class="ch-doc__actions">
                            @if (filled($doctor['slug']))
                                <a class="ch-doc__book" href="{{ route('doctors.show', $doctor['slug']) }}">Profile</a>
                            @endif
                            <a
                                class="ch-doc__book"
                                href="{{ route('home') }}#appointment"
                                @if ($doctor['id'])
                                    data-book-doctor="{{ $doctor['id'] }}"
                                @endif
                            >Book</a>
                        </div>
                    </article>
                @empty
                    <p class="ch-lead">Consultants will appear here when the desk publishes them.</p>
                @endforelse
            </div>
        </div>
    </section>

    <x-web.campus-band :stats="$stats" headline="Book Your Appointment Today" cta-href="#appointment" />

    <section id="locations" class="ch-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <h2 class="ch-sec__title">Our Locations</h2>
                    <p class="ch-depts-intro">Find the nearest {{ $hospitalName }} campus.</p>
                </div>
                <a class="ch-more" href="#locations">Get Directions →</a>
            </div>
            <div class="ch-locs">
                <div class="ch-loc-list">
                    <a class="ch-loc" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($campusAddress) }}" target="_blank" rel="noopener">
                        <span>
                            <strong>Main Campus</strong>
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
                    <h3>Get Directions</h3>
                    <p>Find the nearest {{ $hospitalName }} campus.</p>
                    <input type="hidden" name="api" value="1">
                    <input type="hidden" name="destination" value="{{ $campusAddress }}">
                    <label class="ch-field">
                        <span class="sr-only">Your address</span>
                        <input class="ch-input" type="search" name="origin" placeholder="Enter location">
                    </label>
                    <button type="submit" class="ch-btn ch-btn--solid ch-btn--wide">Get Directions</button>
                </form>
            </div>
        </div>
    </section>

    <section id="faq" class="ch-sec ch-sec--soft">
        <div class="ch-wrap ch-trio">
            <div class="ch-trio__col">
                <div class="ch-trio__head">
                    <h2 class="ch-sec__title">Frequently Asked Questions</h2>
                    <p class="ch-lead">Common questions before you book a visit.</p>
                </div>
                <div class="ch-faq">
                    @forelse ($faqs as $faq)
                        <details>
                            <summary>{{ $faq->question }}</summary>
                            <p>{{ $faq->answer }}</p>
                        </details>
                    @empty
                        <p class="ch-lead">Questions will appear here when the desk publishes them.</p>
                    @endforelse
                </div>
            </div>
            <div class="ch-trio__col">
                <div class="ch-trio__head">
                    <h2 class="ch-sec__title">Patient Preparation</h2>
                    <p class="ch-lead">Everything you need to know before you arrive.</p>
                </div>
                <figure class="ch-trio__media">
                    <img src="{{ $prepImage }}" alt="Preparing for a visit at {{ $hospitalName }}">
                </figure>
                <div class="ch-trio__list">
                    @forelse ($posts->take(3) as $guide)
                        <a class="ch-prep-item" href="{{ route('news.show', $guide) }}">{{ $guide->title }} <span>›</span></a>
                    @empty
                        <a class="ch-prep-item" href="{{ route('about') }}">Visit the campus <span>›</span></a>
                        <a class="ch-prep-item" href="{{ route('services') }}">Read a service <span>›</span></a>
                    @endforelse
                </div>
                <a class="ch-more" href="{{ $posts->isNotEmpty() ? route('news') : route('about') }}">View all Guides →</a>
            </div>
            <div class="ch-trio__col">
                <div class="ch-trio__head">
                    <h2 class="ch-sec__title">Patient Reviews</h2>
                    <p class="ch-lead">What families said after they used the desk.</p>
                </div>
                <figure class="ch-trio__media">
                    <img src="{{ $reviewScene }}" alt="Care at {{ $hospitalName }}">
                </figure>
                @if ($review)
                    @if ($reviewAverage)
                        <p class="ch-review">★ {{ $reviewAverage }} <small>({{ $testimonials->count() }} {{ \Illuminate\Support\Str::plural('review', $testimonials->count()) }})</small></p>
                    @endif
                    <p class="ch-lead ch-trio__quote">“{{ $review->quote }}”</p>
                    <p class="ch-trio__person">
                        @if ($reviewAvatar)
                            <img class="ch-trio__avatar" src="{{ $reviewAvatar }}" alt="">
                        @endif
                        <span>— {{ $review->display_name }}@if ($review->display_role), {{ $review->display_role }}@endif</span>
                    </p>
                @else
                    <p class="ch-lead">Reviews will appear here when patients share them.</p>
                @endif
            </div>
        </div>
    </section>

    <section id="news" class="ch-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <h2 class="ch-sec__title">News & Updates</h2>
                    <p class="ch-depts-intro">Latest news, health tips and campus updates.</p>
                </div>
                <a class="ch-more" href="{{ route('news') }}">View all news →</a>
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
                    <p class="ch-lead">Campus updates will appear here when the desk publishes them.</p>
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
                    <p data-cal-label>Choose a doctor to see free times</p>
                    <button type="button" data-cal-next aria-label="Next">
                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m7.5 5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
                <div class="ch-cal__weekdays" data-cal-weekdays hidden></div>
                <div class="ch-cal__board" data-cal-board></div>
                <p class="ch-cal__hint" data-cal-hint>Green times are free. Red times are already booked.</p>
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
