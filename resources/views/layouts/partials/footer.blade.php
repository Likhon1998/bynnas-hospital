@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Cityhealth Medical Center';
    $nameParts = preg_split('/\s+/', trim($hospitalName)) ?: [$hospitalName];
    $brandMark = $nameParts[0] ?? 'Cityhealth';
    $brandRest = trim(implode(' ', array_slice($nameParts, 1))) ?: 'Medical Center';
    $phone = $hospital->get('phone') ?: '(212) 555-1212';
    $emergency = $hospital->get('emergency_phone') ?: '24/7 Emergency Care';
    $email = $hospital->get('email');
    $address = $hospital->get('address') ?: '12 Lakeside Avenue, Gulshan, Dhaka 1212';
@endphp

<section class="ch-priority">
    <div class="ch-priority__pulse" aria-hidden="true"></div>
    <div class="ch-wrap ch-priority__row">
        <div class="ch-priority__copy">
            <div>
                <h2>Your Health. Our Priority.</h2>
                <p>Experience world-class care from a trusted medical team.</p>
            </div>
        </div>
        <a href="{{ route('home') }}#appointment" class="ch-btn ch-btn--light">Book Appointment Now</a>
    </div>
</section>

<footer id="contact" class="ch-foot">
    <div class="ch-wrap ch-foot__grid">
        <div class="ch-foot__brand">
            <a href="{{ route('home') }}" class="ch-brand">
                <span class="ch-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 36 36" class="h-5 w-5" fill="none">
                        <path d="M18 8v20M9 18h18" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>
                    <span class="ch-brand__name">{{ $brandMark }}</span>
                    <span class="ch-brand__sub">{{ $brandRest }}</span>
                </span>
            </a>
            <p class="mt-4">Committed to exceptional healthcare, medical integrity, and innovation.</p>
        </div>

        <div>
            <h3>Quick Links</h3>
            <ul>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('home') }}#departments">Departments</a></li>
                <li><a href="{{ route('home') }}#doctors">Doctors</a></li>
                <li><a href="{{ route('home') }}#locations">Locations</a></li>
                <li><a href="{{ route('home') }}#contact">Contact Us</a></li>
            </ul>
        </div>

        <div>
            <h3>Patients & Visitors</h3>
            <ul>
                <li><a href="{{ route('home') }}#patients">Patient Portal</a></li>
                <li><a href="{{ route('home') }}#patients">Visiting Hours</a></li>
                <li><a href="{{ route('services') }}">Billing & Insurance</a></li>
                <li><a href="{{ route('home') }}#faq">Patient Preparation</a></li>
                <li><a href="{{ route('home') }}#faq">FAQ</a></li>
            </ul>
        </div>

        <div>
            <h3>Our Services</h3>
            <ul>
                <li><a href="{{ route('emergency') }}">Emergency Care</a></li>
                <li><a href="{{ route('services') }}">Diagnostic Services</a></li>
                <li><a href="{{ route('services') }}">Surgical Care</a></li>
                <li><a href="{{ route('home') }}#doctors">Rehabilitation</a></li>
                <li><a href="{{ route('home') }}#news">Wellness Programs</a></li>
            </ul>
        </div>

        <div>
            <h3>Contact Us</h3>
            <p>{{ $address }}</p>
            <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
            @if ($email)
                <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif
            <p>{{ $emergency }}</p>
            <h3 class="mt-4">Newsletter</h3>
            <p>Stay informed with health news and hospital updates.</p>
            <form class="ch-news-letter" action="{{ route('home') }}#contact" method="get">
                <label class="sr-only" for="newsletter-email">Email</label>
                <input id="newsletter-email" type="email" name="email" placeholder="Subscribe" autocomplete="email">
                <button type="submit">Join</button>
            </form>
        </div>
    </div>
    <div class="ch-wrap ch-legal">
        <p>© {{ now()->year }} {{ $hospitalName }}. All Rights Reserved.</p>
        <p>
            <a href="{{ route('about') }}">Terms of Use</a>
            ·
            <a href="{{ route('about') }}">Privacy Policy</a>
            ·
            <a href="{{ route('about') }}">Accessibility</a>
        </p>
    </div>
</footer>
