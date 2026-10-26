@extends('layouts.public')

@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $phone = $hospital->get('phone');
    $bookableList = isset($bookableServices) && $bookableServices->isNotEmpty() ? $bookableServices : $services;
    $doctorFallback = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80';
    $doctorPhotos = [
        'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=800&q=80',
    ];
    $deptDefaults = [
        ['name' => 'Cardiology', 'icon' => 'heart', 'description' => 'Heart clinics, ECG support, and consultant-led cardiac care.'],
        ['name' => 'Neurology', 'icon' => 'brain', 'description' => 'Brain and nerve clinics for stroke risk, migraine, and seizures.'],
        ['name' => 'Orthopedics', 'icon' => 'bone', 'description' => 'Bone, joint, and injury care with surgical follow-up.'],
        ['name' => 'Gastroenterology', 'icon' => 'activity', 'description' => 'Digestive clinics for ulcer, liver, and gut concerns.'],
        ['name' => 'Pediatrics', 'icon' => 'baby', 'description' => 'Child clinics, growth checks, and fever or cough visits.'],
        ['name' => 'Gynecology & Obstetrics', 'icon' => 'person', 'description' => 'Women’s health, pregnancy clinics, and delivery planning.'],
        ['name' => 'Urology', 'icon' => 'shield', 'description' => 'Kidney and urinary clinics with consultant review.'],
        ['name' => 'Radiology', 'icon' => 'activity', 'description' => 'X-ray, ultrasound, and imaging reported on campus.'],
    ];
    $deptShowcase = collect($deptDefaults);
    $deptIcons = ['heart', 'brain', 'bone', 'activity', 'baby', 'person', 'shield', 'activity'];
    $blogDefaults = [
        [
            'title' => 'Discover How AI-Powered Stethoscopes Are Changing Diagnostics',
            'image' => 'https://images.unsplash.com/photo-1584982751601-97dcc096659c?auto=format&fit=crop&w=900&q=80',
            'tag1' => 'Medical Devices',
            'tag2' => 'Health Care',
            'author' => 'Hamida Jannat',
            'avatar' => $doctorFallback,
        ],
        [
            'title' => 'The Future of Precision Medicine Through DNA Analysis',
            'image' => 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?auto=format&fit=crop&w=900&q=80',
            'tag1' => 'Genetics',
            'tag2' => 'Research',
            'author' => 'Hamida Jannat',
            'avatar' => $doctorFallback,
        ],
        [
            'title' => 'Why Patient-Centered Care Still Starts With Listening',
            'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=900&q=80',
            'tag1' => 'Patient Care',
            'tag2' => 'Clinic',
            'author' => 'Hamida Jannat',
            'avatar' => $doctorFallback,
        ],
    ];
    $doctorShowcase = $doctors->isNotEmpty()
        ? $doctors
        : collect([
            (object) ['name' => 'Hamida Jannat', 'specialization' => 'Cardiologist & Medicine', 'photo' => $doctorPhotos[0], 'department' => null, 'designation' => null],
            (object) ['name' => 'James Brown', 'specialization' => 'Neurologist', 'photo' => $doctorPhotos[1], 'department' => null, 'designation' => null],
            (object) ['name' => 'Ayesha Rahman', 'specialization' => 'Pediatrician', 'photo' => $doctorPhotos[2], 'department' => null, 'designation' => null],
            (object) ['name' => 'Omar Faruk', 'specialization' => 'Orthopedic Surgeon', 'photo' => $doctorPhotos[3], 'department' => null, 'designation' => null],
            (object) ['name' => 'Nadia Islam', 'specialization' => 'Radiologist', 'photo' => $doctorPhotos[4], 'department' => null, 'designation' => null],
        ]);
@endphp

@section('title', $hospitalName)
@section('meta_description', $hospitalName.' — modern hospital care, DNA testing, specialist doctors, and medical departments.')

