@php
    $hospitalName = $hospital->get('hospital_name') ?: 'Hospital';
    $nameParts = preg_split('/\s+/', trim($hospitalName)) ?: [$hospitalName];
    $brandMark = $nameParts[0] ?? 'Hospital';
    $brandRest = trim(implode(' ', array_slice($nameParts, 1))) ?: 'Medical Center';
    $phone = $hospital->get('phone') ?: '';
    $emergency = $hospital->get('emergency_phone') ?: '';
    $email = $hospital->get('email');
    $address = $hospital->get('address') ?: '';
    $tagline = $hospital->get('tagline') ?: 'Committed to exceptional healthcare, medical integrity, and innovation.';
    $logo = $hospital->get('logo');
    $socials = collect([
        'Facebook' => $hospital->get('facebook_url'),
        'YouTube' => $hospital->get('youtube_url'),
        'Instagram' => $hospital->get('instagram_url'),
    ])->filter(fn (?string $url): bool => filled($url) && $url !== 'https://facebook.com' && $url !== 'https://youtube.com' && $url !== 'https://instagram.com');
    $priorityTitle = $promoBanner?->title ?: 'Your Health. Our Priority.';
    $priorityLead = $promoBanner?->subtitle ?: 'Experience world-class care from a trusted medical team.';
    $priorityCta = $promoBanner?->cta_label ?: 'Book Appointment Now';
    $priorityHref = $promoBanner?->destination() ?: route('home').'#appointment';
    $privacy = filled($hospital->get('privacy_content'));
    $terms = filled($hospital->get('terms_content'));
@endphp

<section class="ch-priority">
    <div class="ch-priority__pulse" aria-hidden="true"></div>
    <div class="ch-wrap ch-priority__row">
        <div class="ch-priority__copy">
            <div>
                <h2>{{ $priorityTitle }}</h2>
                <p>{{ $priorityLead }}</p>
            </div>
        </div>
        <a href="{{ $priorityHref }}" class="ch-btn ch-btn--light">{{ $priorityCta }}</a>
    </div>
</section>

<footer id="contact" class="ch-foot">
    <div class="ch-wrap ch-foot__grid">
        <div class="ch-foot__brand">
            <a href="{{ route('home') }}" class="ch-brand">
                @if (filled($logo))
                    <img src="{{ $logo }}" alt="{{ $hospitalName }}" class="ch-brand__logo">
                @else
                    <span class="ch-brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 36 36" class="h-5 w-5" fill="none">
                            <path d="M18 8v20M9 18h18" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                @endif
                <span>
                    <span class="ch-brand__name">{{ $brandMark }}</span>
                    <span class="ch-brand__sub">{{ $brandRest }}</span>
                </span>
            </a>
            <p class="mt-4">{{ $tagline }}</p>
            @if ($socials->isNotEmpty())
                <p class="mt-3">
                    @foreach ($socials as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener">{{ $label }}</a>@if (! $loop->last) · @endif
                    @endforeach
                </p>
            @endif
        </div>

        <div>
            <h3>Quick Links</h3>
            <ul>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('departments') }}">Departments</a></li>
                <li><a href="{{ route('doctors') }}">Doctors</a></li>
                <li><a href="{{ route('home') }}#locations">Locations</a></li>
                <li><a href="{{ route('home') }}#contact">Contact Us</a></li>
            </ul>
        </div>

        <div>
            <h3>Patients & Visitors</h3>
            <ul>
                <li><a href="{{ route('home') }}#patients">Patient Services</a></li>
                <li><a href="{{ route('about') }}">Visiting Hours</a></li>
                <li><a href="{{ route('services') }}">Billing & Insurance</a></li>
                <li><a href="{{ route('packages') }}">Health Packages</a></li>
                <li><a href="{{ route('home') }}#faq">FAQ</a></li>
            </ul>
        </div>

        <div>
            <h3>Our Services</h3>
            <ul>
                @forelse ($footerServices as $service)
                    <li><a href="{{ $service->isUrgent() ? route('emergency') : route('services.show', $service) }}">{{ $service->title }}</a></li>
                @empty
                    <li><a href="{{ route('services') }}">View services</a></li>
                @endforelse
            </ul>
        </div>

        <div>
            <h3>Contact Us</h3>
            @if ($address)
                <p>{{ $address }}</p>
            @endif
            @if ($phone)
                <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
            @endif
            @if ($email)
                <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
            @endif
            @if ($emergency)
                <p>{{ $emergency }}</p>
            @endif
        </div>
    </div>
    <div class="ch-wrap ch-legal">
        <p>© {{ now()->year }} {{ $hospitalName }}. All Rights Reserved.</p>
        <p>
            @if ($terms)
                <a href="{{ route('terms') }}">Terms of Use</a>
            @endif
            @if ($terms && $privacy)
                ·
            @endif
            @if ($privacy)
                <a href="{{ route('privacy') }}">Privacy Policy</a>
            @endif
        </p>
    </div>
</footer>
