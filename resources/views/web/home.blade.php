@extends('layouts.public')

@section('title', ($hospital->get('hospital_name') ?: 'Cityhealth Medical Center').' | Exceptional Care')
@section('meta_description', 'World-class medical care, closer to home.')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Cityhealth Medical Center';
    $bookableList = isset($bookableServices) && $bookableServices->isNotEmpty() ? $bookableServices : $services;
    $doctorFallback = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80';
    $doctorPhotos = [
        'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=400&q=80',
        'https://images.unsplash.com/photo-1651008376811-b90baee875d4?auto=format&fit=crop&w=400&q=80',
    ];
    $deptMeta = [
        'cardiology' => ['icon' => 'heart', 'line' => 'Heart care', 'short' => 'Cardiology'],
        'neurology' => ['icon' => 'brain', 'line' => 'Brain care', 'short' => 'Neurology'],
        'orthopedics' => ['icon' => 'bone', 'line' => 'Bone & joint', 'short' => 'Orthopedics'],
        'pediatrics' => ['icon' => 'baby', 'line' => 'Child care', 'short' => 'Pediatrics'],
        'oncology' => ['icon' => 'ribbon', 'line' => 'Cancer care', 'short' => 'Oncology'],
        'dermatology' => ['icon' => 'sparkles', 'line' => 'Skin care', 'short' => 'Dermatology'],
        'gastroenterology' => ['icon' => 'stomach', 'line' => 'Digestive health', 'short' => 'Gastroenterology'],
        'pulmonology' => ['icon' => 'lungs', 'line' => 'Lung care', 'short' => 'Pulmonology'],
        'urology' => ['icon' => 'shield', 'line' => 'Urinary care', 'short' => 'Urology'],
        'ophthalmology' => ['icon' => 'eye', 'line' => 'Eye care', 'short' => 'Ophthalmology'],
        'ent' => ['icon' => 'ear', 'line' => 'Ear, nose & throat', 'short' => 'ENT'],
        'nephrology' => ['icon' => 'activity', 'line' => 'Kidney care', 'short' => 'Nephrology'],
        'gynecology & obstetrics' => ['icon' => 'person', 'line' => "Women's health", 'short' => 'Gynecology'],
        'general medicine' => ['icon' => 'stethoscope', 'line' => 'Adult care', 'short' => 'General Medicine'],
        'general surgery' => ['icon' => 'scalpel', 'line' => 'Surgical care', 'short' => 'General Surgery'],
        'emergency' => ['icon' => 'ambulance', 'line' => '24/7 urgent care', 'short' => 'Emergency'],
    ];
    $preferredDepts = ['Cardiology', 'Neurology', 'Orthopedics', 'Gynecology & Obstetrics', 'Pediatrics', 'General Medicine', 'General Surgery', 'Dermatology'];
    $deptCatalog = [
        ['name' => 'Cardiology', 'icon' => 'heart', 'line' => 'Heart & Vascular Care', 'tone' => 'clinic'],
        ['name' => 'Neurology', 'icon' => 'brain', 'line' => 'Brain & Nerve Care', 'tone' => 'clinic'],
        ['name' => 'Orthopedics', 'icon' => 'bone', 'line' => 'Bone & Joint Care', 'tone' => 'clinic'],
        ['name' => 'Pediatrics', 'icon' => 'baby', 'line' => 'Child Health Care', 'tone' => 'clinic'],
        ['name' => 'Oncology', 'icon' => 'ribbon', 'line' => 'Cancer Care', 'tone' => 'clinic'],
        ['name' => 'Dermatology', 'icon' => 'sparkles', 'line' => 'Skin, Hair & Nails', 'tone' => 'clinic'],
        ['name' => 'Gastroenterology', 'icon' => 'stomach', 'line' => 'Digestive Health', 'tone' => 'clinic'],
        ['name' => 'Pulmonology', 'icon' => 'lungs', 'line' => 'Lung & Respiratory Care', 'tone' => 'clinic'],
        ['name' => 'Urology', 'icon' => 'shield', 'line' => 'Kidney & Urinary Care', 'tone' => 'clinic'],
        ['name' => 'Ophthalmology', 'icon' => 'eye', 'line' => 'Eye Care', 'tone' => 'clinic'],
        ['name' => 'ENT', 'icon' => 'ear', 'line' => 'Ear, Nose & Throat', 'tone' => 'clinic'],
        ['name' => 'Nephrology', 'icon' => 'activity', 'line' => 'Kidney Care', 'tone' => 'clinic'],
    ];
    $deptItems = $departments->map(function ($department) use ($deptMeta) {
        $meta = $deptMeta[strtolower((string) $department->name)] ?? ['icon' => $department->icon ?: 'hospital', 'line' => $department->description ?: 'Specialist clinic care'];

        return [
            'name' => $department->name,
            'short' => $meta['short'] ?? $department->name,
            'icon' => $meta['icon'],
            'line' => $meta['line'],
            'tone' => str_contains(strtolower((string) $department->name), 'emergency') ? 'urgent' : 'clinic',
        ];
    });
    if ($deptItems->count() < 6) {
        $existing = $deptItems->pluck('name')->map(fn ($name) => strtolower((string) $name));
        foreach ($deptCatalog as $item) {
            if ($existing->contains(strtolower($item['name']))) {
                continue;
            }
            $deptItems->push($item);
            if ($deptItems->count() >= 12) {
                break;
            }
        }
    }
    $doctorDefaults = [
        ['name' => 'Dr. Michael Anderson', 'specialization' => 'Cardiologist', 'years' => 15],
        ['name' => 'Dr. Sarah Williams', 'specialization' => 'Neurologist', 'years' => 12],
        ['name' => 'Dr. David Lee', 'specialization' => 'Orthopedic Surgeon', 'years' => 14],
        ['name' => 'Dr. Priya Patel', 'specialization' => 'Pediatrician', 'years' => 10],
        ['name' => 'Dr. James Brown', 'specialization' => 'Oncologist', 'years' => 18],
        ['name' => 'Dr. Emily Clark', 'specialization' => 'Dermatologist', 'years' => 9],
    ];
    $doctorItems = $doctors->values()->map(function ($doctor, $index) use ($doctorPhotos, $doctorFallback, $doctorDefaults) {
        $fallback = $doctorDefaults[$index] ?? $doctorDefaults[0];

        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'specialization' => $doctor->specialization ?: $doctor->department?->name ?: $fallback['specialization'],
            'department' => $doctor->department?->name,
            'years' => $doctor->experience_years ?: $fallback['years'],
            'photo' => $doctor->photo ?: $doctorPhotos[$index] ?? $doctorFallback,
        ];
    });
    if (! ($isDoctorSearch ?? false) && $doctorItems->count() < 6) {
        foreach (array_slice($doctorDefaults, $doctorItems->count()) as $index => $fallback) {
            $photoIndex = $doctorItems->count() + $index;
            $doctorItems->push([
                'id' => null,
                'name' => $fallback['name'],
                'specialization' => $fallback['specialization'],
                'department' => $fallback['specialization'],
                'years' => $fallback['years'],
                'photo' => $doctorPhotos[$photoIndex] ?? $doctorFallback,
            ]);
        }
    }
    $newsDefaults = [
        [
            'title' => 'Cityhealth Medical Center Earns Top Safety Rating',
            'excerpt' => 'National reviewers recognized the campus for safe, coordinated hospital care.',
            'image' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=900&q=80',
            'date' => now()->subDays(18),
        ],
        [
            'title' => 'New Advanced Cardiac Care Unit Now Open',
            'excerpt' => 'State-of-the-art support for heart patients is now available on the main campus.',
            'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80',
            'date' => now()->subDays(11),
        ],
        [
            'title' => 'Free Community Health Screening Day',
            'excerpt' => 'Join a free screening event and meet the Cityhealth care team.',
            'image' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=900&q=80',
            'date' => now()->subDays(6),
        ],
    ];
    $newsItems = $posts->isNotEmpty()
        ? $posts->map(function ($post, $index) use ($newsDefaults) {
            return [
                'title' => $post->title,
                'excerpt' => $post->excerpt ?: $newsDefaults[$index]['excerpt'] ?? 'Read the latest from the medical campus.',
                'image' => $newsDefaults[$index]['image'] ?? $newsDefaults[0]['image'],
                'date' => $post->published_at ?: now(),
            ];
        })
        : collect($newsDefaults);
    $review = $testimonials->first();
    $reviewQuote = $review?->quote ?: 'The doctors and staff at '.$hospitalName.' provided excellent, highly recommended care.';
    $reviewName = $review?->display_name ?: 'Jessica M.';
    $locations = [
        [
            'name' => 'Main Campus',
            'address' => $hospital->get('address') ?: '12 Lakeside Avenue, Gulshan, Dhaka 1212',
            'image' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=300&q=80',
        ],
        [
            'name' => 'Banani Clinic',
            'address' => '28 Kemal Ataturk Avenue, Banani, Dhaka 1213',
            'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=300&q=80',
        ],
        [
            'name' => 'Dhanmondi Care Center',
            'address' => '45 Road 27, Dhanmondi, Dhaka 1209',
            'image' => 'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=300&q=80',
        ],
    ];
    $deptItems = $deptItems->sortBy(function (array $item) use ($preferredDepts) {
        $position = array_search($item['name'], $preferredDepts, true);

        return $position === false ? 100 : $position;
    })->values()->take(12);
    $times = ['08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
    $previewDoctors = collect($directoryDoctors ?? $doctors);
    if (request()->filled('specialty')) {
        $matchedPreview = $previewDoctors
            ->filter(fn ($doctor) => $doctor->department?->name === request('specialty'))
            ->values();
        if ($matchedPreview->isNotEmpty()) {
            $previewDoctors = $matchedPreview;
        }
    }
@endphp

@section('content')
    <section class="ch-hero">
        <div class="ch-wrap ch-hero__grid">
            <div class="ch-hero__copy">
                <p class="ch-hero__kicker">{{ strtoupper($hospitalName) }} —</p>
                <h1>Exceptional Care.<br>Closer to Home.</h1>
                <p class="ch-hero__lead">World-class medical care, advanced technology, and a compassionate team — for a healthier tomorrow.</p>
                <ul class="ch-checks">
                    <li><span class="ch-check"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4 10-10"/></svg></span> Expert doctors</li>
                    <li><span class="ch-check"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4 10-10"/></svg></span> Advanced technology</li>
                    <li><span class="ch-check"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4 10-10"/></svg></span> Patient-first</li>
                </ul>
                <a class="ch-more" href="{{ route('about') }}">Learn more →</a>
            </div>

            <div class="ch-hero__card">
                <form id="appointment" class="ch-card" method="POST" action="{{ route('appointment-requests.store') }}">
                    @csrf
                    <div class="ch-card__head">
                        <h2>Book an Appointment</h2>
                    </div>
                    <div class="ch-card__body">
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
                        <select class="ch-input" name="doctor">
                            <option value="">Select doctor</option>
                            @foreach ($directoryDoctors ?? $doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }} — {{ $doctor->specialization ?: $doctor->department?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="ch-field">
                        <span>Select Service</span>
                        <select class="ch-input" name="service" required>
                            <option value="">Choose a service</option>
                            @foreach ($bookableList as $service)
                                <option value="{{ $service->slug }}" @selected(old('service') === $service->slug)>{{ $service->title }}</option>
                            @endforeach
                        </select>
                        @error('service')
                            <span class="ch-error">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="ch-field">
                        <span>Select Date</span>
                        <input class="ch-input" type="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" required>
                        @error('date')
                            <span class="ch-error">{{ $message }}</span>
                        @enderror
                    </label>
                    <label class="ch-field">
                        <span>Preferred Time</span>
                        <select class="ch-input" name="time">
                            <option value="">Preferred time</option>
                            @foreach ($times as $time)
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="ch-btn ch-btn--solid ch-btn--wide">Book Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section id="departments" class="ch-depts-sec">
        <div class="ch-wrap ch-depts-row">
            <div class="ch-depts-lead">
                <h2 class="ch-sec__title">Our Departments</h2>
                <p>Comprehensive care under one roof</p>
                <a class="ch-more" href="{{ route('services') }}">View all Departments →</a>
            </div>
            <div class="ch-depts">
                @foreach ($deptItems->take(8) as $department)
                    <a class="ch-dept" href="{{ route('services') }}">
                        <span class="ch-dept__icon"><x-icon :name="$department['icon']" class="h-4 w-4" /></span>
                        <strong>{{ $department['short'] ?? $department['name'] }}</strong>
                        <em>{{ $department['line'] }}</em>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="find-doctor" class="ch-find-sec">
        <div class="ch-wrap">
            <div class="ch-find">
                <div class="ch-find__media">
                    <img class="ch-find__photo" src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80" alt="Physician">
                    <p>Consult with our expert doctors</p>
                    <p class="ch-find__count"><strong>250+</strong> Specialists</p>
                    <a class="ch-more" href="#doctors">View all Specialists →</a>
                </div>
                <div class="ch-find__body">
                    <p class="ch-find__kicker">Doctor directory</p>
                    <h2>Find the Right Doctor for You</h2>
                    <p>Search by a specialty. We will show consultants you can book — you do not need to know a name first.</p>
                    <form class="ch-search" action="{{ route('home') }}#doctors" method="get" data-doctor-filter>
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
                                @foreach ($directoryDoctors ?? $doctors as $doctor)
                                    <option
                                        value="{{ $doctor->name }}"
                                        data-specialty="{{ $doctor->department?->name }}"
                                        @selected(request('q') === $doctor->name)
                                    >{{ $doctor->name }}@if ($doctor->specialization || $doctor->department) — {{ $doctor->specialization ?: $doctor->department?->name }}@endif</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="ch-search__field">
                            <span>Campus</span>
                            <select class="ch-input" name="location">
                                <option value="">All locations</option>
                                <option @selected(request('location') === 'Main Campus')>Main Campus</option>
                                <option @selected(request('location') === 'Banani Clinic')>Banani Clinic</option>
                                <option @selected(request('location') === 'Dhanmondi Care Center')>Dhanmondi Care Center</option>
                            </select>
                        </label>
                        <button type="submit" class="ch-btn ch-btn--solid ch-search__go">Show doctors</button>
                    </form>
                    @if ($previewDoctors->isNotEmpty())
                        <div class="ch-find__people">
                            @foreach ($previewDoctors->take(4) as $doctor)
                                <a class="ch-find__person" href="{{ route('home', ['q' => $doctor->name]) }}#doctors">
                                    <img src="{{ $doctor->photo ?: ($doctorPhotos[$loop->index] ?? $doctorFallback) }}" alt="{{ $doctor->name }}">
                                    <span>
                                        <strong>{{ $doctor->name }}</strong>
                                        <em>{{ $doctor->specialization ?: $doctor->department?->name ?: 'Consultant' }}</em>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="ch-find__chips">
                        @foreach ($departments->take(5) as $department)
                            <a href="{{ route('home', ['specialty' => $department->name]) }}#doctors">{{ $department->name }}</a>
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
                <article class="ch-service ch-service--urgent">
                    <span class="ch-service__icon"><x-icon name="ambulance" class="h-5 w-5" /></span>
                    <p class="ch-service__meta">Open 24 hours</p>
                    <h3>Emergency Care</h3>
                    <p>Walk in any time. The trauma desk stays open day or night.</p>
                    <p class="ch-service__fact">{{ $hospital->get('emergency_phone') ?: $hospital->get('phone') ?: '24/7 Emergency Care' }}</p>
                    <a href="{{ route('emergency') }}">Go to emergency →</a>
                </article>
                <article class="ch-service">
                    <span class="ch-service__icon"><x-icon name="users" class="h-5 w-5" /></span>
                    <p class="ch-service__meta">Secure records</p>
                    <h3>Patient Portal</h3>
                    <p>View visits, reports, and upcoming appointments in one place.</p>
                    <p class="ch-service__fact">Staff and clinicians sign in here</p>
                    <a href="{{ route('admin.login') }}">Log in →</a>
                </article>
                <article class="ch-service">
                    <span class="ch-service__icon"><x-icon name="clock" class="h-5 w-5" /></span>
                    <p class="ch-service__meta">Family visits</p>
                    <h3>Visiting Hours</h3>
                    <p>Check visiting times and what to bring before you arrive.</p>
                    <p class="ch-service__fact">{{ $workingHours['saturday'] ?? $workingHours['monday'] ?? '9:00 AM – 9:00 PM' }} · wards</p>
                    <a href="{{ route('about') }}">View details →</a>
                </article>
                <article class="ch-service">
                    <span class="ch-service__icon"><x-icon name="shield" class="h-5 w-5" /></span>
                    <p class="ch-service__meta">Pay with clarity</p>
                    <h3>Insurance & Billing</h3>
                    <p>Most major plans are accepted. Bring your card and we will help.</p>
                    <p class="ch-service__fact">Estimates before planned care</p>
                    <a href="{{ route('services') }}">Learn more →</a>
                </article>
            </div>
        </div>
    </section>

    <section id="doctors" class="ch-docs-sec">
        <div class="ch-wrap">
            <div class="ch-sec__head">
                <div>
                    <h2 class="ch-sec__title">Our Expert Doctors</h2>
                    <p class="ch-depts-intro">
                        @if ($isDoctorSearch ?? false)
                            Showing consultants{{ $doctorSearchLabel ? ' for '.$doctorSearchLabel : '' }}.
                        @else
                            Skilled. Experienced. Compassionate.
                        @endif
                    </p>
                </div>
                @if ($isDoctorSearch ?? false)
                    <a class="ch-more" href="{{ route('home') }}#doctors">Clear search</a>
                @else
                    <a class="ch-more" href="{{ route('home') }}#find-doctor">View all Doctors →</a>
                @endif
            </div>
            <div class="ch-docs">
                @forelse ($doctorItems->take($isDoctorSearch ?? false ? 12 : 6) as $doctor)
                    <article class="ch-doc">
                        <img src="{{ $doctor['photo'] }}" alt="{{ $doctor['name'] }}">
                        <strong>{{ $doctor['name'] }}</strong>
                        <em>{{ $doctor['specialization'] }}</em>
                        <p class="ch-stars"><b>★★★★★</b> 4.8</p>
                        <a
                            class="ch-doc__book"
                            href="{{ route('home') }}#appointment"
                            @if ($doctor['id'])
                                data-book-doctor="{{ $doctor['id'] }}"
                            @endif
                        >Book</a>
                    </article>
                @empty
                    <p class="ch-lead">No consultants match that search. Pick another specialty or browse the list above.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="ch-band">
        <div class="ch-wrap ch-band__row">
            <div class="ch-band__copy">
                <div>
                    <h2>Book Your Appointment Today</h2>
                    <p>Choose a convenient time and specialist.</p>
                </div>
            </div>
            <div class="ch-stats">
                <div class="ch-stat"><strong>250+</strong><span>Expert Doctors</span></div>
                <div class="ch-stat"><strong>50+</strong><span>Specialties</span></div>
                <div class="ch-stat"><strong>1.2M+</strong><span>Patients</span></div>
                <div class="ch-stat"><strong>500+</strong><span>Hospital Beds</span></div>
                <div class="ch-stat"><strong>98%</strong><span>Satisfaction</span></div>
            </div>
            <a href="#appointment" class="ch-btn ch-btn--light">Book Appointment →</a>
        </div>
    </section>

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
                    @foreach ($locations as $location)
                        <a class="ch-loc" href="#locations">
                            <span>
                                <strong>{{ $location['name'] }}</strong>
                                <span>{{ $location['address'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
                <div class="ch-map">
                    <iframe title="{{ $hospitalName }} locations" src="https://www.openstreetmap.org/export/embed.html?bbox=90.39%2C23.78%2C90.43%2C23.81&amp;layer=mapnik&amp;marker=23.7925%2C90.4078" loading="lazy"></iframe>
                </div>
                <form class="ch-dir" action="https://www.google.com/maps/search/" method="get" target="_blank" rel="noopener">
                    <h3>Get Directions</h3>
                    <p>Find the nearest {{ $hospitalName }} campus.</p>
                    <label class="ch-field">
                        <span class="sr-only">Your address</span>
                        <input class="ch-input" type="search" name="query" placeholder="Enter location">
                    </label>
                    <button type="submit" class="ch-btn ch-btn--solid ch-btn--wide">Get Directions</button>
                </form>
            </div>
        </div>
    </section>

    <section id="faq" class="ch-sec ch-sec--soft">
        <div class="ch-wrap ch-trio">
            <div>
                <h2 class="ch-sec__title">Frequently Asked Questions</h2>
                <div class="ch-faq">
                    <details>
                        <summary>Do I need an appointment?</summary>
                        <p>Emergency care is open around the clock. For clinics and planned visits, booking ahead keeps your wait shorter.</p>
                    </details>
                    <details>
                        <summary>Do you accept my insurance?</summary>
                        <p>Most major plans are accepted. Bring your insurance card and a photo ID to the desk.</p>
                    </details>
                    <details>
                        <summary>What should I prepare for my first visit?</summary>
                        <p>Bring prior prescriptions, reports, and a list of medicines you take now.</p>
                    </details>
                </div>
                <a class="ch-more" href="{{ route('about') }}">View all FAQ →</a>
            </div>
            <div>
                <h2 class="ch-sec__title">Patient Preparation</h2>
                <p class="ch-lead">Everything you need to know before you arrive.</p>
                <img class="ch-trio__photo" src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80" alt="Patient preparation">
                <div>
                    <a class="ch-prep-item" href="{{ route('about') }}">Pre-Visit Checklist <span>›</span></a>
                    <a class="ch-prep-item" href="{{ route('about') }}">What to Bring <span>›</span></a>
                    <a class="ch-prep-item" href="{{ route('about') }}">Fasting Guidelines <span>›</span></a>
                </div>
                <a class="ch-more" href="{{ route('about') }}">View all Guides →</a>
            </div>
            <div>
                <h2 class="ch-sec__title">Patient Reviews</h2>
                <img class="ch-trio__photo" src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80" alt="Patient review">
                <p class="ch-review">★ 4.8 <small>(2,340 reviews)</small></p>
                <p class="ch-lead">“{{ $reviewQuote }}”</p>
                <p class="ch-lead">— {{ $reviewName }}</p>
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
                <a class="ch-more" href="#news">View all news →</a>
            </div>
            <div class="ch-news">
                @foreach ($newsItems->take(3) as $article)
                    <article class="ch-article">
                        <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
                        <time datetime="{{ $article['date']->toDateString() }}">{{ $article['date']->format('F j, Y') }}</time>
                        <h3>{{ $article['title'] }}</h3>
                        <p>{{ $article['excerpt'] }}</p>
                        <a class="ch-more" href="{{ route('about') }}">Read more →</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