@section('content')
    <section class="mock-stats" aria-label="Impact figures">
        <div class="web-wrap mock-stats__row">
            <div>
                <strong>$250M</strong>
                <span>In Healthcare Funding</span>
            </div>
            <div>
                <strong>20M+</strong>
                <span>Patients Served Globally</span>
            </div>
            <div>
                <strong>95%</strong>
                <span>Patient Satisfaction Rate</span>
            </div>
            <div>
                <strong>200+</strong>
                <span>Medical Professionals</span>
            </div>
        </div>
    </section>

    <section class="mock-block">
        <div class="web-wrap mock-dna">
            <article class="mock-dna__main">
                <div class="mock-dna__art" aria-hidden="true">
                    <img
                        src="https://images.unsplash.com/photo-1614935151651-0be4c845d56a?auto=format&fit=crop&w=1400&q=80"
                        alt=""
                        referrerpolicy="no-referrer"
                    >
                    <div class="mock-dna__helix">
                        <svg viewBox="0 0 220 320" fill="none">
                            <path d="M60 20c40 40 60 60 100 100S220 220 160 300" stroke="#22c55e" stroke-width="14" stroke-linecap="round"/>
                            <path d="M160 20c-40 40-60 60-100 100S0 220 60 300" stroke="#38bdf8" stroke-width="14" stroke-linecap="round"/>
                            <path d="M78 56h64M70 96h80M66 136h88M70 176h80M78 216h64M88 256h44" stroke="#ffffff" stroke-width="8" stroke-linecap="round" opacity="0.9"/>
                        </svg>
                    </div>
                </div>
                <div class="mock-dna__copy">
                    <h2>We Offer Pro<br>DNA Testing</h2>
                    <div class="mock-dna__actions">
                        <span class="mock-dna__reports">
                            <span class="mock-dna__reports-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M8 4h8v4H8V4zm-2 4h12v12H6V8zm4 4h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            DNA Reports: 5.5K
                        </span>
                        <a href="#appointment" class="mock-dna__start">
                            Get Started
                            <span aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </a>
                    </div>
                </div>
            </article>

            <div class="mock-dna__side">
                <article class="mock-dna__trust">
                    <img
                        src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=900&q=80"
                        alt="Trusted clinician"
                        referrerpolicy="no-referrer"
                    >
                    <p>Trusted &amp; Secure<br>Testing</p>
                </article>
                <article class="mock-dna__rate">
                    <div class="mock-dna__rate-copy">
                        <strong>99.9%</strong>
                        <span>Accuracy</span>
                        <em>DNA Matching Rate</em>
                    </div>
                    <div class="mock-dna__rate-art" aria-hidden="true">
                        <svg viewBox="0 0 120 160" fill="none">
                            <path d="M30 10c22 22 34 34 56 56s34 56 10 94" stroke="#22c55e" stroke-width="10" stroke-linecap="round"/>
                            <path d="M90 10c-22 22-34 34-56 56s-34 56-10 94" stroke="#7dd3fc" stroke-width="10" stroke-linecap="round"/>
                            <path d="M40 34h40M36 58h48M34 82h52M38 106h44M46 130h28" stroke="#ffffff" stroke-width="6" stroke-linecap="round" opacity="0.85"/>
                        </svg>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="mock-block">
        <div class="web-wrap">
            <div class="mock-head">
                <span class="mock-badge">Doctors</span>
                <h2>Meet Our Specialized<br>Medical Team</h2>
            </div>

            <div class="mock-doctors">
                @foreach ($doctorShowcase as $index => $doctor)
                    <article @class(['mock-doctor', 'is-active' => $loop->iteration === (int) ceil($doctorShowcase->count() / 2) || ($loop->count < 3 && $loop->first)])>
                        <div class="mock-doctor__photo">
                            <img
                                src="{{ filled($doctor->photo ?? null) ? $doctor->photo : ($doctorPhotos[$index % count($doctorPhotos)] ?? $doctorFallback) }}"
                                alt="{{ $doctor->name }}"
                                referrerpolicy="no-referrer"
                                loading="lazy"
                            >
                        </div>
                        <div class="mock-doctor__info">
                            <h3>{{ $doctor->name }}</h3>
                            <p>{{ $doctor->specialization ?: ($doctor->department?->name ?: ($doctor->designation ?: 'Consultant')) }}</p>
                            <div class="mock-doctor__social">
                                <a href="#appointment" aria-label="Book with {{ $doctor->name }}"><x-icon name="stethoscope" class="h-3.5 w-3.5" /></a>
                                <a href="{{ route('about') }}" aria-label="About {{ $doctor->name }}"><x-icon name="users" class="h-3.5 w-3.5" /></a>
                                @if ($phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" aria-label="Call about {{ $doctor->name }}"><x-icon name="phone" class="h-3.5 w-3.5" /></a>
                                @else
                                    <a href="#appointment" aria-label="Contact for {{ $doctor->name }}"><x-icon name="phone" class="h-3.5 w-3.5" /></a>
                                @endif
                                <a href="mailto:{{ $hospital->get('email') ?: 'info@hospital.com' }}" aria-label="Email about {{ $doctor->name }}"><x-icon name="mail" class="h-3.5 w-3.5" /></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mock-block mock-block--soft">
        <div class="web-wrap">
            <div class="mock-head">
                <span class="mock-badge">Departments</span>
                <h2>Explore Our Medical<br>Departments</h2>
            </div>

            <div class="mock-depts">
                @foreach ($deptShowcase as $index => $department)
                    @php
                        $name = is_array($department) ? $department['name'] : $department->name;
                        $desc = is_array($department)
                            ? $department['description']
                            : ($department->description ?: 'Specialist clinics and ward support for this department.');
                        $icon = is_array($department)
                            ? $department['icon']
                            : ($deptIcons[$index] ?? 'heart');
                    @endphp
                    <article class="mock-dept">
                        <div class="mock-dept__top">
                            <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="mock-dept__icon" aria-hidden="true">
                                <x-icon :name="$icon" class="h-5 w-5" />
                            </span>
                        </div>
                        <h3>{{ $name }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($desc, 88) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mock-block">
        <div class="web-wrap">
            <div class="mock-head">
                <span class="mock-badge">Blog</span>
                <h2>Latest From Medical<br>Frontlines</h2>
            </div>

            <div class="mock-posts">
                @forelse ($posts as $index => $post)
                    <article class="mock-post">
                        <div class="mock-post__media">
                            <img
                                src="{{ $post->cover_image ?: ($blogDefaults[$index]['image'] ?? $blogDefaults[0]['image']) }}"
                                alt="{{ $post->title }}"
                                referrerpolicy="no-referrer"
                                loading="lazy"
                            >
                        </div>
                        <div class="mock-post__body">
                            <div class="mock-post__tags">
                                <span class="is-green">{{ $post->blogCategory?->name ?: 'Medical' }}</span>
                                <span>Health Care</span>
                            </div>
                            <h3>{{ $post->title }}</h3>
                            <div class="mock-post__author">
                                <img src="{{ $doctorFallback }}" alt="" referrerpolicy="no-referrer">
                                <span>Hamida Jannat</span>
                            </div>
                        </div>
                    </article>
                @empty
                    @foreach ($blogDefaults as $item)
                        <article class="mock-post">
                            <div class="mock-post__media">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" referrerpolicy="no-referrer" loading="lazy">
                            </div>
                            <div class="mock-post__body">
                                <div class="mock-post__tags">
                                    <span class="is-green">{{ $item['tag1'] }}</span>
                                    <span>{{ $item['tag2'] }}</span>
                                </div>
                                <h3>{{ $item['title'] }}</h3>
                                <div class="mock-post__author">
                                    <img src="{{ $item['avatar'] }}" alt="" referrerpolicy="no-referrer">
                                    <span>{{ $item['author'] }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section id="appointment" class="mock-block mock-block--soft">
        <div class="web-wrap">
            <div class="mock-head">
                <span class="mock-badge">Appointments</span>
                <h2>Ask The Desk<br>For A Slot</h2>
            </div>
            <form action="{{ route('appointment-requests.store') }}" method="POST" class="site-form">
                @csrf
                <label class="site-field">
                    <span>Your name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <em>{{ $message }}</em> @enderror
                </label>
                <label class="site-field">
                    <span>Phone</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                    @error('phone') <em>{{ $message }}</em> @enderror
                </label>
                <label class="site-field">
                    <span>Service</span>
                    <select name="service" required>
                        <option value="">Choose one</option>
                        @foreach ($bookableList as $serviceItem)
                            <option value="{{ $serviceItem->slug }}" @selected(old('service', request('service')) === $serviceItem->slug)>{{ $serviceItem->title }}</option>
                        @endforeach
                    </select>
                    @error('service') <em>{{ $message }}</em> @enderror
                </label>
                <label class="site-field">
                    <span>Preferred date</span>
                    <input type="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" required>
                    @error('date') <em>{{ $message }}</em> @enderror
                </label>
                <button type="submit" class="site-btn site-btn--navy site-form__submit">Send request</button>
            </form>
        </div>
    </section>
@endsection
